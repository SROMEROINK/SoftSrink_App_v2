# PATRON CRUD SIMPLE SOFTSRINK

## Objetivo

Este patrón se utiliza para módulos CRUD simples de la aplicación SoftSrink_App_v2.

Aplica a módulos donde:
- se crea un registro por vez
- se edita un registro por vez
- se elimina con soft delete
- se puede restaurar desde eliminados
- se usa DataTables en index
- se usa SweetAlert2 para alertas
- se usan los scripts globales:
  - `public/js/form-ajax-submit.js`
  - `public/js/form-edit-check.js`

---

## Módulos donde aplica

Ejemplos de uso:
- `estado_planificacion`
- `mp_diametro`
- `marcas_insumos`
- `proveedores`
- `mp_materia_prima`
- otros catálogos maestros similares

No usar este patrón para módulos de carga múltiple por filas como:
- `pedido_cliente`
- `fabricacion`
- `mp_ingresos`

Para esos casos usar:
- `PATRON_CRUD_MULTIFILA_SOFTSRINK.md`

---

## Estructura estándar del módulo

### Modelo
Ubicación:
```text
app/Models/Modulo.php

 Notas! 08/03/2026:

 Controlador

Ubicación:

app/Http/Controllers/ModuloController.php
Vistas

Ubicación:

resources/views/modulo/

Archivos mínimos:

index.blade.php
create.blade.php
edit.blade.php
show.blade.php
deleted.blade.php
CSS

Ubicación:

public/vendor/adminlte/dist/css/

Archivos sugeridos:

modulo_index.css
modulo_create.css
modulo_edit.css
modulo_show.css
modulo_deleted.css
Rutas

Ubicación:

routes/web.php
Reglas generales del modelo

Todo modelo CRUD simple debe tener:

SoftDeletes

$table

$primaryKey

public $timestamps = true

$fillable

relaciones necesarias

campos de auditoría:

created_by

updated_by

deleted_by

Ejemplo base
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modulo extends Model
{
    use SoftDeletes;

    protected $table = 'nombre_tabla';
    protected $primaryKey = 'Id_Modulo';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Campo_1',
        'Campo_2',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'reg_Status' => 'integer',
    ];
}
Reglas generales del controlador

Un controlador CRUD simple debería tener como mínimo estos métodos:

index()

resumen()

getUniqueFilters()

getData()

create()

store()

show()

edit()

update()

destroy()

showDeleted()

restore()

Además:

debe usar Auth

debe usar Log

debe usar DB si hay transacciones

debe usar Rule para validaciones únicas

debe usar el trait:

CheckForChanges

Recomendación

Siempre que el módulo tenga edición individual, usar:

use App\Http\Controllers\Traits\CheckForChanges;
Reglas para index.blade.php

La vista index debe incluir:

header principal con x-header-card

cards resumen:

total

activos

eliminados

DataTable con filtros

columna de acciones:

ver

editar

eliminar

botón limpiar filtros

AJAX para resumen

AJAX para DataTable

SweetAlert para eliminar

El index debe mostrar:

registros activos solamente

nunca registros soft deleted

Los eliminados deben ir a:

deleted.blade.php

Reglas para create.blade.php

Para formularios simples:

usar data-ajax="true"

usar data-redirect-url

enviar por form-ajax-submit.js

mostrar alertas con SweetAlert2

Ejemplo de form
<form method="POST"
      action="{{ route('modulo.store') }}"
      data-ajax="true"
      data-redirect-url="{{ route('modulo.index') }}">
    @csrf
</form>
Reglas para edit.blade.php

Para formularios de edición:

usar data-edit-check="true"

usar data-exclude-fields="_token,_method"

usar data-redirect-url

usar data-success-message

incluir form-edit-check.js

Ejemplo de form
<form action="{{ route('modulo.update', $registro->Id_Modulo) }}"
      method="POST"
      data-edit-check="true"
      data-exclude-fields="_token,_method"
      data-redirect-url="{{ route('modulo.index') }}"
      data-success-message="Registro actualizado correctamente">
    @csrf
    @method('PUT')
</form>
Reglas para show.blade.php

La vista show debe:

mostrar detalle de un solo registro

incluir datos principales

mostrar relaciones si aplica

mostrar estado

mostrar fechas

tener botón volver

Reglas para deleted.blade.php

La vista deleted debe:

listar solo registros eliminados con onlyTrashed()

tener botón restaurar

usar SweetAlert2 antes de restaurar

volver al index al restaurar correctamente

Reglas de validación
En store()

Cuando un campo debe ser único entre activos:

Rule::unique('nombre_tabla', 'Campo_1')->whereNull('deleted_at')
En update()

Cuando se edita un registro:

Rule::unique('nombre_tabla', 'Campo_1')
    ->ignore($id, 'Id_Modulo')
    ->whereNull('deleted_at')

Esto permite:

ignorar soft deleted si así lo requiere la lógica

mantener unicidad entre activos

Reglas para destroy()

En destroy():

buscar el registro con findOrFail

guardar deleted_by

ejecutar soft delete

devolver JSON si el borrado es por AJAX

Estructura típica
public function destroy($id)
{
    try {
        $registro = Modulo::findOrFail($id);

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
Reglas para restore()

En restore():

usar withTrashed()

restaurar el registro

redirigir al index con mensaje

Estructura típica
public function restore($id)
{
    try {
        $registro = Modulo::withTrashed()->findOrFail($id);
        $registro->restore();

        return redirect()->route('modulo.index')
            ->with('success', 'Registro restaurado correctamente.');
    } catch (\Exception $e) {
        Log::error('Error al restaurar registro', [
            'id' => $id,
            'error' => $e->getMessage(),
        ]);

        return redirect()->route('modulo.index')
            ->with('error', 'No se pudo restaurar el registro.');
    }
}
Reglas para DataTables

El método getData() debe:

devolver solo activos

permitir filtros

devolver columnas necesarias

incluir relaciones si se muestran en tabla

usar datatables()->of(...)

Buenas prácticas

ordenar por ID descendente

evitar joins innecesarios

no cargar eliminados

usar alias cuando hay joins

Reglas de auditoría

En todos los módulos CRUD simples:

Crear

guardar created_by

Actualizar

guardar updated_by

Eliminar

guardar deleted_by

Scripts globales obligatorios
Para create
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
Para edit
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
Patrón visual recomendado

Se recomienda reutilizar:

estructura AdminLTE

cards para resumen

DataTables en index

formularios simples con form-control

botones:

primary = guardar/actualizar

default o secondary = volver/cancelar

danger = eliminar

success = restaurar

Errores comunes a evitar
1. Mezclar modelo incorrecto en destroy()

Ejemplo incorrecto:

estar en MpIngresoController

pero usar MpMateriaPrima::findOrFail($id)

2. No usar whereNull('deleted_at') en reglas únicas

Esto puede provocar errores si el negocio permite reutilizar valores eliminados.

3. No usar data-ajax="true" en create

Hace que se pierda la lógica global de alertas.

4. No usar data-edit-check="true" en edit

Hace que no funcione la validación de “sin cambios”.

5. No guardar deleted_by

Se pierde trazabilidad del usuario que eliminó.

6. No distinguir CRUD simple de CRUD multifila

Esto complica muchísimo el mantenimiento.

Flujo ideal de implementación de un nuevo CRUD simple

Crear modelo

Crear controlador

Crear rutas

Crear vista index

Crear vista create

Crear vista edit

Crear vista show

Crear vista deleted

Crear css base

Probar create

Probar edit

Probar destroy

Probar restore

Probar filtros

Probar resumen

Archivos base relacionados

Ver también:

PATRON_MODELOS_SOFTSRINK.md

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_VISTAS_BLADE_SOFTSRINK.md

PATRON_RUTAS_SOFTSRINK.md

PATRON_DATATABLES_SOFTSRINK.md

PATRON_ALERTAS_Y_AJAX_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

Referencias internas del proyecto

Este patrón fue construido tomando como base módulos ya trabajados en SoftSrink_App_v2, especialmente:

estado_planificacion

marcas_insumos

mp_diametro

mp_materia_prima

proveedores

Nota final

Cuando el módulo:

tenga varias filas en create

use arrays campo[]

tenga cabecera + detalle

genere correlativos automáticos por fila

entonces NO usar este patrón.

Usar en ese caso:

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

