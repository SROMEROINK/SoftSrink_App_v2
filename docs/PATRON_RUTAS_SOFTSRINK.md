# PATRON RUTAS SOFTSRINK

## Objetivo

Este documento define el patrón recomendado para declarar rutas dentro de `SoftSrink_App_v2`.

Busca unificar:

- organización de rutas por módulo
- nombres de rutas
- orden de declaración
- rutas auxiliares para DataTables
- rutas de filtros
- rutas de resumen
- rutas de eliminados
- rutas de restore
- uso correcto de `Route::resource()`
- compatibilidad con AJAX y vistas Blade

---

# 1. Archivo principal

Las rutas web del proyecto viven en:

```text id="xwgs0p"
routes/web.php

Notas! 08/03/2026:

2. Estructura general recomendada

En SoftSrink, las rutas deben ir agrupadas dentro de middleware de autenticación.

Patrón base
Route::middleware(['auth', 'verified'])->group(function () {
    // rutas del sistema
});
Motivo

Esto asegura que:

solo usuarios autenticados entren a módulos internos

se mantenga consistencia con Breeze / AdminLTE

las vistas de gestión no queden públicas

3. Orden recomendado dentro de web.php

Conviene mantener este orden:

use App\Http\Controllers\...

ruta raíz /

grupo auth + verified

dentro del grupo:

dashboard

rutas auxiliares por módulo

rutas especiales AJAX

Route::resource(...)

require __DIR__ . '/auth.php';

4. Patrón por módulo

Cada módulo debería declararse en bloque.

Orden recomendado del bloque

ruta data

ruta filters

ruta resumen

ruta deleted

ruta restore

otras rutas AJAX especiales

Route::resource(...)

Ejemplo base
use App\Http\Controllers\ModuloController;

Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');

Route::resource('modulo', ModuloController::class);
5. Por qué las rutas auxiliares van antes de resource
Regla importante

Siempre declarar las rutas especiales antes de Route::resource().

Motivo

Evita conflictos con rutas tipo:

modulo/create
modulo/{id}
modulo/deleted

Si resource va primero, Laravel puede interpretar:

deleted

filters

resumen

como si fueran {id}.

6. Patrón para CRUD simple

Un módulo CRUD simple normalmente necesita:

data

filters si hay selects dinámicos

resumen si hay cards

deleted

restore

resource

Ejemplo completo
Route::get('estado_planificacion/data', [EstadoPlanificacionController::class, 'getData'])->name('estado_planificacion.data');
Route::get('estado_planificacion/filters', [EstadoPlanificacionController::class, 'getUniqueFilters'])->name('estado_planificacion.filters');
Route::get('estado_planificacion/resumen', [EstadoPlanificacionController::class, 'resumen'])->name('estado_planificacion.resumen');
Route::get('estado_planificacion/deleted', [EstadoPlanificacionController::class, 'showDeleted'])->name('estado_planificacion.deleted');
Route::post('estado_planificacion/restore/{id}', [EstadoPlanificacionController::class, 'restore'])->name('estado_planificacion.restore');
Route::resource('estado_planificacion', EstadoPlanificacionController::class);
7. Patrón para CRUD multifila

Los módulos multifila suelen necesitar más rutas AJAX.

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
8. Naming recomendado
Recurso

El nombre del recurso debe ser consistente en:

ruta

route name

carpeta de vistas

nombre del módulo visual

Ejemplos correctos
mp_ingresos
pedido_cliente
estado_planificacion
marcas_insumos
proveedores
9. Naming de route names
Regla

Usar siempre el mismo prefijo del recurso.

Ejemplos
mp_ingresos.index
mp_ingresos.data
mp_ingresos.filters
mp_ingresos.resumen
mp_ingresos.deleted
mp_ingresos.restore
10. Rutas data

Todo módulo que use DataTables debe tener una ruta data.

Patrón
Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Se usa desde

index.blade.php

AJAX de DataTables

11. Rutas filters

Cuando haya filtros <select> dinámicos, conviene una ruta específica.

Patrón
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
Se usa desde

JS del index

carga de combos de filtro

12. Rutas resumen

Cuando arriba del index hay cards tipo:

total

activos

eliminados

debe existir una ruta resumen.

Patrón
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
13. Rutas deleted

Cuando el módulo usa SoftDeletes y tiene vista de eliminados:

Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
14. Rutas restore

Cuando el módulo puede restaurar registros:

Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');

o, si querés mantener otro orden:

Route::post('modulo/restore/{id}', [ModuloController::class, 'restore'])->name('modulo.restore');
Recomendación

Mantener un solo criterio en cada módulo.
No mezclar ambos formatos sin necesidad.

15. Rutas AJAX especiales

Algunos módulos tienen lógica técnica extra.

Ejemplos reales del proyecto
Correlativo
Route::get('pedido_cliente/ultimo-nro-of', [PedidoClienteController::class, 'getUltimoNroOF']);
Descripción de producto
Route::get('productos/{id}/descripcion', [ProductoController::class, 'getDescripcionProducto']);
Subcategorías
Route::get('productos/subcategorias', [ProductoController::class, 'getSubcategorias'])->name('productos.subcategorias');
Códigos
Route::get('productos/codigos', [ProductoController::class, 'getCodigosProducto'])->name('productos.codigos');
16. Uso de Route::resource()

Cuando el módulo tiene CRUD clásico, usar:

Route::resource('modulo', ModuloController::class);

Esto genera automáticamente:

index

create

store

show

edit

update

destroy

17. Rutas que no deben faltar en un CRUD estándar
Mínimo recomendado
Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Route::resource('modulo', ModuloController::class);
Completo recomendado si usa SoftDeletes y cards
Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');
Route::resource('modulo', ModuloController::class);
18. Ejemplo completo dentro de grupo middleware
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('estado_planificacion/data', [EstadoPlanificacionController::class, 'getData'])->name('estado_planificacion.data');
    Route::get('estado_planificacion/filters', [EstadoPlanificacionController::class, 'getUniqueFilters'])->name('estado_planificacion.filters');
    Route::get('estado_planificacion/resumen', [EstadoPlanificacionController::class, 'resumen'])->name('estado_planificacion.resumen');
    Route::get('estado_planificacion/deleted', [EstadoPlanificacionController::class, 'showDeleted'])->name('estado_planificacion.deleted');
    Route::post('estado_planificacion/restore/{id}', [EstadoPlanificacionController::class, 'restore'])->name('estado_planificacion.restore');
    Route::resource('estado_planificacion', EstadoPlanificacionController::class);
});
19. Uso con permisos

Hay dos formas comunes en el proyecto:

A. Middleware global en rutas

Ejemplo:

Route::middleware(['auth', 'verified'])->group(function () {
    ...
});
B. Middleware dentro del controlador

Ejemplo:

public function __construct()
{
    $this->middleware('permission:ver produccion')->only('index', 'show');
    $this->middleware('permission:editar produccion')->only(['create', 'store', 'edit', 'update']);
}
Recomendación

Mantener autenticación general en rutas y permisos específicos en el controlador si ya venís trabajando así.

20. Verificación con Artisan

Después de agregar o modificar rutas, correr:

php artisan route:clear
php artisan optimize:clear
php artisan route:list
Objetivo

limpiar caché

validar nombres

detectar conflictos

confirmar que las nuevas rutas existen

21. Buenas prácticas
Sí hacer

agrupar rutas por módulo

comentar bloques si el archivo crece mucho

mantener orden consistente

declarar auxiliares antes de resource

usar nombres coherentes

usar name(...) en rutas auxiliares

No hacer

mezclar rutas de muchos módulos sin orden

declarar resource antes de deleted o filters

usar nombres distintos para la misma lógica

duplicar rutas por accidente

22. Errores comunes a evitar
1. resource antes que deleted

Puede hacer que Laravel interprete deleted como {id}.

2. No usar name() en rutas auxiliares

Después complica Blade, JS y mantenimiento.

3. Nombres inconsistentes

Ejemplo:

ruta mp_ingresos

vistas mp_ingreso

route name mp_ingreso.*

Eso genera confusión.

4. Rutas duplicadas

Puede romper comportamientos o dejar una ruta tapando a otra.

5. No revisar route:list

Te hace perder tiempo al depurar.

23. Plantilla rápida reutilizable
use App\Http\Controllers\ModuloController;

Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');

Route::resource('modulo', ModuloController::class);
24. Relación con otros patrones

Este documento debe leerse junto con:

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_VISTAS_BLADE_SOFTSRINK.md

PATRON_DATATABLES_SOFTSRINK.md

PATRON_CRUD_SIMPLE_SOFTSRINK.md

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

25. Conclusión

En SoftSrink_App_v2, las rutas deben cumplir estas reglas:

ser claras

ser consistentes

evitar conflictos con resource

soportar DataTables, AJAX y restore

ser fáciles de copiar a nuevos módulos

Por eso, cada módulo nuevo debería declararse siguiendo este patrón antes de avanzar con controlador y vistas.