# PATRON DATATABLES SOFTSRINK

## Objetivo

Este documento define el patrón oficial de uso de DataTables en `SoftSrink_App_v2`.

Busca unificar:

- tablas del `index`
- filtros por columna
- carga AJAX
- integración con Laravel
- respuestas desde `getData()`
- botones de acciones
- cards resumen
- recarga de datos sin refrescar toda la página

---

# 1. Rol de DataTables en SoftSrink

En SoftSrink, DataTables se usa principalmente en la vista:

- `index.blade.php`

para mostrar:
- listados de catálogos
- pedidos
- fabricaciones
- ingresos
- estados
- entregas
- proveedores
- etc.

---

# 2. Patrón general de index con DataTables

Una vista `index` con DataTables debe incluir:

1. título o `x-header-card`
2. cards resumen si aplica
3. tabla HTML con `thead`
4. segunda fila de filtros
5. JS con inicialización de DataTables
6. AJAX hacia `route('modulo.data')`

---

# 3. Estructura HTML recomendada

## Tabla base

```blade id="l5b8yq"
<div class="table-responsive">
    <table id="tabla_modulo" class="table table-striped table-bordered w-100">
        <thead>
            <tr>
                <th>ID</th>
                <th>Campo 1</th>
                <th>Campo 2</th>
                <th>created_at</th>
                <th>updated_at</th>
                <th>Acciones</th>
            </tr>
            <tr class="filter-row">
                <th></th>
                <th><input type="text" id="filtro_campo_1" class="form-control filtro-texto" placeholder="Filtrar Campo 1"></th>
                <th><input type="text" id="filtro_campo_2" class="form-control filtro-texto" placeholder="Filtrar Campo 2"></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>


Notas! 08/03/2026:

4. Regla de naming
ID de tabla

Usar nombres claros y únicos:

tabla_modulo
productos_table
ingresos_materia_prima
estado_planificacion_table
pedido_cliente_table
IDs de filtros

Usar patrón:

filtro_nombre_campo

Ejemplos:

filtro_nro_ingreso
filtro_proveedor
filtro_materia_prima
filtro_fecha_pedido
filtro_estado_plani
5. Patrón JS de inicialización
Base recomendada
$(document).ready(function () {
    var table = $('#tabla_modulo').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('modulo.data') }}",
            type: 'GET',
            data: function (d) {
                d.filtro_campo_1 = $('#filtro_campo_1').val();
                d.filtro_campo_2 = $('#filtro_campo_2').val();
            }
        },
        columns: [
            { data: 'Id_Modulo' },
            { data: 'Campo_1' },
            { data: 'Campo_2' },
            { data: 'created_at' },
            { data: 'updated_at' },
            {
                data: 'Id_Modulo',
                render: function (data) {
                    return `
                        <a href="/modulo/${data}" class="btn btn-info btn-sm">Ver</a>
                        <a href="/modulo/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                        <button onclick="deleteRegistro(${data})" class="btn btn-danger btn-sm">Eliminar</button>
                    `;
                },
                orderable: false,
                searchable: false
            }
        ],
        searching: false,
        paging: true,
        pageLength: 10,
        responsive: true,
        language: {
            lengthMenu: "Mostrar _MENU_ registros por página",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando página _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });

    $('.filtro-texto, .filtro-select').on('keyup change', function () {
        table.ajax.reload(null, false);
    });

    $('#clearFilters').click(function () {
        $('.filtro-texto').val('');
        $('.filtro-select').val('');
        table.ajax.reload();
    });
});
6. Configuración recomendada
Opciones frecuentes

Estas son las opciones más usadas y recomendadas:

processing: true,
serverSide: true,
searching: false,
paging: true,
responsive: true,
pageLength: 10,
Opciones adicionales si hace falta
scrollX: true,
scrollY: '60vh',
scrollCollapse: true,
fixedHeader: true,

Usarlas cuando la tabla tenga muchas columnas, por ejemplo:

mp_ingresos

fabricacion

7. Patrón de columnas
Regla

Las columnas en JS deben coincidir con los nombres que devuelve getData().

Ejemplo

Si el backend devuelve:

{
  "Id_MP": 1,
  "Nro_Ingreso_MP": 301,
  "Proveedor": "BIOMATERIALES"
}

entonces en columns:

{ data: 'Id_MP' },
{ data: 'Nro_Ingreso_MP' },
{ data: 'Proveedor' },
8. Columna de acciones

Toda tabla index debe tener una columna de acciones al final.

Acciones mínimas recomendadas

Ver

Editar

Eliminar

Ejemplo estándar
{
    data: 'Id_Modulo',
    render: function (data) {
        return `
            <a href="/modulo/${data}" class="btn btn-info btn-sm">Ver</a>
            <a href="/modulo/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
            <button onclick="deleteRegistro(${data})" class="btn btn-danger btn-sm">Eliminar</button>
        `;
    },
    orderable: false,
    searchable: false
}
9. Filtros por columna
Tipos estándar
Input de texto
<input type="text" id="filtro_nombre" class="form-control filtro-texto" placeholder="Filtrar Nombre">
Select
<select id="filtro_estado" class="form-control filtro-select">
    <option value="">Todos</option>
</select>
Regla

Todo filtro debe:

tener ID único

enviarse en ajax.data

ser leído en getData()

10. Botón limpiar filtros

El botón limpiar filtros debe existir en navegación o index:

<button type="button" class="btn btn-dark" id="clearFilters">Limpiar Filtros</button>
Comportamiento esperado

limpiar inputs

resetear selects

recargar DataTable

11. Método backend getData()

El método del controlador debe:

leer filtros desde $request

construir query

devolver datatables()->of(...)

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

        return response()->json(['error' => 'Error al recuperar los datos.'], 500);
    }
}
12. Método getUniqueFilters()

Cuando haya selects dinámicos en filtros, usar un método separado.

Ejemplo
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

        return response()->json(['error' => 'Error al recuperar filtros únicos.'], 500);
    }
}
13. Carga de filtros únicos en JS
Ejemplo
function loadUniqueFilters() {
    $.ajax({
        url: "{{ route('modulo.filters') }}",
        type: 'GET',
        success: function (data) {
            fillSelect('#filtro_campo_1', data.campo_1);
            fillSelect('#filtro_campo_2', data.campo_2);
        }
    });
}

function fillSelect(selector, data) {
    var select = $(selector);
    select.empty();
    select.append('<option value="">Todos</option>');
    data.forEach(function (value) {
        select.append('<option value="' + value + '">' + value + '</option>');
    });
}
14. Cards resumen

Muchos módulos del proyecto usan cards arriba del DataTable.

Patrón esperado

total

activos

eliminados

Backend

Debe existir un método resumen().

Frontend
$.get("{{ route('modulo.resumen') }}", function (data) {
    $('#total-registros').text(data.total);
    $('#activos-registros').text(data.activos);
    $('#eliminados-registros').text(data.eliminados);
});
15. Delete con AJAX desde DataTable

Toda tabla index que elimine desde acciones debe hacerlo con confirmación.

Ejemplo base
function deleteRegistro(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminarlo'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/modulo/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: response.message || 'Registro eliminado correctamente.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#tabla_modulo').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    Swal.fire(
                        'Error',
                        xhr.responseJSON?.message || 'Ha ocurrido un error al intentar eliminar.',
                        'error'
                    );
                }
            });
        }
    });
}
16. Regla sobre paginación y reload

Cuando se recarga la tabla después de eliminar o filtrar, preferir:

table.ajax.reload(null, false);
Motivo

conserva página actual

evita volver al inicio innecesariamente

17. Regla sobre consultas
Sí hacer

usar select() explícito

usar alias en joins

usar orderBy(...)

usar leftJoin() cuando una relación puede faltar

usar with() cuando sea más claro que join

No hacer

Model::all() en index con DataTables server side

traer columnas innecesarias

traer registros eliminados salvo necesidad puntual

18. Casos de uso en SoftSrink
CRUD simple

Ejemplos:

estado_planificacion

marcas_insumos

proveedores

Usan:

DataTable estándar

filtros simples

delete AJAX

CRUD técnico grande

Ejemplos:

mp_ingresos

fabricacion

Usan:

muchas columnas

scrollX

filtros avanzados

joins

19. Errores comunes a evitar
1. Nombre de columna distinto entre backend y frontend

Si backend devuelve Proveedor y JS espera Prov_Nombre, falla.

2. Filtro no enviado por AJAX

Si no se incluye en ajax.data, el backend nunca lo recibe.

3. Filtro enviado pero no leído en getData()

La UI cambia, pero la query no.

4. Recargar tabla completa con location.reload()

No es necesario en la mayoría de los casos.

5. No definir orderable: false en acciones

Puede romper el comportamiento de la tabla.

6. No usar alias en joins

Genera conflictos de nombres de columnas.

20. Patrón oficial recomendado
Vista

tabla con filtros

DataTable server side

acciones al final

cards resumen arriba si aplica

Controlador

getData()

getUniqueFilters() si aplica

resumen() si aplica

JS

filtros conectados

clear filters

delete AJAX

reload parcial

SweetAlert2

21. Archivos relacionados

Revisar también:

PATRON_CRUD_SIMPLE_SOFTSRINK.md

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_VISTAS_BLADE_SOFTSRINK.md

PATRON_ALERTAS_Y_AJAX_SOFTSRINK.md

22. Conclusión

En SoftSrink_App_v2, DataTables no es solo una tabla visual.

Es el centro operativo del index, y por eso debe cumplir estas reglas:

cargar por AJAX

filtrar correctamente

integrarse con backend limpio

soportar acciones sin recargar la página

mantener consistencia entre módulos

Por eso, cada nuevo módulo con index debe revisar este patrón antes de implementarse.


---

# 2. `docs/PATRON_MODELOS_SOFTSRINK.md`

```md id="nt2mgn"
# PATRON MODELOS SOFTSRINK

## Objetivo

Este documento define el patrón recomendado para los modelos Eloquent de `SoftSrink_App_v2`.

Busca unificar:

- nombres de tabla
- claves primarias
- SoftDeletes
- timestamps
- fillable
- casts
- relaciones
- auditoría (`created_by`, `updated_by`, `deleted_by`)

---

# 1. Rol del modelo en SoftSrink

Cada modelo debe representar fielmente una tabla de base de datos y dejar clara:

- la tabla usada
- su primary key
- los campos editables
- sus relaciones
- el manejo de auditoría
- el uso de borrado lógico si corresponde

---

# 2. Estructura base recomendada

## Modelo simple

```php id="jnhbyz"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NombreModelo extends Model
{
    use SoftDeletes;

    protected $table = 'nombre_tabla';
    protected $primaryKey = 'Id_Modelo';
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
3. namespace

Todos los modelos deben usar:

namespace App\Models;
4. Uso de SoftDeletes
Cuándo usarlo

Usarlo cuando el módulo requiera:

historial de eliminados

recuperación de registros

control de trazabilidad

no perder datos físicamente

Patrón
use Illuminate\Database\Eloquent\SoftDeletes;

y luego:

use SoftDeletes;
Requisito en base de datos

La tabla debe tener:

deleted_at

y, si el proyecto lo usa:

deleted_by
5. $table

Siempre declarar explícitamente el nombre de tabla si no coincide con la convención plural de Laravel.

Ejemplo
protected $table = 'mp_ingreso';
6. $primaryKey

En SoftSrink muchas tablas usan PK personalizada.

Ejemplo
protected $primaryKey = 'Id_MP';
Reglas

siempre declararla si no es id

revisar que coincida con la base

7. $incrementing

Usar si la PK es autoincremental.

Ejemplo
public $incrementing = true;

Si la clave no es numérica/autoincremental, revisar si corresponde false.

8. $keyType

Definir el tipo si hace falta.

Ejemplo
protected $keyType = 'int';
9. public $timestamps = true

En SoftSrink, si la tabla tiene:

created_at

updated_at

entonces conviene usar:

public $timestamps = true;
Cuándo usar false

Solo si la tabla realmente no maneja esos campos.

10. $fillable

Todo campo editable debe estar definido aquí.

Ejemplo
protected $fillable = [
    'Campo_1',
    'Campo_2',
    'reg_Status',
    'created_by',
    'updated_by',
    'deleted_by',
];
Regla

No olvidar incluir:

campos del formulario

campos de auditoría que se asignan desde controlador

11. $casts

Usar para normalizar tipos y evitar problemas en comparaciones o vistas.

Ejemplos
protected $casts = [
    'reg_Status' => 'integer',
    'Fecha_Ingreso' => 'date',
    'Cant_Fabricacion' => 'integer',
];
Cuándo conviene

enteros

booleanos

fechas

decimales si la lógica lo requiere

12. Relaciones base de auditoría

Si el módulo guarda usuarios de auditoría, conviene agregar estas relaciones:

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function updater()
{
    return $this->belongsTo(User::class, 'updated_by');
}

public function deleter()
{
    return $this->belongsTo(User::class, 'deleted_by');
}
Requisito

Importar el modelo User si se usa.

13. Relaciones belongsTo

Cuando la tabla apunta a otra tabla por FK, usar belongsTo().

Ejemplo
public function proveedor()
{
    return $this->belongsTo(Proveedor::class, 'Id_Proveedor', 'Prov_Id');
}
Regla

Siempre indicar:

modelo relacionado

FK local

PK remota

14. Relaciones hasMany

Cuando un registro padre tiene muchos hijos.

Ejemplo
public function ingresos()
{
    return $this->hasMany(MpIngreso::class, 'Id_Materia_Prima', 'Id_Materia_Prima');
}
15. Relaciones en módulos técnicos
Ejemplo MpIngreso

Puede tener:

proveedor

materiaPrima

diametro

Ejemplo PedidoCliente

Puede tener:

producto

estadoPlanificacion

creator

updater

Ejemplo RegistroDeFabricacion

Puede tener:

listado_of

creator

updater

16. Ejemplo real tipo SoftSrink
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoPlanificacion extends Model
{
    use SoftDeletes;

    protected $table = 'estado_planificacion';
    protected $primaryKey = 'Estado_Plani_Id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Nombre_Estado',
        'Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Status' => 'integer',
    ];
}
17. Campos de auditoría recomendados

En tablas administrativas o técnicas, conviene tener:

created_at
created_by
updated_at
updated_by
deleted_at
deleted_by
Regla

Si la tabla los tiene, el modelo debe estar preparado para trabajar con ellos.

18. Reglas de naming
Nombres de modelo

Siempre en singular y PascalCase:

MpIngreso
EstadoPlanificacion
PedidoCliente
RegistroDeFabricacion
Nombres de relaciones

Siempre descriptivos y en camelCase:

proveedor
materiaPrima
diametro
estadoPlanificacion
creator
updater
deleter
19. Errores comunes a evitar
1. No declarar $table

Si la tabla no sigue convención de Laravel, el modelo falla.

2. No declarar $primaryKey

Si no es id, el update/show/destroy pueden romperse.

3. Olvidar SoftDeletes

Hace que delete() borre físicamente.

4. No incluir campos en $fillable

Impide guardar correctamente.

5. No definir relaciones

Complica muchísimo controladores, vistas y DataTables.

6. No usar casts cuando hacen falta

Genera problemas en comparaciones, isDirty() y visualización.

20. Buenas prácticas
Sí hacer

declarar todo explícitamente

usar SoftDeletes cuando corresponda

mantener relaciones limpias

usar nombres claros

revisar consistencia con DB

incluir auditoría

No hacer

dejar que Laravel “adivine” todo

mezclar nombres inconsistentes

omitir claves o relaciones importantes

21. Relación con otros patrones

Este documento debe leerse junto con:

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_CRUD_SIMPLE_SOFTSRINK.md

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

22. Conclusión

En SoftSrink_App_v2, un modelo bien definido debe cumplir estas reglas:

representar correctamente la tabla real

declarar su primary key

manejar SoftDeletes si corresponde

exponer relaciones limpias

soportar la trazabilidad del sistema

ser reutilizable y claro para controladores y vistas

Por eso, cada vez que se cree un nuevo modelo, revisar este patrón antes de usarlo.


---

# 3. `docs/PATRON_RUTAS_SOFTSRINK.md`

```md id="zp614f"
# PATRON RUTAS SOFTSRINK

## Objetivo

Este documento define el patrón recomendado para declarar rutas en `SoftSrink_App_v2`.

Busca unificar:

- nombres de rutas
- orden de declaración
- rutas especiales para DataTables
- rutas de filtros
- rutas de resumen
- rutas de eliminados
- rutas de restore
- uso de `Route::resource()`

---

# 1. Ubicación

Archivo principal:

```text id="wlh4c7"
routes/web.php
2. Patrón general

En SoftSrink, cada módulo suele tener dos grupos de rutas:

A. Rutas auxiliares

Ejemplos:

data

filters

resumen

deleted

restore

rutas AJAX especiales

B. Rutas resource

Ejemplo:

Route::resource('modulo', ModuloController::class);
3. Orden recomendado de rutas por módulo

Siempre conviene declarar primero las rutas auxiliares y después Route::resource().

Patrón recomendado
Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');

Route::resource('modulo', ModuloController::class);
Motivo

Evita conflictos de interpretación con rutas como:

modulo/create

modulo/{id}

modulo/deleted

4. Patrón para CRUD simple
Ejemplo base
use App\Http\Controllers\ModuloController;

Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');
Route::resource('modulo', ModuloController::class);
5. Patrón para CRUD multifila

En módulos multifila también suele existir Route::resource(), pero además aparecen rutas extra para AJAX técnico.

Ejemplo tipo pedido_cliente
Route::get('pedido_cliente/data', [PedidoClienteController::class, 'getData'])->name('pedido_cliente.data');
Route::get('pedido_cliente/ultimo-nro-of', [PedidoClienteController::class, 'getUltimoNroOF']);
Route::get('pedido-cliente/get-id-producto/{nroOf}', [PedidoClienteController::class, 'getIdProductoPorNroOf']);
Route::resource('pedido_cliente', PedidoClienteController::class);
Ejemplo tipo mp_ingresos
Route::get('mp_ingresos/data', [MpIngresoController::class, 'getData'])->name('mp_ingresos.data');
Route::get('mp_ingresos/filters', [MpIngresoController::class, 'getUniqueFilters'])->name('mp_ingresos.filters');
Route::get('mp_ingresos/resumen', [MpIngresoController::class, 'resumenIngresos'])->name('mp_ingresos.resumen');
Route::get('mp_ingresos/deleted', [MpIngresoController::class, 'showDeleted'])->name('mp_ingresos.deleted');
Route::post('mp_ingresos/{id}/restore', [MpIngresoController::class, 'restore'])->name('mp_ingresos.restore');
Route::resource('mp_ingresos', MpIngresoController::class);
6. Naming recomendado
Regla general

El nombre de la ruta debe seguir el nombre del recurso.

Ejemplos correctos
mp_ingresos.index
mp_ingresos.data
mp_ingresos.filters
mp_ingresos.resumen
mp_ingresos.deleted
mp_ingresos.restore
Evitar

nombres inconsistentes

mezclar singular y plural sin criterio

usar nombres ambiguos

7. Rutas DataTables

Todo módulo con DataTable debe tener una ruta data.

Patrón
Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Uso

Se consume desde el JS de index.blade.php.

8. Rutas de filtros únicos

Si un módulo tiene filtros <select> cargados por AJAX, debe tener ruta filters.

Patrón
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
9. Rutas de resumen

Si un módulo muestra cards de:

total

activos

eliminados

debe tener ruta resumen.

Patrón
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
10. Rutas de eliminados y restore
Eliminados
Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
Restore
Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');
11. Uso de Route::resource()

En SoftSrink, cuando el módulo es CRUD tradicional, conviene usar:

Route::resource('modulo', ModuloController::class);

Esto crea automáticamente:

index

create

store

show

edit

update

destroy

12. Casos donde usar rutas especiales

Además de resource, algunos módulos necesitan rutas adicionales:

Correlativos
Route::get('pedido_cliente/ultimo-nro-of', [PedidoClienteController::class, 'getUltimoNroOF']);
Descripciones AJAX
Route::get('productos/{id}/descripcion', [ProductoController::class, 'getDescripcionProducto']);
Códigos o selects dependientes
Route::get('productos/codigos', [ProductoController::class, 'getCodigosProducto'])->name('productos.codigos');
Route::get('productos/subcategorias', [ProductoController::class, 'getSubcategorias'])->name('productos.subcategorias');
13. Middleware y permisos

Las rutas suelen ir dentro del grupo:

Route::middleware(['auth', 'verified'])->group(function () {
    ...
});

Si aplica, el controlador además puede manejar permisos con middleware interno.

Ejemplo
public function __construct()
{
    $this->middleware('permission:ver produccion')->only('index', 'show');
    $this->middleware('permission:editar produccion')->only(['create', 'store', 'edit', 'update']);
}
14. Buenas prácticas de organización
Sí hacer

agrupar rutas por módulo

declarar auxiliares antes de resource

usar nombres coherentes

dejar una separación visual entre módulos

usar name(...) en casi todas las rutas auxiliares

No hacer

mezclar rutas de distintos módulos sin orden

poner resource antes de rutas especiales conflictivas

duplicar rutas

usar nombres distintos para lo mismo

15. Patrón recomendado de bloque por módulo
use App\Http\Controllers\ModuloController;

// Rutas auxiliares del módulo
Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');

// CRUD principal
Route::resource('modulo', ModuloController::class);
16. Verificación con Artisan

Después de agregar o cambiar rutas, conviene correr:

php artisan route:clear
php artisan optimize:clear
php artisan route:list
Objetivo

limpiar caché

verificar que aparezcan las rutas nuevas

comprobar nombres correctos

17. Errores comunes a evitar
1. Declarar resource antes de deleted

Puede generar conflicto con /{id}.

2. No poner nombre a rutas auxiliares

Después complica Blade y JS.

3. Duplicar rutas

Confunde al framework y al mantenimiento.

4. Usar rutas que no coinciden con el nombre del módulo

Complica la lectura del proyecto.

5. Mezclar singular/plural sin criterio

Ejemplo:

controlador en plural

route name en singular

carpeta view con otro nombre

18. Relación con otros patrones

Este documento debe leerse junto con:

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_VISTAS_BLADE_SOFTSRINK.md

PATRON_CRUD_SIMPLE_SOFTSRINK.md

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

19. Conclusión

En SoftSrink_App_v2, las rutas deben cumplir estas reglas:

ser claras

ser consistentes

evitar conflictos

soportar CRUD + AJAX + filtros + restore

ser fáciles de leer y mantener

Por eso, cada módulo nuevo debería copiar este patrón y adaptarlo solo en nombres y métodos específicos.


---

# 4. `docs/PATRON_VISTAS_BLADE_SOFTSRINK.md`

```md id="8ubmwe"
# PATRON VISTAS BLADE SOFTSRINK

## Objetivo

Este documento define el patrón recomendado para las vistas Blade dentro de `SoftSrink_App_v2`.

Busca unificar:

- estructura general de vistas
- uso de `adminlte::page`
- títulos
- formularios
- DataTables
- botones
- integración con JS global
- vistas `index`, `create`, `edit`, `show`, `deleted`

---

# 1. Patrón general base

Toda vista principal del proyecto debe usar:

```blade id="0gf6sd"
@extends('adminlte::page')

y estructurarse con:

@section('title')

@section('content_header')

@section('content')

@section('css')

@section('js') si hace falta

2. Estructura mínima recomendada
@extends('adminlte::page')

@section('title', 'Título de la vista')

@section('content_header')
    <h1>Título de la vista</h1>
@stop

@section('content')
    ...
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop
3. Vista index.blade.php
Objetivo

Mostrar el listado principal del módulo.

Debe incluir

título

x-header-card si aplica

cards resumen si aplica

DataTable

filtros

columna acciones

botón limpiar filtros

JS de delete AJAX si corresponde

Estructura típica
@extends('adminlte::page')

@section('title', 'Nombre del Módulo')

@section('content_header')
<x-header-card 
    title="Nombre del Módulo"
    quantityTitle="Total de registros:"
    quantity="{{ $totalRegistros }}"
    buttonRoute="{{ route('modulo.create') }}"
    buttonText="Crear Registro"
    deletedRouteUrl="{{ route('modulo.deleted') }}"
    deletedButtonText="Ver Eliminados"
/>
@stop

@section('content')
<div class="container-fluid">
    ...
    <table id="tabla_modulo" class="table table-striped table-bordered w-100">
        ...
    </table>
</div>
@stop
4. Vista create.blade.php
Objetivo

Dar de alta registros nuevos.

Variante A: create simple

Un solo registro por formulario.

Reglas

usar data-ajax="true" si trabaja con AJAX

usar data-redirect-url

incluir @csrf

incluir botones guardar y volver

Ejemplo base
<form method="POST"
      action="{{ route('modulo.store') }}"
      data-ajax="true"
      data-redirect-url="{{ route('modulo.index') }}">
    @csrf

    <div class="form-group">
        <label for="Campo_1">Campo 1</label>
        <input type="text" name="Campo_1" id="Campo_1" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('modulo.index') }}" class="btn btn-default">Cancelar</a>
</form>
Variante B: create multifila

Se usa cuando hay tabla dinámica y arrays.

Reglas

form con data-ajax="true"

tbody dinámico

botón agregar fila

botón submit

JS por fila

inputs campo[]

5. Vista edit.blade.php
Objetivo

Editar un registro existente.

Reglas

usar data-edit-check="true"

usar data-exclude-fields="_token,_method"

usar data-redirect-url

usar data-success-message

incluir @method('PUT')

incluir form-edit-check.js

Ejemplo base
<form action="{{ route('modulo.update', $registro->Id_Modulo) }}"
      method="POST"
      data-edit-check="true"
      data-exclude-fields="_token,_method"
      data-redirect-url="{{ route('modulo.index') }}"
      data-success-message="Registro actualizado correctamente">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="Campo_1">Campo 1</label>
        <input type="text" class="form-control" id="Campo_1" name="Campo_1" value="{{ $registro->Campo_1 }}" required>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="{{ route('modulo.index') }}" class="btn btn-default">Cancelar</a>
</form>
6. Vista show.blade.php
Objetivo

Mostrar el detalle de un registro.

Reglas

usar card o estructura limpia

listar campos importantes

mostrar relaciones si aplica

botón volver

Ejemplo base
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Detalle del registro</h3>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Campo 1:</strong> {{ $registro->Campo_1 }}</li>
                <li class="list-group-item"><strong>Campo 2:</strong> {{ $registro->Campo_2 }}</li>
            </ul>
        </div>
        <div class="card-footer">
            <a href="{{ route('modulo.index') }}" class="btn btn-default">Volver</a>
        </div>
    </div>
</div>
7. Vista deleted.blade.php
Objetivo

Mostrar registros eliminados y permitir restore.

Reglas

listar solo eliminados

mostrar botón restaurar

confirmar con SweetAlert

volver al index

Ejemplo base
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Campo 1</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($registrosEliminados as $registro)
        <tr>
            <td>{{ $registro->Id_Modulo }}</td>
            <td>{{ $registro->Campo_1 }}</td>
            <td>
                <form method="POST" action="{{ route('modulo.restore', $registro->Id_Modulo) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">Restaurar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
8. Uso de x-header-card

Cuando el módulo tiene header uniforme, conviene usar este componente.

Ejemplo
<x-header-card 
    title="Ingresos de Materia Prima"
    quantityTitle="Total de Unidades Ingresadas:"
    quantity="{{ $totalIngresos }}"
    buttonRoute="{{ route('mp_ingresos.create') }}"
    buttonText="Crear Ingreso"
    deletedRouteUrl="{{ route('mp_ingresos.deleted') }}"
    deletedButtonText="Ver Ingresos Eliminados"
/>
9. Reglas de botones
Botones recomendados

btn-primary = guardar / actualizar

btn-success = crear / restaurar

btn-info = ver

btn-danger = eliminar

btn-default o btn-secondary = volver / cancelar

btn-dark = limpiar filtros

10. Reglas de formularios
Sí hacer

usar form-group

usar form-control

usar labels claros

usar required cuando corresponda

usar selects cargados desde backend o AJAX

separar visualmente secciones grandes

No hacer

mezclar demasiada lógica en Blade

repetir código JS innecesario

usar ids repetidos en tablas dinámicas

11. Reglas para CSS por vista

Cada vista importante debería tener su CSS específico, salvo que reutilice uno base.

Nombres sugeridos
modulo_index.css
modulo_create.css
modulo_edit.css
modulo_show.css
modulo_deleted.css
Ubicación
public/vendor/adminlte/dist/css/
12. Reglas para JS por vista
index.blade.php

Suele incluir:

DataTables

AJAX resumen

delete AJAX

filtros

create.blade.php

Suele incluir:

SweetAlert2

form-ajax-submit.js

JS propio del formulario si hace falta

edit.blade.php

Suele incluir:

SweetAlert2

form-edit-check.js

JS auxiliar para autocalcular campos si hace falta

show.blade.php

Muchas veces no necesita JS

deleted.blade.php

Puede incluir:

SweetAlert2

confirmación AJAX de restore

13. Reglas para scripts globales
En create AJAX

Siempre que el form use AJAX incluir:

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
En edit AJAX

Siempre incluir:

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
14. Reglas para módulos multifila en Blade

Cuando el módulo sea multifila:

usar tabla dinámica

generar filas con JS

trabajar con name="campo[]"

usar clases por fila

usar closest('tr')

evitar ids repetidos

separar cabecera y detalle si corresponde

15. Reglas de consistencia
Naming de vistas

La carpeta y las rutas deben coincidir con el módulo.

Ejemplo correcto
resources/views/estado_planificacion/index.blade.php
resources/views/estado_planificacion/create.blade.php
resources/views/estado_planificacion/edit.blade.php
resources/views/estado_planificacion/show.blade.php
resources/views/estado_planificacion/deleted.blade.php
16. Errores comunes a evitar
1. No incluir @csrf

Rompe create/update/delete.

2. No incluir @method('PUT') en edit

Rompe update.

3. No incluir scripts globales

Se pierde el patrón de alertas y AJAX.

4. Repetir JS innecesario

Duplica mantenimiento.

5. Mezclar demasiado HTML con lógica

Vuelve la vista difícil de mantener.

6. No alinear nombres de vistas con rutas

Complica navegación y mantenimiento.

17. Relación entre vistas y controladores

Cada vista debe corresponder a un método claro del controlador:

index.blade.php ← index()

create.blade.php ← create()

edit.blade.php ← edit()

show.blade.php ← show()

deleted.blade.php ← showDeleted()

18. Buenas prácticas generales
Sí hacer

mantener estructura homogénea

separar CSS por vista

usar SweetAlert2 como estándar

usar Blade limpio y legible

reutilizar componentes cuando haga falta

documentar patrones repetidos

No hacer

inventar estructura distinta para cada módulo

mezclar AJAX manual si ya hay script global

meter lógica de negocio fuerte en Blade

19. Relación con otros patrones

Este documento debe leerse junto con:

PATRON_CRUD_SIMPLE_SOFTSRINK.md

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_ALERTAS_Y_AJAX_SOFTSRINK.md

PATRON_DATATABLES_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

20. Conclusión

En SoftSrink_App_v2, las vistas Blade deben cumplir estas reglas:

ser consistentes entre módulos

integrarse bien con Laravel + AdminLTE

soportar el patrón AJAX oficial

ser fáciles de copiar y adaptar

separar bien estructura, estilo y comportamiento

Por eso, cada nueva vista debe revisarse contra este patrón antes de darse por terminada.