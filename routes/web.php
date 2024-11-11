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
use App\Http\Controllers\MpDiametroController;
use App\Http\Controllers\MpMateriaPrimaController;
use App\Http\Controllers\PedidoClienteController;
use App\Http\Controllers\ProductoCategoriaController;
use App\Http\Controllers\RegistroDeFabricacionController;
use App\Http\Controllers\FechasOfController;
use App\Http\Controllers\ListadoEntregaProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\MarcasInsumosController;

// Ruta por defecto al iniciar la app
Route::get('/', function () {
    return redirect()->route('login');
})->name('default_login');

// Rutas para usuarios autenticados
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('home');
    })->name('dashboard');

    // Rutas protegidas por roles específicos (Administrador)
    Route::middleware(['role:Administrador'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RolePermissionController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // Ruta específica para DataTables y otras funcionalidades
    Route::get('productos/data', [ProductoController::class, 'getData'])->name('productos.data');
    Route::get('pedido_cliente/data', [PedidoClienteController::class, 'getData'])->name('pedido_cliente.data');
    Route::get('fabricacion/data', [RegistroDeFabricacionController::class, 'getData'])->name('fabricacion.data');
    Route::get('fechas_of/data', [FechasOfController::class, 'getData'])->name('fechas_of.data');
    Route::get('/entregas_productos/data', [ListadoEntregaProductoController::class, 'getData'])->name('entregas_productos.data');
    Route::get('/fabricacion/withFiltro', [RegistroDeFabricacionController::class, 'indexWithFiltro'])->name('fabricacion.withFiltro');
    Route::get('proveedores/data', [ProveedorController::class, 'getData'])->name('proveedores.data');
    Route::get('mp_diametro/data', [MpDiametroController::class, 'getData'])->name('mp_diametro.data');
    Route::get('mp_materia_prima/data', [MpMateriaPrimaController::class, 'getData'])->name('mp_materia_prima.data');
    Route::get('marcas_insumos/data', [MarcasInsumosController::class, 'getData'])->name('marcas_insumos.data');
    



    Route::get('/productos/codigos', [ProductoController::class, 'getCodigosProducto']);

    // Rutas para obtener categorías y subcategorías
    Route::get('/productos/categorias', [ProductoController::class, 'getCategorias'])->name('productos.categorias');
    Route::get('/productos/subcategorias', [ProductoController::class, 'getSubcategoriasPorFamilia'])->name('productos.subcategorias');
    Route::get('/productos/grupos', [ProductoController::class, 'getGruposPorSubcategoria'])->name('productos.grupos');
    Route::get('productos/familias', [ProductoController::class, 'getFamilias'])->name('productos.Familias');
    Route::get('/productos/tipos', [ProductoController::class, 'getTipos'])->name('productos.Tipos');
    Route::get('/productos/unique-filters', [ProductoController::class, 'getUniqueFilters'])->name('productos.getUniqueFilters');
    Route::get('/productos/clientes', [ProductoController::class, 'getClientes'])->name('productos.Clientes');


    
    Route::get('/materia_prima/{mp_id}/codigo', [PedidoClienteController::class, 'getCodigoMp']);
    Route::get('/pedido_cliente/ultimo-nro-of', [PedidoClienteController::class, 'getUltimoNroOF']);
    Route::get('/productos/{id}/descripcion', [ProductoController::class, 'getDescripcionProducto']);

    // Rutas para las funcionalidades de Materia Prima
    // Rutas para los controladores de Materia Prima y Diámetros
    Route::get('/mp_diametros', [MpDiametroController::class, 'index'])->name('mp_diametros.index');
    Route::get('/mp_materias_primas', [MpMateriaPrimaController::class, 'index'])->name('mp_materias_primas.index');
    Route::get('/mp_ingresos', [MpIngresoController::class, 'index'])->name('mp_ingresos.index');
    Route::get('/mp_ingresos/filters', [MpIngresoController::class, 'getUniqueFilters'])->name('mp_ingresos.filters');
    Route::get('/mp_ingresos/data', [MpIngresoController::class, 'getData'])->name('mp_ingresos.data');
    Route::get('/mp_egresos/data', [MpEgresoController::class, 'getData'])->name('mp_egresos.data');
    Route::get('/mp_egresos', [MpEgresoController::class, 'index'])->name('mp_egresos.index');
    Route::get('/mp_ingresos/ultimo_nro_ingreso', [MpIngresoController::class, 'getUltimoNroIngreso'])->name('mp_ingresos.ultimo_nro_ingreso');

    // Otras rutas relacionadas
    Route::get('/productos_categoria', [ProductoCategoriaController::class, 'index'])->name('productos_categoria.index');
    Route::get('/entregas_productos', [ListadoEntregaProductoController::class, 'index'])->name('entregas_productos.index');
    Route::get('/pedido-cliente/get-id-producto/{nroOf}', [PedidoClienteController::class, 'getIdProductoPorNroOf']);

    // Rutas para fabricación
    Route::get('fabricacion/show/{nroOF}', [RegistroDeFabricacionController::class, 'showByNroOF'])->name('fabricacion.showByNroOF');


    Route::get('/proveedores/deleted', [ProveedorController::class, 'showDeleted'])->name('proveedores.deleted');
    Route::post('/proveedores/{id}/restore', [ProveedorController::class, 'restore'])->name('proveedores.restore');

    Route::get('/materia_prima/diametro', [MpDiametroController::class, 'index'])->name('materia_prima.diametro.index');

    Route::get('/mp_diametro/deleted', [MpDiametroController::class, 'showDeleted'])->name('mp_diametro.deleted');
    Route::post('/mp_diametro/{id}/restore', [MpDiametroController::class, 'restore'])->name('mp_diametro.restore');
    
    // Rutas para MpMateriaPrima
    Route::get('/mp_materias_primas/deleted', [MpMateriaPrimaController::class, 'showDeleted'])->name('mp_materias_primas.deleted');
    Route::post('/mp_materias_primas/{id}/restore', [MpMateriaPrimaController::class, 'restore'])->name('mp_materias_primas.restore');

    
    // Rutas para MpIngreso
    Route::get('/mp_ingresos/deleted', [MpIngresoController::class, 'showDeleted'])->name('mp_ingresos.deleted');
    Route::post('/mp_ingresos/{id}/restore', [MpMateriaPrimaController::class, 'restore'])->name('mp_ingresos.restore');
    
    // Rutas para MarcasInsumos
    Route::get('marcas_insumos/deleted', [MarcasInsumosController::class, 'showDeleted'])->name('marcas_insumos.deleted');
    Route::post('marcas_insumos/{id}/restore', [MarcasInsumosController::class, 'restore'])->name('marcas_insumos.restore');
    Route::get('marcas_insumos/unique-filters', [MarcasInsumosController::class, 'getUniqueFilters'])->name('marcas_insumos.getUniqueFilters');
   
    // Rutas para MarcasInsumos
    Route::get('mp_egresos/deleted', [MpEgresoController::class, 'showDeleted'])->name('mp_egresos.deleted');
    Route::post('mp_egresos/{id}/restore', [MpEgresoController::class, 'restore'])->name('mp_egresos.restore');
    Route::get('mp_egresos/unique-filters', [MpEgresoController::class, 'getUniqueFilters'])->name('mp_egresos.getUniqueFilters');

    // Rutas para los recursos
    Route::resource('profile', ProfileController::class)->only(['index', 'edit', 'show', 'update', 'destroy']);
    Route::resource('productos', ProductoController::class);
    Route::resource('mp_ingresos', MpIngresoController::class);
    Route::resource('mp_egresos', MpEgresoController::class);
    Route::resource('mp_diametro', MpDiametroController::class);
    Route::resource('mp_materia_prima', MpMateriaPrimaController::class);
    Route::resource('categoria', ProductoCategoriaController::class);
    Route::resource('fabricacion', RegistroDeFabricacionController::class);
    Route::resource('entregas_productos', ListadoEntregaProductoController::class);
    Route::resource('pedido_cliente', PedidoClienteController::class);
    Route::resource('fechas_of', FechasOfController::class);
    Route::resource('proveedores', ProveedorController::class);
    Route::resource('marcas_insumos', MarcasInsumosController::class);



});

// Incluye las rutas de autenticación (si existe un archivo auth.php)
require __DIR__ . '/auth.php';
