09/03/2026:
ver los filtros de la tabla de ingreso de mp, solucion a busqueda con like
*Ajustar la vista en el index para refrescar la tabla mejor, cambiar por este codigo:

$('.filtro-select').on('change', function () {
    table.ajax.reload(null, false);
});

$('.filtro-texto').on('keyup input', function () {
    table.ajax.reload(null, false);
});


*Ver archivo "notas_3_plantila_para_vistas_09032026"

//09/03/2026
--  Solución para pintar todas las filas de la tabla desde CSS:

/* Resaltar fila al pasar el mouse */
#ingresos_materia_prima tbody tr:hover td {
    background-color: #00e002 !important;
    color: #000 !important;
    font-weight: 700 !important;
    transition: background-color 0.15s ease-in-out;
}

#ingresos_materia_prima tbody tr:hover td:first-child {
    box-shadow: inset 4px 0 0 #5d00e0;
}

#ingresos_materia_prima tbody tr {
    cursor: pointer;
}


09/03/2026: vistas agrupadas:

resources/views/
├── materia_prima/
├── productos/
├── produccion/
├── compras/
├── herramientas/
├── facturacion/
├── layouts/
├── partials/


Consejo adicional para mantener consistencia

Elegí una sola convención y mantenela en todo el proyecto.

Yo usaría esta:

carpetas principales: por dominio

subcarpetas: por submódulo funcional

vistas internas: index/create/edit/show/deleted

Ejemplo:

productos/tipos/index.blade.php
productos/tipos/create.blade.php
productos/tipos/edit.blade.php

*12/3/2026

Asi deberian quedar en lo formularios de edit:

@section('content')
    <form action="{{ route('marcas_insumos.update', $marca->Id_Marca) }}"
          method="POST"
          data-ajax="true"
          data-edit-check="true"
          data-exclude-fields="_token,_method"
          data-redirect-url="{{ route('marcas_insumos.index') }}"
          data-success-message="Marca de insumo actualizada correctamente">
        @csrf


        y agregar estos js


        @section('js')
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('js/form-edit-check.js') }}"></script>
        <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
       @stop

       para agregar las pestañas deberia agregar esto en cada vista:

       @section('content')

    @include('partials.navigation')

    *El alerta que me gusta esta en la parte de create marca_insumo!- y en la vista de Eliminado tambien

    13/03/2026
    
    * Consejo rutas web:

    Qué hace realmente Route::resource

Esta sola línea:

Route::resource('proveedores', ProveedorController::class);

te crea automáticamente estas rutas:

GET /proveedores → index

GET /proveedores/create → create

POST /proveedores → store

GET /proveedores/{proveedore} → show

GET /proveedores/{proveedore}/edit → edit

PUT/PATCH /proveedores/{proveedore} → update

DELETE /proveedores/{proveedore} → destroy

O sea: todo el CRUD completo.

**Qué rutas sí necesitás agregar aparte

Solo las que no vienen dentro de resource, por ejemplo tus auxiliares:

Route::get('proveedores/data', [ProveedorController::class, 'getData'])->name('proveedores.data');
Route::get('proveedores/resumen', [ProveedorController::class, 'resumen'])->name('proveedores.resumen');
Route::get('proveedores/deleted', [ProveedorController::class, 'showDeleted'])->name('proveedores.deleted');
Route::post('proveedores/{id}/restore', [ProveedorController::class, 'restore'])->name('proveedores.restore');

Route::resource('proveedores', ProveedorController::class);


Regla práctica para adelante

Tenelo así como criterio:

si usás Route::resource, no repitas index/create/store/show/edit/update/destroy

solo agregá rutas extra como:

data

resumen

deleted

restore

filtros AJAX

exportaciones

