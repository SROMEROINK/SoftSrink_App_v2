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

30/03/2026

* Transicion de `registro_de_fabricacion`:

La vista de fabricacion ya no debe depender del modelo viejo `Listado_OF` cuando el proyecto esta cargando OF nuevas desde `pedido_cliente` + `pedido_cliente_mp`.

Patron recomendado de transicion:

- `registro_de_fabricacion` toma el pedido base desde `pedido_cliente`
- producto y categoria salen de `productos`
- maquina y familia salen desde `pedido_cliente_mp`
- `listado_of` queda como consolidado/historico, no como dependencia obligatoria para abrir la vista de fabricacion

Esto permite mantener visible fabricacion mientras se termina de redefinir el consolidado final.

30/03/2026

* Regla visual para DataTables anchos:

Cuando una vista tenga muchas columnas y use `scrollX`, no conviene mezclarlo con configuracion `responsive: true` porque puede desfasar encabezados, cuerpo y filtros.

Patron recomendado para proximas vistas:

- usar `scrollX: true`
- usar `responsive: false` en tablas horizontales grandes
- definir un contenedor especifico tipo `.table-responsive-modulo`
- en CSS usar `dataTables_scrollHeadInner` con `width: auto !important`
- en CSS usar tabla interna con `width: auto !important` y `min-width: 100%`
- si hay columnas de auditoria o secundarias, ocultarlas con pliegue
- si la ultima columna tiene botones, dejar `Acciones` fija al borde derecho con `position: sticky; right: 0;`

Aplicado en `registro_de_fabricacion` para:

- corregir desfasaje entre encabezados y filas
- mantener visible la columna `Acciones` aun con zoom 100%
- evitar que los botones de la ultima columna queden fuera de pantalla

30/03/2026

* Regla global de compactacion para tablas:

Prioridad visual del proyecto:

- primero debe verse bien el registro cargado
- despues el filtro
- por ultimo el texto del encabezado

Por eso, para DataTables del sistema se deja como regla base:

- usar `shared/datatables.css` en todas las vistas con tabla
- compactar `th`, `td` y filtros con padding mas corto
- reducir ancho minimo de inputs/select de filtros
- mantener `min-width: 100%` pero con tabla interna en `width: auto`
- preferir encabezados compactos antes que columnas anchas por texto

Si una vista necesita ajuste fino, se complementa con CSS propio del modulo, pero la base comun debe salir desde `public/vendor/adminlte/dist/css/shared/datatables.css`.

30/03/2026

* Cierre operativo de `listado_of` y `pedido_cliente_maquinas`:

Se redefine `listado_of` como `VIEW` SQL operativa y ya no como tabla fisica de carga manual.

Estructura final validada:

- `pedido_cliente` conserva el pedido base
- `pedido_cliente_mp` conserva la planificacion y definicion de MP operativa
- `pedido_cliente_maquinas` pasa a ser la fuente oficial de maquina unica por OF
- `registro_de_fabricacion` conserva la produccion real
- `listado_of` queda como consolidado final para sistema
- `listado_of_db` puede mantenerse como vista auxiliar para Excel / Power Query

Reglas aplicadas:

- una OF no puede pasar por varias maquinas
- la asignacion de maquina queda normalizada en `pedido_cliente_maquinas`
- `listado_of` debe tomar `Nro_Maquina` y `Familia_Maquina` desde `pedido_cliente_maquinas` + `maquinas_produc`
- si existe informacion en `pedido_cliente_mp`, puede usarse solo como respaldo transitorio
- `Codigo_MP`, `Nro_Certificado_MP`, `Nro_Pedido_MP`, `Nro_Remito_MP`, `Fecha_Ingreso_MP` y `Prov_Nombre` deben salir de `mp_ingreso`, que es la tabla madre
- el numero de ingreso MP puede resolverse desde `pedido_cliente_mp` o desde staging historico, pero una vez resuelto, los datos descriptivos deben salir de `mp_ingreso`

Migracion historica aplicada:

- se creo `pedido_cliente_maquinas`
- se cargo historico desde CSV reducido con columnas `N° O.F`, `N° De Ingreso MP_DB`, `Id_maquina`
- la carga correcta se hace a tabla staging y luego con `INSERT ... SELECT`
- no se debe importar el CSV directo a `pedido_cliente_maquinas` porque `N° De Ingreso MP_DB` no es `Id_Pedido_MP`

Criterio para futuras vistas / consultas:

- usar `listado_of` como resumen final del sistema
- usar `listado_of_backup_20260330` solo para comparacion o recupero puntual
- usar `listado_of_db` solo como salida simplificada para consumo externo si Excel necesita menos joins

