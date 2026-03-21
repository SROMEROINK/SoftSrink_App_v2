# PATRON CRUD VISUAL UNIFICADO

## Objetivo

Este documento define el patron visual y funcional que debe repetirse en los CRUD nuevos de `SoftSrink_App_v2`.

La referencia base actual para este patron es:

- `proveedores`
- `marcas_insumos`
- `materia_prima/materias_base`
- `materia_prima/diametro`

La idea es que, salvo necesidad puntual, los modulos nuevos no inventen una estructura distinta.

---

## 1. Regla principal

Todo CRUD nuevo debe intentar mantener:

- mismo header visual
- misma navegacion secundaria
- mismo estilo de DataTable
- mismos filtros
- mismas cards resumen
- misma botonera de acciones
- mismo patron visual para `show`
- mismo criterio de auditoria (`created_by`, `updated_by`, `deleted_by`)

---

## 2. Estructura esperada de index

### Header

Usar `x-header-card` como base:

```blade
@section('content_header')
<x-header-card
    title="Nombre del Modulo"
    buttonRoute="{{ route('modulo.create') }}"
    buttonText="Crear Registro"
    deletedRouteUrl="{{ route('modulo.deleted') }}"
    deletedButtonText="Ver Eliminados"
/>
@stop
```

### Regla de botonera superior

Si el modulo tiene `soft delete`, el `index` debe mostrar siempre ambos botones en el header:

- `Crear ...`
- `Ver eliminados`

No dejar solo el boton de crear si el modulo ya maneja:

- `deleted_at`
- vista `deleted`
- metodo `restore()`

Ese criterio debe repetirse en todos los CRUD futuros del proyecto.

### Regla de cantidad superior

Si el `index` ya muestra cards resumen con:

- total
- activos
- eliminados

entonces no debe renderizar una fila extra con `Cantidad de ...` en el `header-card`.

Ese bloque superior solo se usa si el modulo no tiene resumen visual con cards.

### Contenido

La vista `index` debe tener:

1. `container-fluid`
2. cards resumen arriba
3. card principal con DataTable
4. tabla con dos filas en `thead`
5. fila 1 = encabezados
6. fila 2 = filtros

Ejemplo base:

```blade
@section('content')
<div class="container-fluid">
    <div class="row mt-3">
        ...
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla_modulo" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Campo principal</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th>
                                <input type="text" id="filtro_campo" class="form-control filtro-texto" placeholder="Filtrar Campo">
                            </th>
                            <th>
                                <select id="filtro_estado" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                </select>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
```

---

## 3. Shared CSS obligatorios en index

En modulos CRUD simples con este patron, cargar:

```blade
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/datatables.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/summary-boxes.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_index.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_especifico_index.css') }}">
```

### Regla

El CSS especifico del modulo debe quedar reducido a lo minimo.

Ejemplos validos:

- hover de filas
- ajuste fino de ancho o nowrap
- selector especifico de una tabla concreta

Ejemplos que NO deberian repetirse en cada CSS:

- `.filtro-texto`
- `.filtro-select`
- estilos genericos de cards
- estilos generales de botones
- estilos generales de detalle show

Eso debe vivir en `shared/`.

---

## 4. Filtros

### Inputs

Siempre:

```blade
class="form-control filtro-texto"
```

### Selects

Siempre:

```blade
class="form-control filtro-select"
```

### Regla

Si el `select` no tiene `filtro-select`, el borde rojo de `shared/filters.css` no se aplicara.

---

## 5. Cards resumen

Todo `index` administrativo o de catalogo debe intentar mostrar:

- total
- activos
- eliminados

IDs sugeridos:

- `#total-modulo`
- `#activos-modulo`
- `#eliminados-modulo`

Backend recomendado:

- metodo `resumen()`
- route `modulo.resumen`

Frontend:

```js
function cargarResumen() {
    $.get("{{ route('modulo.resumen') }}", function (data) {
        $('#total-modulo').text(data.total);
        $('#activos-modulo').text(data.activos);
        $('#eliminados-modulo').text(data.eliminados);
    });
}
```

---

## 6. Botonera de acciones en index

La botonera por defecto debe ser:

- `Ver`
- `Editar`
- `Eliminar`

Patron recomendado:

```js
render: function(data) {
    return `
        <a href="/modulo/${data}" class="btn btn-info btn-sm">Ver</a>
        <a href="/modulo/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
        <button onclick="deleteRegistro(${data})" class="btn btn-danger btn-sm">Eliminar</button>
    `;
}
```

Si un modulo no tiene `show`, documentarlo y justificarlo. Pero por defecto, debe tenerlo.

---

## 7. Estructura esperada de show

La vista `show` debe tomar como referencia `proveedores/show`.

Debe tener:

1. `show-header`
2. `card` principal con `show-card`
3. `show-card-header`
4. bloques `detail-item`
5. `detail-divider`
6. footer con botones `Volver` y `Editar`

### Regla importante para show

Las vistas `show` no deben incluir:

- `partials.navigation`
- botoneras de listado
- boton `Limpiar Filtros`
- filtros de tabla
- cards resumen del `index`

La vista `show` debe enfocarse solo en el detalle del registro seleccionado.

### CSS shared obligatorios en show

```blade
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_show.css') }}">
```

### Regla

El CSS del `show` del modulo debe ser minimo. La base del detalle vive en `shared/show-details.css`.

---

## 8. Auditoria en show

Si la tabla tiene:

- `created_at`
- `updated_at`
- `deleted_at`
- `created_by`
- `updated_by`
- `deleted_by`

entonces deben mostrarse en `show`.

Si el modulo no tiene relacion cargada al modelo `User`, se puede mostrar el ID.
Si ya existe relacion, mostrar nombre del usuario.

---

## 9. Controlador minimo esperado

Para un CRUD simple con este patron:

- `index()`
- `getData()`
- `resumen()`
- `show()`
- `create()`
- `store()`
- `edit()`
- `update()`
- `destroy()`
- `showDeleted()`
- `restore()`

---

## Regla de alertas en create y edit

En todas las vistas `create` y `edit` que usen los JS globales reutilizables, cargar siempre `SweetAlert2` antes del script del formulario.

### Create

```blade
@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop
```

### Edit

```blade
@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop
```

### Regla

No depender de que `Swal` venga cargado por otra vista o por el layout.
Cada `create/edit` debe asegurar su propia carga si usa:

- `form-ajax-submit.js`
- `form-edit-check.js`

---

## 10. Rutas minimas esperadas

Antes de `Route::resource()`:

```php
Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');

Route::resource('modulo', ModuloController::class);
```

Si hay filtros AJAX para combos, agregar tambien:

```php
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
```

---

## 11. Regla de implementacion para modulos nuevos

Cuando se cree un modulo nuevo:

1. tomar `proveedores` o `marcas_insumos` como referencia visual
2. tomar `materias_base` o `diametro` como ejemplo reciente de migracion a `shared`
3. evitar copiar CSS legacy completo
4. priorizar `shared/*.css`
5. dejar CSS especifico del modulo lo mas chico posible

---

## 12. Cuándo reutilizar un patron simple

Usar este patron para:

- catalogos
- tablas maestras
- tablas con pocos campos
- CRUD con un solo registro por formulario

No usarlo tal cual en:

- modulos multifila
- modulos con detalle dinamico tipo pedido/fabricacion/ingresos complejos

En esos casos, adaptarlo.

---

## 13. Referencias recomendadas del proyecto

- `resources/views/proveedores/index.blade.php`
- `resources/views/proveedores/show.blade.php`
- `resources/views/marcas_insumos/index.blade.php`
- `resources/views/materia_prima/materias_base/index.blade.php`
- `resources/views/materia_prima/diametro/index.blade.php`
- `public/vendor/adminlte/dist/css/shared/filters.css`
- `public/vendor/adminlte/dist/css/shared/show-details.css`

---

## 14. Conclusión

De ahora en mas, el criterio es:

- misma base visual
- misma base funcional
- mismo patron de auditoria
- mismo criterio de acciones
- minima repeticion de CSS

Cada CRUD nuevo debe parecer parte del mismo sistema, no un modulo aislado.

---

## 15. Regla de alertas unificadas

Para todas las vistas nuevas y futuras:

- `create`: cargar SweetAlert2 y luego `js/swal-utils.js` antes de `form-ajax-submit.js`
- `edit`: cargar SweetAlert2 y luego `js/swal-utils.js` antes de `form-edit-check.js`
- `index` con accion `Eliminar`: usar `SwalUtils.confirmDelete()`, `SwalUtils.deleted()` y `SwalUtils.error()`
- `deleted` con accion `Restaurar`: usar `SwalUtils.confirmRestore()`, `SwalUtils.restored()` y `SwalUtils.error()`

Evitar escribir `Swal.fire(...)` manual para confirmaciones, eliminaciones, restauraciones y mensajes genericos de exito/error, salvo que haya una necesidad realmente particular de esa vista.

---

## 16. Regla fija para tablas index

Para todas las vistas `index` nuevas y futuras:

- los titulos del `thead` deben quedar centrados
- la botonera de `Acciones` debe ir horizontal (`Ver`, `Editar`, `Eliminar` en la misma linea)
- evitar CSS legacy copiado de modulos viejos si la vista ya usa `shared/*.css`
- el CSS especifico del modulo debe contener solo lo que realmente usa esa vista
- si hay pliegues de columnas, deben implementarse desde el `blade` + JS de DataTables y con CSS puntual del modulo

Si una tabla empieza a arrastrar reglas viejas, conviene reescribir su CSS especifico antes de seguir agregando parches.

---

## 17. Regla fija para vistas create y show

Para todas las vistas `create` y `show` nuevas y futuras:

- no incluir `partials.navigation`
- no mostrar botoneras globales del sistema
- no mostrar `Limpiar Filtros`
- en `show`, mostrar solo el detalle del registro y sus acciones propias
- en `create`, mostrar solo el formulario y sus botones propios

Si el formulario usa `select` como campos clave del negocio, priorizar:

- `select` con borde rojo usando `shared/filters.css`
- dependencias en cascada cuando un campo condiciona al siguiente
- valores derivados automáticos si el dato final se arma a partir de otros campos

---

## 18. Regla fija para fechas

Para todas las vistas nuevas y futuras:

- los campos fecha de formulario deben usar `input[type="date"]`
- el valor del formulario debe ir en formato `Y-m-d`
- en listados/DataTables, las fechas deben devolverse ya formateadas desde el controlador, evitando timestamps completos tipo `2026-03-04T03:00:00.000000Z`

Si la fecha viene casteada como `date` o `datetime`, formatearla en el controlador antes de enviarla a la tabla.
