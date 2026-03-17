# PATRON CRUD MULTIFILA SOFTSRINK

## Objetivo

Este patrón se utiliza para módulos de carga múltiple por filas dentro de SoftSrink_App_v2.

Aplica a módulos donde:
- se cargan varios registros en una sola operación
- se usa una tabla dinámica en la vista `create`
- se envían arrays tipo `campo[]`
- puede haber correlativos automáticos por fila
- puede haber cálculos automáticos por fila
- el `store()` recorre múltiples filas
- la validación se hace por arrays
- se usa SweetAlert2
- se usa `form-ajax-submit.js`

---

## Módulos donde aplica

Ejemplos reales:
- `pedido_cliente`
- `fabricacion`
- `mp_ingresos`

No usar este patrón para módulos simples de una sola carga por vez.

Para esos casos usar:
- `PATRON_CRUD_SIMPLE_SOFTSRINK.md`

---

# 1. Modelo mental del CRUD multifila

El CRUD multifila en SoftSrink no funciona como un formulario clásico.

## En vez de:
- 1 submit = 1 registro

## Funciona así:
- 1 submit = muchas filas
- cada fila puede transformarse en 1 registro en base de datos

---

## Estructuras posibles

### A. Tabla pura por filas
Cada fila tiene todos los campos necesarios.

Ejemplo:
- `pedido_cliente`

Cada fila contiene:
- `nro_of[]`
- `producto_id[]`
- `fecha_del_pedido[]`
- `cant_fabricacion[]`

---

### B. Cabecera + detalle
Hay datos generales arriba y detalle por filas abajo.

Ejemplo:
- `mp_ingresos`

Cabecera:
- `Nro_Pedido`
- `Nro_Remito`
- `Fecha_Ingreso`
- `Nro_OC`
- `Id_Proveedor`

Filas:
- `Nro_Ingreso_MP[]`
- `Id_Materia_Prima[]`
- `Id_Diametro_MP[]`
- `Codigo_MP[]`
- etc.

---

### C. Grilla técnica con lógica adicional
Cada fila tiene dependencias, cálculos o autocompletados.

Ejemplo:
- `fabricacion`

Filas:
- `nro_of[]`
- `Id_Producto[]`
- `nro_parcial[]`
- `Nro_OF_Parcial[]`
- `fecha_fabricacion[]`
- `horario[]`
- `operario[]`
- `turno[]`
- `cant_horas[]`

---

# 2. Principio central del store()

En CRUD multifila, el `store()` debe:

1. validar cabecera si existe
2. validar arrays por fila
3. recorrer las filas con `foreach`
4. insertar un registro por fila
5. devolver JSON
6. usar transacción
7. informar duplicados o errores de validación

---

# 3. Estructura típica del controlador `store()`

## Ejemplo base simple

```php id="gzt6ij"
public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'campo_fila_1' => 'required|array|min:1',
            'campo_fila_1.*' => 'required|string|max:255',

            'campo_fila_2' => 'required|array|min:1',
            'campo_fila_2.*' => 'required|integer',
        ]);

        DB::beginTransaction();

        foreach ($validatedData['campo_fila_1'] as $index => $valor) {
            Modelo::create([
                'campo_fila_1' => $validatedData['campo_fila_1'][$index],
                'campo_fila_2' => $validatedData['campo_fila_2'][$index],
                'created_by'   => Auth::id(),
            ]);
        }

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Registros creados correctamente.',
            'redirect' => route('modulo.index'),
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors'  => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Error al guardar registros multifila', [
            'error'   => $e->getMessage(),
            'usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al guardar los registros: ' . $e->getMessage()
        ], 400);
    }
}


 Notas! 08/03/2026:

 4. Validación por arrays

La validación multifila siempre debe pensarse así:

Campo array
'campo' => 'required|array|min:1',
Campo por fila
'campo.*' => 'required|string|max:255',
Ejemplo real tipo pedido_cliente
'nro_of.*' => 'required|numeric|unique:pedido_cliente,Nro_OF',
'producto_id.*' => 'required|numeric',
'fecha_del_pedido.*' => 'required|date',
'cant_fabricacion.*' => 'required|numeric',
Ejemplo real tipo mp_ingresos
'Nro_Ingreso_MP' => 'required|array|min:1',
'Nro_Ingreso_MP.*' => [
    'required',
    'integer',
    'distinct',
    Rule::unique('mp_ingreso', 'Nro_Ingreso_MP')->whereNull('deleted_at'),
],
5. Validación de duplicados

En CRUD multifila hay dos tipos de duplicados:

A. Duplicado dentro de la misma tabla del formulario

Ejemplo:

el usuario repite dos veces el mismo número dentro del submit

Para eso usar:

'distinct'
B. Duplicado contra base de datos

Ejemplo:

el valor ya existe en la tabla

Para eso usar:

Rule::unique(...)

o

'unique:tabla,campo'
C. Duplicado detectado manualmente por lógica de negocio

Ejemplo:

fabricacion con Nro_OF_Parcial

pedido_cliente con Nro_OF

En esos casos puede usarse lógica manual:

$duplicatedRows = [];

foreach ($request->nro_of as $index => $nro_of) {
    $registroExistente = PedidoCliente::where('Nro_OF', $nro_of)->first();

    if ($registroExistente) {
        $duplicatedRows[] = $index + 1;
    }
}

Y luego devolver:

return response()->json([
    'success' => false,
    'message' => 'Algunas filas tienen errores de validación.',
    'duplicatedRows' => $duplicatedRows
], 400);
6. Integración con form-ajax-submit.js

Este patrón está pensado para trabajar con:

public/js/form-ajax-submit.js
Requisitos del formulario

El form debe tener:

<form method="POST"
      action="{{ route('modulo.store') }}"
      data-ajax="true"
      data-redirect-url="{{ route('modulo.index') }}">
Qué espera el JS global
Éxito
{
  "success": true,
  "message": "Registros creados correctamente.",
  "redirect": "/ruta/index"
}
Validación Laravel
{
  "success": false,
  "errors": {
    "campo.0": ["Mensaje error"]
  }
}
Duplicados por filas
{
  "success": false,
  "message": "Algunas filas tienen errores de validación.",
  "duplicatedRows": [2,4,5]
}
Beneficio

form-ajax-submit.js ya puede:

mostrar validaciones 422

mostrar duplicados por fila

marcar filas duplicadas

redirigir al index

mostrar mensajes de éxito/error

7. Patrón de vista create.blade.php multifila

Una vista multifila debe tener:

formulario con data-ajax="true"

tabla HTML

tbody vacío o dinámico

botón agregar fila

botón guardar

JS que genere filas

eventos por fila

validación mínima antes del submit

Ejemplo de estructura base
<form method="POST"
      action="{{ route('modulo.store') }}"
      data-ajax="true"
      data-redirect-url="{{ route('modulo.index') }}">

    @csrf

    <table class="table" id="tablaModulo">
        <thead>
            <tr>
                <th>N° Fila</th>
                <th>Campo 1</th>
                <th>Campo 2</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <button type="button" id="agregarFila">Agregar Fila</button>
    <button type="submit">Guardar</button>
</form>
8. JS correlativo

En módulos multifila, muchas veces necesitás un correlativo automático.

Ejemplo tipo pedido_cliente

Cada fila nueva toma el último número y suma 1.

let filaCounter = 1;
let ultimoCorrelativo = 100;

function generarFila(nroFila, correlativo) {
    return `
        <tr>
            <td class="nro-fila">${nroFila}</td>
            <td><input type="number" name="numero[]" value="${correlativo}" required></td>
            <td><input type="text" name="descripcion[]" required></td>
            <td><button type="button" class="eliminar-fila">Eliminar</button></td>
        </tr>
    `;
}

$('#agregarFila').on('click', function () {
    const ultimoVisible = parseInt($('#tablaModulo tbody tr:last input[name="numero[]"]').val()) || ultimoCorrelativo;
    const siguiente = ultimoVisible + 1;

    $('#tablaModulo tbody').append(generarFila(filaCounter, siguiente));
    filaCounter++;
});
Recomendación

Siempre que el correlativo sea importante:

cargar el valor inicial desde el backend

no hardcodearlo en JS

recalcular al eliminar filas si la lógica lo requiere

9. Recalcular filas y numeración visual

Cuando se elimina una fila, puede ser necesario recalcular:

número visual de fila

correlativo

otros campos dependientes

Ejemplo
function actualizarNumeracionFilas() {
    $('#tablaModulo tbody tr').each(function(index) {
        $(this).find('.nro-fila').text(index + 1);
    });
}
10. Cálculos automáticos por fila

En CRUD multifila técnico, una fila puede calcular valores automáticamente.

Ejemplo tipo mp_ingresos
function actualizarMtsFila($fila) {
    const unidades = parseFloat($fila.find('.unidades-mp').val()) || 0;
    const longitud = parseFloat($fila.find('.longitud-unidad').val()) || 0;
    const total = unidades * longitud;
    $fila.find('.mts-totales').val(total.toFixed(2));
}
Ejemplo tipo fabricacion

calcular Nro_OF_Parcial

setear turno

setear horas

habilitar operario

autocompletar ID producto

11. Dependencias entre selects

En multifila, una fila puede depender de datos AJAX.

Ejemplo tipo pedido_cliente

categoría

subcategoría

código de producto

descripción

Esto implica:

usar $(document).on('change', '.selector', ...)

trabajar siempre con la fila actual

nunca usar selectores globales fijos si hay varias filas

Regla clave

Siempre usar:

var $fila = $(this).closest('tr');

para no afectar todas las filas.

12. Reglas de JS para CRUD multifila
Sí hacer

usar eventos delegados

trabajar con closest('tr')

separar funciones pequeñas

validar antes de submit si no hay filas

recalcular después de eliminar fila

No hacer

asumir que hay una sola fila

usar ids repetidos por fila

usar selectores globales para inputs de tabla

mezclar lógica de una fila con otra

13. Patrón base de JS multifila
$(document).ready(function () {
    let filaCounter = 1;
    let correlativoInicial = 1;

    function generarFila(nroFila, correlativo) {
        return `
            <tr>
                <td class="nro-fila">${nroFila}</td>
                <td><input type="number" name="numero[]" value="${correlativo}" required></td>
                <td><input type="text" name="descripcion[]" required></td>
                <td><button type="button" class="btn btn-danger eliminar-fila">Eliminar</button></td>
            </tr>
        `;
    }

    function actualizarNumeracionFilas() {
        $('#tablaModulo tbody tr').each(function(index) {
            $(this).find('.nro-fila').text(index + 1);
        });
    }

    $('#agregarFila').on('click', function () {
        const ultimoVisible = parseInt($('#tablaModulo tbody tr:last input[name="numero[]"]').val()) || correlativoInicial;
        const siguiente = ultimoVisible + 1;

        $('#tablaModulo tbody').append(generarFila(filaCounter, siguiente));
        filaCounter++;
        actualizarNumeracionFilas();
    });

    $('#tablaModulo').on('click', '.eliminar-fila', function () {
        $(this).closest('tr').remove();
        actualizarNumeracionFilas();
    });

    $('form[data-ajax="true"]').on('submit', function (e) {
        if ($('#tablaModulo tbody tr').length === 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Advertencia',
                text: 'Debe agregar al menos una fila.',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
    });

    $('#tablaModulo tbody').append(generarFila(filaCounter, correlativoInicial));
    filaCounter++;
});
14. Reglas del update() en multifila

En la mayoría de tus módulos multifila actuales:

el create es múltiple

pero el edit es individual

Eso está bien y es una buena práctica.

Recomendación

Mantener:

create multifila

edit individual

Porque editar muchas filas juntas:

complica validación

complica trazabilidad

complica UX

15. Reglas de edición individual en módulos multifila

La edición individual debe:

usar form-edit-check.js

validar unicidad ignorando el propio registro

actualizar solo un registro

devolver JSON de éxito/error

Ejemplo

mp_ingresos.edit

pedido_cliente.edit

fabricacion.edit

16. Casos de negocio típicos
Pedido del cliente

correlativo por fila: Nro_OF

selects dependientes

descripción autocompletada

1 fila = 1 pedido

Fabricación

Nro_OF

Nro_Parcial

Nro_OF_Parcial

horarios y operarios

validación técnica

Ingreso de materia prima

cabecera + detalle

correlativo Nro_Ingreso_MP[]

cálculo de metros

autocompletado de origen

regla especial para soft delete

17. Errores comunes a evitar
1. Validar como si fuera un create simple

Incorrecto:

'Campo' => 'required|string'

Si el campo va por fila debe ser:

'Campo' => 'required|array|min:1',
'Campo.*' => 'required|string'
2. Repetir ids HTML en cada fila

No usar:

<input id="Campo">
<input id="Campo">

En filas dinámicas usar:

class

name="Campo[]"

nunca depender de ids repetidos

3. No usar closest('tr')

Provoca que una acción afecte la fila incorrecta.

4. No devolver duplicatedRows

Perdés la posibilidad de marcar filas con el JS global.

5. No usar transacciones

Si fallan algunas filas, el sistema puede quedar inconsistente.

6. Intentar editar masivamente cuando no hace falta

En SoftSrink conviene:

alta múltiple

edición individual

18. Estructura sugerida de archivos

Para un módulo multifila:

app/Http/Controllers/ModuloController.php
resources/views/modulo/index.blade.php
resources/views/modulo/create.blade.php
resources/views/modulo/edit.blade.php
public/vendor/adminlte/dist/css/modulo_create.css
public/vendor/adminlte/dist/css/modulo_index.css

Y si aplica:

resources/views/modulo/show.blade.php
resources/views/modulo/deleted.blade.php
19. Relación con otros archivos de docs

Complementar este patrón con:

PATRON_ALERTAS_Y_AJAX_SOFTSRINK.md

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_VISTAS_BLADE_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

20. Conclusión operativa

Cuando el módulo tenga:

una tabla dinámica

varias filas en el mismo submit

arrays campo[]

cálculos o autocompletados por fila

correlativos automáticos

validación por fila

entonces usar este patrón.

Resumen del flujo correcto

cargar datos iniciales desde backend

generar la primera fila en JS

permitir agregar/eliminar filas

calcular/autocompletar por fila

validar arrays en store()

guardar con foreach

usar transacción

devolver JSON

dejar la edición para formularios individuales

