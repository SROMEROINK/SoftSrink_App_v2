# PATRON CONTROLADORES SOFTSRINK

## Objetivo

Este documento define el patrón recomendado para controladores dentro de `SoftSrink_App_v2`.

Busca unificar:

- estructura de métodos
- validaciones
- respuestas JSON
- uso de transacciones
- uso de `CheckForChanges`
- integración con DataTables
- integración con SweetAlert2 y AJAX
- soft delete y restore
- trazabilidad con `created_by`, `updated_by`, `deleted_by`

---

# 1. Tipos de controladores del proyecto

En SoftSrink hay dos grandes tipos de controladores:

## A. Controlador CRUD simple
Ejemplos:
- `EstadoPlanificacionController`
- `MarcasInsumosController`
- `MpDiametroController`
- `ProveedorController`

## B. Controlador CRUD multifila
Ejemplos:
- `PedidoClienteController`
- `RegistroDeFabricacionController`
- `MpIngresoController`

---

# 2. Imports recomendados

En la mayoría de los controladores del proyecto conviene tener:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\NombreModelo;

Notas! 08/03/2026

4. Método index()
Objetivo

Mostrar la vista principal del módulo.

Buenas prácticas

devolver la vista index.blade.php

enviar los datos mínimos necesarios

no cargar listados completos innecesarios si la tabla usa DataTables AJAX

Ejemplo típico
public function index()
{
    $totalRegistros = NombreModelo::count();

    return view('modulo.index', compact('totalRegistros'));
}
5. Método resumen()
Objetivo

Alimentar los cards superiores del index:

total

activos

eliminados

Ejemplo típico
public function resumen()
{
    return response()->json([
        'total'      => NombreModelo::withTrashed()->count(),
        'activos'    => NombreModelo::count(),
        'eliminados' => NombreModelo::onlyTrashed()->count(),
    ]);
}
Regla

Si el módulo usa resumen visual en index, conviene tener este método.

6. Método getUniqueFilters()
Objetivo

Cargar valores únicos para filtros de DataTables.

Buenas prácticas

usar una consulta base

devolver JSON

capturar errores con try/catch

si hay joins, usar alias claros

ordenar y devolver valores limpios

Ejemplo típico
public function getUniqueFilters(Request $request)
{
    try {
        $baseQuery = NombreModelo::query();

        return response()->json([
            'campo_1' => $baseQuery->distinct()->pluck('Campo_1')->sort()->values(),
            'campo_2' => $baseQuery->distinct()->pluck('Campo_2')->sort()->values(),
        ]);
    } catch (\Exception $e) {
        Log::error('Error en getUniqueFilters: ' . $e->getMessage());

        return response()->json([
            'error' => 'Error al recuperar filtros únicos.'
        ], 500);
    }
}
7. Método getData()
Objetivo

Alimentar el DataTable del index.

Reglas

usar datatables()->of(...)

devolver solo registros activos salvo que se necesite otra lógica

aplicar filtros del request

usar joins o relaciones solo si hacen falta

loguear errores

Ejemplo base
public function getData(Request $request)
{
    try {
        $query = NombreModelo::select([
            'Id_Modulo',
            'Campo_1',
            'Campo_2',
            'created_at',
            'updated_at',
        ])->orderBy('Id_Modulo', 'desc');

        if ($request->filled('filtro_campo_1')) {
            $query->where('Campo_1', 'like', '%' . $request->filtro_campo_1 . '%');
        }

        if ($request->filled('filtro_campo_2')) {
            $query->where('Campo_2', 'like', '%' . $request->filtro_campo_2 . '%');
        }

        return datatables()->of($query)->make(true);

    } catch (\Exception $e) {
        Log::error('Error en getData: ' . $e->getMessage());

        return response()->json([
            'error' => 'Error al recuperar los datos.'
        ], 500);
    }
}
8. Método create()
Objetivo

Mostrar la vista de alta.

Buenas prácticas

cargar catálogos necesarios

enviar correlativos si corresponde

no meter lógica de guardado acá

Ejemplo simple
public function create()
{
    return view('modulo.create');
}
Ejemplo con catálogos
public function create()
{
    $proveedores = Proveedor::where('reg_Status', 1)->orderBy('Prov_Nombre')->get();

    return view('modulo.create', compact('proveedores'));
}
9. Método store()
Objetivo

Guardar un nuevo registro.

Reglas

validar datos

usar transacción si hace falta

guardar created_by

devolver JSON si el formulario es AJAX

manejar ValidationException

loguear errores generales

Ejemplo base
public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'Campo_1' => [
                'required',
                'string',
                'max:255',
                Rule::unique('nombre_tabla', 'Campo_1')->whereNull('deleted_at'),
            ],
            'Campo_2' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        $validatedData['created_by'] = Auth::id();

        NombreModelo::create($validatedData);

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Registro creado correctamente.',
            'redirect' => route('modulo.index'),
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Error al crear registro', [
            'error' => $e->getMessage(),
            'usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al crear el registro: ' . $e->getMessage()
        ], 400);
    }
}
10. Método show()
Objetivo

Mostrar el detalle de un registro.

Regla

usar findOrFail()

enviar el modelo a la vista

Ejemplo
public function show($id)
{
    $registro = NombreModelo::findOrFail($id);

    return view('modulo.show', compact('registro'));
}
11. Método edit()
Objetivo

Mostrar la vista de edición.

Buenas prácticas

usar findOrFail()

cargar catálogos asociados si hay selects

mandar todo lo necesario a la vista

Ejemplo
public function edit($id)
{
    $registro = NombreModelo::findOrFail($id);

    return view('modulo.edit', compact('registro'));
}
12. Método update()
Objetivo

Actualizar un registro existente.

Regla oficial de SoftSrink

Cuando el edit usa AJAX, conviene usar el trait:

use App\Http\Controllers\Traits\CheckForChanges;

y dentro del controlador:

use CheckForChanges;
Beneficios

detecta si hubo cambios reales

evita updates innecesarios

devuelve JSON compatible con form-edit-check.js

Ejemplo base
public function update(Request $request, $id)
{
    $registro = NombreModelo::findOrFail($id);

    $validatedData = $request->validate([
        'Campo_1' => [
            'required',
            'string',
            'max:255',
            Rule::unique('nombre_tabla', 'Campo_1')
                ->ignore($id, 'Id_Modulo')
                ->whereNull('deleted_at'),
        ],
        'Campo_2' => 'required|string|max:255',
        'reg_Status' => 'required|in:0,1',
    ]);

    return $this->updateIfChanged($registro, $validatedData, [
        'success_redirect'   => route('modulo.index'),
        'success_message'    => 'Registro actualizado correctamente.',
        'no_changes_message' => 'No se realizaron cambios.',
        'set_updated_by'     => true,
        'use_transaction'    => true,
        'normalize_data'     => false,
    ]);
}
13. Método destroy()
Objetivo

Eliminar un registro mediante soft delete.

Reglas

usar findOrFail()

guardar deleted_by

devolver JSON si el delete es AJAX

validar bloqueos de negocio si corresponde

Ejemplo base
public function destroy($id)
{
    try {
        $registro = NombreModelo::findOrFail($id);

        $registro->deleted_by = Auth::id();
        $registro->save();
        $registro->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente.'
        ]);
    } catch (\Exception $e) {
        Log::error('Error al eliminar registro', [
            'id' => $id,
            'error' => $e->getMessage(),
            'usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'No se pudo eliminar el registro.'
        ], 400);
    }
}
14. Método showDeleted()
Objetivo

Mostrar registros eliminados lógicamente.

Ejemplo
public function showDeleted()
{
    $registrosEliminados = NombreModelo::onlyTrashed()
        ->orderBy('deleted_at', 'desc')
        ->get();

    return view('modulo.deleted', compact('registrosEliminados'));
}
15. Método restore()
Objetivo

Restaurar un registro eliminado.

Ejemplo
public function restore($id)
{
    try {
        $registro = NombreModelo::withTrashed()->findOrFail($id);
        $registro->restore();

        return redirect()->route('modulo.index')
            ->with('success', 'Registro restaurado correctamente.');
    } catch (\Exception $e) {
        Log::error('Error al restaurar registro', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);

        return redirect()->route('modulo.index')
            ->with('error', 'No se pudo restaurar el registro.');
    }
}
16. Controladores multifila

En módulos multifila, el patrón cambia principalmente en store().

Reglas

validar arrays

usar foreach

usar transacción

devolver JSON

opcionalmente devolver duplicatedRows

Ejemplo mínimo
public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'campo' => 'required|array|min:1',
            'campo.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        foreach ($validatedData['campo'] as $index => $valor) {
            NombreModelo::create([
                'campo' => $validatedData['campo'][$index],
                'created_by' => Auth::id(),
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Registros creados correctamente.',
            'redirect' => route('modulo.index'),
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Error al guardar registros multifila', [
            'error' => $e->getMessage(),
            'usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al guardar los registros.'
        ], 400);
    }
}
17. Casos donde usar transacciones
Sí usar transacción cuando:

se insertan varias filas

hay cabecera + detalle

hay múltiples operaciones en la misma acción

un fallo parcial dejaría inconsistencia

Puede no ser indispensable cuando:

se guarda un solo registro simple

no hay operaciones secundarias

Recomendación SoftSrink

Si hay dudas, preferir usar transacción.

18. Casos donde usar Log::info()

Conviene usar Log::info() cuando:

se está depurando un update complicado

se quiere revisar el request recibido

hay cálculos o transformaciones previas al save

Ejemplo
Log::info('Intentando actualizar módulo', [
    'id' => $id,
    'usuario' => Auth::id(),
    'request_data' => $request->all()
]);
19. Casos donde usar Log::error()

Siempre conviene usar Log::error() en:

catch generales

fallos de delete

fallos de restore

fallos de create

fallos de update inesperados

Ejemplo
Log::error('Error al actualizar módulo', [
    'id' => $id,
    'error' => $e->getMessage(),
    'usuario' => Auth::id(),
]);
20. Validaciones únicas recomendadas
En create
Rule::unique('tabla', 'campo')->whereNull('deleted_at')
En update
Rule::unique('tabla', 'campo')
    ->ignore($id, 'PrimaryKey')
    ->whereNull('deleted_at')
Motivo

Esto permite:

ignorar soft deleted si la lógica de negocio lo requiere

evitar falsos positivos en update

21. Trazabilidad obligatoria

Todo controlador debería contemplar:

Create

created_by

Update

updated_by

Delete

deleted_by

Si usa timestamps

verificar que el modelo tenga public $timestamps = true

22. Buenas prácticas generales
Sí hacer

un método por responsabilidad

try/catch en operaciones sensibles

JSON consistente en AJAX

redirect consistente en vistas normales

nombres claros

validación explícita

consultas ordenadas

usar findOrFail

No hacer

mezclar modelos incorrectos

devolver HTML en endpoints AJAX

duplicar lógica de validación innecesariamente

guardar sin validar

usar Model::all() si no hace falta

cargar demasiada lógica en index()

23. Errores comunes a evitar
1. Usar el modelo incorrecto

Ejemplo:
estar en MpIngresoController pero eliminar con MpMateriaPrima::findOrFail($id).

2. No capturar validaciones correctamente

Eso rompe el AJAX del frontend.

3. Mezclar redirect y JSON en el mismo flujo sin criterio

Complica el mantenimiento.

4. No usar CheckForChanges en edit con AJAX

Se pierden ventajas de UX y control.

5. No guardar deleted_by

Perdés trazabilidad.

6. No usar transacción en multifila

Puede dejar datos inconsistentes.

24. Relación con otros patrones

Este documento debe leerse junto con:

PATRON_CRUD_SIMPLE_SOFTSRINK.md

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

PATRON_ALERTAS_Y_AJAX_SOFTSRINK.md

PATRON_VISTAS_BLADE_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

25. Conclusión

En SoftSrink_App_v2, un controlador bien hecho debe cumplir estas reglas:

validar correctamente

registrar trazabilidad del usuario

responder de forma consistente

soportar AJAX cuando la vista lo requiera

proteger la integridad del negocio

ser fácil de mantener y replicar

Por eso, al crear un nuevo controlador:

seguir este patrón

adaptar solo nombres de tabla, campos y relaciones

mantener siempre la misma lógica general