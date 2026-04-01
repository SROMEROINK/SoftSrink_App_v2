<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MpIngresoController;
use App\Http\Controllers\MpEgresoController;
use App\Http\Controllers\MpSalidaInicialController;
use App\Http\Controllers\MpDiametroController;
use App\Http\Controllers\MpMateriaPrimaController;
use App\Http\Controllers\PedidoClienteController;
use App\Http\Controllers\ProductoCategoriaController;
use App\Http\Controllers\RegistroDeFabricacionController;
use App\Http\Controllers\FechasOfController;
use App\Http\Controllers\ListadoEntregaProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\MarcasInsumosController;
use App\Http\Controllers\EstadoPlanificacionController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\ProductoTipoController;
use App\Http\Controllers\ProductoSubcategoriaController;
use App\Http\Controllers\ProductoGrupoSubcategoriaController;
use App\Http\Controllers\ProductoGrupoConjuntosController;
use App\Http\Controllers\PedidoClienteMpController;
use App\Http\Controllers\MpMovimientoAdicionalController;
use App\Http\Controllers\ListadoOfController;

// Ruta por defecto al iniciar la app
Route::get('/', function () {
    return redirect()->route('login');
})->name('default_login');

// Rutas para usuarios autenticados
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('home');
    })->name('dashboard');

    // Rutas protegidas por roles especÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ficos (Administrador)
    Route::middleware(['role:Administrador'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RolePermissionController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // Ruta especÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­fica para DataTables y otras funcionalidades
    Route::get('productos/data', [ProductoController::class, 'getData'])->name('productos.data');
    Route::get('pedido_cliente/data', [PedidoClienteController::class, 'getData'])->name('pedido_cliente.data');
    Route::get('pedido_cliente/resumen', [PedidoClienteController::class, 'resumen'])->name('pedido_cliente.resumen');
    Route::get('pedido_cliente/plain', [PedidoClienteController::class, 'indexPlain'])->name('pedido_cliente.plain');
    Route::get('listado_of', [ListadoOfController::class, 'index'])->name('listado_of.index');
    Route::get('listado_of/data', [ListadoOfController::class, 'getData'])->name('listado_of.data');
    Route::get('listado_of/filters', [ListadoOfController::class, 'getUniqueFilters'])->name('listado_of.filters');
    Route::get('listado_of/resumen', [ListadoOfController::class, 'resumen'])->name('listado_of.resumen');
    Route::get('listado_of/plain', [ListadoOfController::class, 'indexPlain'])->name('listado_of.plain');
    Route::get('listado_of/export/csv', [ListadoOfController::class, 'exportCsv'])->name('listado_of.exportCsv');
    Route::get('listado_of/export/excel', [ListadoOfController::class, 'exportExcel'])->name('listado_of.exportExcel');
    Route::get('pedido_cliente_mp/data', [PedidoClienteMpController::class, 'getData'])->name('pedido_cliente_mp.data');
    Route::get('pedido_cliente_mp/resumen', [PedidoClienteMpController::class, 'resumen'])->name('pedido_cliente_mp.resumen');
    Route::get('pedido_cliente_mp/planner', [PedidoClienteMpController::class, 'planner'])->name('pedido_cliente_mp.planner');
    Route::get('pedido_cliente_mp/create-massive', [PedidoClienteMpController::class, 'createMassive'])->name('pedido_cliente_mp.createMassive');
    Route::post('pedido_cliente_mp/store-massive', [PedidoClienteMpController::class, 'storeMassive'])->name('pedido_cliente_mp.storeMassive');
    Route::get('pedido_cliente_mp/deleted', [PedidoClienteMpController::class, 'showDeleted'])->name('pedido_cliente_mp.deleted');
    Route::get('pedido_cliente_mp/edit-group', [PedidoClienteMpController::class, 'editGroup'])->name('pedido_cliente_mp.editGroup');
    Route::post('pedido_cliente_mp/update-group', [PedidoClienteMpController::class, 'updateGroup'])->name('pedido_cliente_mp.updateGroup');
    Route::get('pedido_cliente_mp/{id}/edit-massive', [PedidoClienteMpController::class, 'editMassive'])->name('pedido_cliente_mp.editMassive');
    Route::post('pedido_cliente_mp/{id}/update-massive', [PedidoClienteMpController::class, 'updateMassive'])->name('pedido_cliente_mp.updateMassive');
    Route::post('pedido_cliente_mp/{id}/restore', [PedidoClienteMpController::class, 'restore'])->name('pedido_cliente_mp.restore');
    Route::get('fabricacion/data', [RegistroDeFabricacionController::class, 'getData'])->name('fabricacion.data');
    Route::post('fabricacion/import-historico', [RegistroDeFabricacionController::class, 'importHistoricCsv'])->name('fabricacion.importHistoricCsv');
    Route::get('fabricacion/resumen', [RegistroDeFabricacionController::class, 'resumen'])->name('fabricacion.resumen');
    Route::get('fechas_of/data', [FechasOfController::class, 'getData'])->name('fechas_of.data');
    Route::get('/entregas_productos/data', [ListadoEntregaProductoController::class, 'getData'])->name('entregas_productos.data');
    Route::get('/entregas_productos/filters', [ListadoEntregaProductoController::class, 'getUniqueFilters'])->name('entregas_productos.filters');
    Route::get('/entregas_productos/resumen', [ListadoEntregaProductoController::class, 'resumen'])->name('entregas_productos.resumen');
    Route::get('/entregas_productos/of-data/{nroOf}', [ListadoEntregaProductoController::class, 'getOfData'])->name('entregas_productos.ofData');
    Route::get('/fabricacion/withFiltro', [RegistroDeFabricacionController::class, 'indexWithFiltro'])->name('fabricacion.withFiltro');
    Route::get('mp_diametro/data', [MpDiametroController::class, 'getData'])->name('mp_diametro.data');
    
    
    
    Route::get('/productos/codigos', [ProductoController::class, 'getCodigosProducto'])->name('productos.codigos');
    
    // Rutas para obtener categorÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­as y subcategorÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­as
    Route::get('/productos/categorias', [ProductoController::class, 'getCategorias'])->name('productos.categorias');
    Route::get('/productos/subcategorias', [ProductoController::class, 'getSubcategorias'])->name('productos.subcategorias');
    Route::get('/productos/codigos', [ProductoController::class, 'getCodigosProducto'])->name('productos.codigos');
    Route::get('/productos/grupos', [ProductoController::class, 'getGruposPorSubcategoria'])->name('productos.grupos');
    Route::get('productos/familias', [ProductoController::class, 'getFamilias'])->name('productos.Familias');
    Route::get('/productos/tipos', [ProductoController::class, 'getTipos'])->name('productos.Tipos');
    Route::get('/productos/unique-filters', [ProductoController::class, 'getUniqueFilters'])->name('productos.getUniqueFilters');
    Route::get('/productos/dependent-filters', [ProductoController::class, 'getDependentFilters'])->name('productos.dependentFilters');
    Route::get('/productos/form-dependencies', [ProductoController::class, 'getFormDependencies'])->name('productos.formDependencies');
    Route::get('/productos/resumen', [ProductoController::class, 'resumen'])->name('productos.resumen');
    Route::get('/productos/deleted', [ProductoController::class, 'showDeleted'])->name('productos.deleted');
    Route::post('/productos/restore/{id}', [ProductoController::class, 'restore'])->name('productos.restore');
    Route::get('/productos/clientes', [ProductoController::class, 'getClientes'])->name('productos.Clientes');
    Route::get('/productos/materiales-mp', [ProductoController::class, 'getMaterialesMP'])->name('productos.materiales_mp');
    
    
    Route::get('/materia_prima/{mp_id}/codigo', [PedidoClienteController::class, 'getCodigoMp']);
    Route::get('/pedido_cliente/ultimo-nro-of', [PedidoClienteController::class, 'getUltimoNroOF']);
    Route::get('/productos/{id}/descripcion', [ProductoController::class, 'getDescripcionProducto']);
    
    // Rutas para las funcionalidades de Materia Prima
    // Rutas para los controladores de Materia Prima y DiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡metros
    Route::get('/mp_diametros', [MpDiametroController::class, 'index'])->name('mp_diametros.index');
    Route::get('/mp_materias_primas', [MpMateriaPrimaController::class, 'index'])->name('mp_materias_primas.index');
    Route::get('/mp_ingresos', [MpIngresoController::class, 'index'])->name('mp_ingresos.index');
    Route::get('/materia_prima/stock', [MpIngresoController::class, 'stock'])->name('mp_stock.index');
    Route::get('/mp_ingresos/filters', [MpIngresoController::class, 'getUniqueFilters'])->name('mp_ingresos.filters');
    Route::get('/mp_ingresos/data', [MpIngresoController::class, 'getData'])->name('mp_ingresos.data');
    Route::get('/mp_egresos/data', [MpEgresoController::class, 'getData'])->name('mp_egresos.data');
    Route::get('/mp_egresos', [MpEgresoController::class, 'index'])->name('mp_egresos.index');
    Route::get('/mp_salidas_iniciales/data', [MpSalidaInicialController::class, 'getData'])->name('mp_salidas_iniciales.data');
    Route::get('/mp_salidas_iniciales/editar-masivo/{id?}', [MpSalidaInicialController::class, 'editMassive'])->name('mp_salidas_iniciales.editMassive');
    Route::put('/mp_salidas_iniciales/actualizar-masivo', [MpSalidaInicialController::class, 'updateMassive'])->name('mp_salidas_iniciales.updateMassive');
    Route::get('/mp_movimientos_adicionales/data', [MpMovimientoAdicionalController::class, 'getData'])->name('mp_movimientos_adicionales.data');
    Route::get('/mp_movimientos_adicionales/deleted', [MpMovimientoAdicionalController::class, 'showDeleted'])->name('mp_movimientos_adicionales.deleted');
    Route::post('/mp_movimientos_adicionales/{id}/restore', [MpMovimientoAdicionalController::class, 'restore'])->name('mp_movimientos_adicionales.restore');
    Route::post('/mp_movimientos_adicionales/import-csv', [MpMovimientoAdicionalController::class, 'importLegacyCsv'])->name('mp_movimientos_adicionales.importCsv');
    Route::post('/mp_egresos/import-historico', [MpEgresoController::class, 'importHistoricCsv'])->name('mp_egresos.importHistoricCsv');
    Route::get('/mp_salidas_iniciales/deleted', [MpSalidaInicialController::class, 'showDeleted'])->name('mp_salidas_iniciales.deleted');
    Route::post('/mp_salidas_iniciales/{id}/restore', [MpSalidaInicialController::class, 'restore'])->name('mp_salidas_iniciales.restore');
    Route::post('/mp_salidas_iniciales/import-historico', [MpSalidaInicialController::class, 'importHistoricCsv'])->name('mp_salidas_iniciales.importHistoricCsv');
    Route::get('/mp_ingresos/ultimo_nro_ingreso', [MpIngresoController::class, 'getUltimoNroIngreso'])->name('mp_ingresos.ultimo_nro_ingreso');
    Route::get('/mp_ingresos/resumen', [MpIngresoController::class, 'resumenIngresos'])->name('mp_ingresos.resumen');
    // Otras rutas relacionadas
    Route::get('/productos_categoria', [ProductoCategoriaController::class, 'index'])->name('productos_categoria.index');
    Route::get('/entregas_productos', [ListadoEntregaProductoController::class, 'index'])->name('entregas_productos.index');
    Route::get('/pedido-cliente/get-id-producto/{nroOf}', [PedidoClienteController::class, 'getIdProductoPorNroOf']);
    
    // Rutas para fabricaciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n
    Route::get('fabricacion/show/{nroOF}', [RegistroDeFabricacionController::class, 'showByNroOF'])->name('fabricacion.showByNroOF');
    
    // Rutas para proveedores
    Route::get('proveedores/data', [ProveedorController::class, 'getData'])->name('proveedores.data');
    Route::get('/proveedores/resumen', [ProveedorController::class, 'resumen'])->name('proveedores.resumen');
    Route::get('/proveedores/deleted', [ProveedorController::class, 'showDeleted'])->name('proveedores.deleted');
    Route::post('/proveedores/{id}/restore', [ProveedorController::class, 'restore'])->name('proveedores.restore');

    
    Route::get('/materia_prima/diametro', [MpDiametroController::class, 'index'])->name('materia_prima.diametro.index');
    Route::get('mp_materia_prima/data', [MpMateriaPrimaController::class, 'getData'])->name('mp_materia_prima.data');
    Route::get('mp_materia_prima/resumen', [MpMateriaPrimaController::class, 'resumen'])->name('mp_materia_prima.resumen');
    Route::get('mp_diametro/resumen', [MpDiametroController::class, 'resumen'])->name('mp_diametro.resumen');
    
    Route::get('/mp_diametro/deleted', [MpDiametroController::class, 'showDeleted'])->name('mp_diametro.deleted');
    Route::post('/mp_diametro/{id}/restore', [MpDiametroController::class, 'restore'])->name('mp_diametro.restore');
    
    // Rutas para MpMateriaPrima
    Route::get('/mp_materias_primas/deleted', [MpMateriaPrimaController::class, 'showDeleted'])->name('mp_materias_primas.deleted');
    Route::post('/mp_materias_primas/{id}/restore', [MpMateriaPrimaController::class, 'restore'])->name('mp_materias_primas.restore');
    
    
    // Rutas para MpIngreso
    Route::get('/mp_ingresos/deleted', [MpIngresoController::class, 'showDeleted'])->name('mp_ingresos.deleted');
    // Modificado el 06-03-2026 -Route::post('/mp_ingresos/{id}/restore', [MpMateriaPrimaController::class, 'restore'])->name('mp_ingresos.restore');
    Route::post('/mp_ingresos/{id}/restore', [MpIngresoController::class, 'restore'])->name('mp_ingresos.restore');
    
    // Rutas para MarcasInsumos
    Route::get('/marcas_insumos/data', [MarcasInsumosController::class, 'getData'])->name('marcas_insumos.data');
    Route::get('marcas_insumos/data', [MarcasInsumosController::class, 'getData'])->name('marcas_insumos.data');
    Route::get('/marcas_insumos/filters', [MarcasInsumosController::class, 'getUniqueFilters'])->name('marcas_insumos.filters');
    Route::get('/marcas_insumos/resumen', [MarcasInsumosController::class, 'resumen'])->name('marcas_insumos.resumen');
    Route::get('/marcas_insumos/deleted', [MarcasInsumosController::class, 'showDeleted'])->name('marcas_insumos.deleted');
    Route::post('/marcas_insumos/{id}/restore', [MarcasInsumosController::class, 'restore'])->name('marcas_insumos.restore');
    
    
    // Rutas para EstadoPlanificacion
    Route::get('estado_planificacion/data', [EstadoPlanificacionController::class, 'getData'])->name('estado_planificacion.data');
    Route::get('estado_planificacion/filters', [EstadoPlanificacionController::class, 'getUniqueFilters'])->name('estado_planificacion.filters');
    Route::get('estado_planificacion/resumen', [EstadoPlanificacionController::class, 'resumen'])->name('estado_planificacion.resumen');
    Route::get('estado_planificacion/deleted', [EstadoPlanificacionController::class, 'showDeleted'])->name('estado_planificacion.deleted');
    Route::post('estado_planificacion/restore/{id}', [EstadoPlanificacionController::class, 'restore'])->name('estado_planificacion.restore');

    // Rutas para ProductoTipo
    Route::get('producto_tipo/data', [ProductoTipoController::class, 'getData'])->name('producto_tipo.data');
    Route::get('producto_tipo/filters', [ProductoTipoController::class, 'getUniqueFilters'])->name('producto_tipo.filters');
    Route::get('producto_tipo/resumen', [ProductoTipoController::class, 'resumen'])->name('producto_tipo.resumen');
    Route::get('producto_tipo/deleted', [ProductoTipoController::class, 'showDeleted'])->name('producto_tipo.deleted');
    Route::post('producto_tipo/restore/{id}', [ProductoTipoController::class, 'restore'])->name('producto_tipo.restore');

    // Rutas para ProductoCategoria
    Route::get('producto_categoria/data', [ProductoCategoriaController::class, 'getData'])->name('producto_categoria.data');
    Route::get('producto_categoria/filters', [ProductoCategoriaController::class, 'getUniqueFilters'])->name('producto_categoria.filters');
    Route::get('producto_categoria/resumen', [ProductoCategoriaController::class, 'resumen'])->name('producto_categoria.resumen');
    Route::get('producto_categoria/deleted', [ProductoCategoriaController::class, 'showDeleted'])->name('producto_categoria.deleted');
    Route::post('producto_categoria/restore/{id}', [ProductoCategoriaController::class, 'restore'])->name('producto_categoria.restore');

    // Rutas para ProductoSubcategoria
    Route::get('producto_subcategoria/data', [ProductoSubcategoriaController::class, 'getData'])->name('producto_subcategoria.data');
    Route::get('producto_subcategoria/filters', [ProductoSubcategoriaController::class, 'getUniqueFilters'])->name('producto_subcategoria.filters');
    Route::get('producto_subcategoria/resumen', [ProductoSubcategoriaController::class, 'resumen'])->name('producto_subcategoria.resumen');
    Route::get('producto_subcategoria/deleted', [ProductoSubcategoriaController::class, 'showDeleted'])->name('producto_subcategoria.deleted');
    Route::post('producto_subcategoria/restore/{id}', [ProductoSubcategoriaController::class, 'restore'])->name('producto_subcategoria.restore');

    Route::get('producto_grupo_subcategoria/data', [ProductoGrupoSubcategoriaController::class, 'getData'])->name('producto_grupo_subcategoria.data');
    Route::get('producto_grupo_subcategoria/filters', [ProductoGrupoSubcategoriaController::class, 'getUniqueFilters'])->name('producto_grupo_subcategoria.filters');
    Route::get('producto_grupo_subcategoria/resumen', [ProductoGrupoSubcategoriaController::class, 'resumen'])->name('producto_grupo_subcategoria.resumen');
    Route::get('producto_grupo_subcategoria/deleted', [ProductoGrupoSubcategoriaController::class, 'showDeleted'])->name('producto_grupo_subcategoria.deleted');
    Route::post('producto_grupo_subcategoria/restore/{id}', [ProductoGrupoSubcategoriaController::class, 'restore'])->name('producto_grupo_subcategoria.restore');

    Route::get('producto_grupo_conjuntos/data', [ProductoGrupoConjuntosController::class, 'getData'])->name('producto_grupo_conjuntos.data');
    Route::get('producto_grupo_conjuntos/filters', [ProductoGrupoConjuntosController::class, 'getUniqueFilters'])->name('producto_grupo_conjuntos.filters');
    Route::get('producto_grupo_conjuntos/resumen', [ProductoGrupoConjuntosController::class, 'resumen'])->name('producto_grupo_conjuntos.resumen');
    Route::get('producto_grupo_conjuntos/deleted', [ProductoGrupoConjuntosController::class, 'showDeleted'])->name('producto_grupo_conjuntos.deleted');
    Route::post('producto_grupo_conjuntos/restore/{id}', [ProductoGrupoConjuntosController::class, 'restore'])->name('producto_grupo_conjuntos.restore');


    // Rutas para los recursos
    Route::resource('profile', ProfileController::class)->only(['index', 'edit', 'show', 'update', 'destroy']);
    Route::resource('productos', ProductoController::class);
    Route::resource('mp_ingresos', MpIngresoController::class);
    Route::get('/mp_egresos/create-massive', [MpEgresoController::class, 'createMassive'])->name('mp_egresos.createMassive');
    Route::post('/mp_egresos/store-massive', [MpEgresoController::class, 'storeMassive'])->name('mp_egresos.storeMassive');
    Route::get('/mp_egresos/deleted', [MpEgresoController::class, 'showDeleted'])->name('mp_egresos.deleted');
    Route::post('/mp_egresos/{id}/restore', [MpEgresoController::class, 'restore'])->name('mp_egresos.restore');
    Route::resource('mp_egresos', MpEgresoController::class);
    Route::resource('mp_salidas_iniciales', MpSalidaInicialController::class);
    Route::resource('mp_movimientos_adicionales', MpMovimientoAdicionalController::class);
    Route::resource('mp_diametro', MpDiametroController::class);
    Route::resource('mp_materia_prima', MpMateriaPrimaController::class);
    Route::resource('producto_categoria', ProductoCategoriaController::class);
    Route::resource('fabricacion', RegistroDeFabricacionController::class);
    Route::resource('entregas_productos', ListadoEntregaProductoController::class);
    Route::resource('pedido_cliente', PedidoClienteController::class);
    Route::resource('pedido_cliente_mp', PedidoClienteMpController::class);
    Route::resource('fechas_of', FechasOfController::class);
    Route::resource('proveedores', ProveedorController::class);
    Route::resource('marcas_insumos', MarcasInsumosController::class);
    Route::resource('estado_planificacion', EstadoPlanificacionController::class);
    Route::resource('producto_tipo', ProductoTipoController::class);
    Route::resource('producto_subcategoria', ProductoSubcategoriaController::class);
    Route::resource('producto_grupo_subcategoria', ProductoGrupoSubcategoriaController::class);
    Route::resource('producto_grupo_conjuntos', ProductoGrupoConjuntosController::class);

// 08/03/2026*
// Rutas para el mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³dulo de ejemplo (descomentar si se implementa el controlador y modelo correspondiente)

Route::get('modulo/data', [ModuloController::class, 'getData'])->name('modulo.data');
Route::get('modulo/filters', [ModuloController::class, 'getUniqueFilters'])->name('modulo.filters');
Route::get('modulo/resumen', [ModuloController::class, 'resumen'])->name('modulo.resumen');
Route::get('modulo/deleted', [ModuloController::class, 'showDeleted'])->name('modulo.deleted');
Route::post('modulo/{id}/restore', [ModuloController::class, 'restore'])->name('modulo.restore');
Route::resource('modulo', ModuloController::class);

});

// Incluye las rutas de autenticaciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n (si existe un archivo auth.php)
require __DIR__ . '/auth.php';








