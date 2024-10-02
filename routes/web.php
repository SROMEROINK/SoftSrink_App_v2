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
use App\Http\Controllers\PedidoClienteController;
use App\Http\Controllers\ProductoCategoriaController;
use App\Http\Controllers\RegistroDeFabricacionController;
use App\Http\Controllers\FechasOfController;
use App\Http\Controllers\ListadoEntregaProductoController;
use App\Http\Controllers\AjaxController;

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
    Route::get('/productos/codigos', [ProductoController::class, 'getCodigosProducto']);

    // Rutas para obtener categorías y subcategorías
    Route::get('/productos/categorias', [ProductoController::class, 'getCategorias']);
    Route::get('/productos/subcategorias', [ProductoController::class, 'getSubcategorias'])->name('productos.subcategorias');
    Route::get('/materia_prima/{mp_id}/codigo', [PedidoClienteController::class, 'getCodigoMp']);
    Route::get('/pedido_cliente/ultimo-nro-of', [PedidoClienteController::class, 'getUltimoNroOF']);
    Route::get('/productos/{id}/descripcion', [ProductoController::class, 'getDescripcionProducto']);

    // Rutas para las funcionalidades de Materia Prima
    Route::get('/mp_ingresos', [MpIngresoController::class, 'index'])->name('mp_ingresos.index');
    Route::get('/mp_ingresos/filters', [MpIngresoController::class, 'getUniqueFilters'])->name('mp_ingresos.filters');
    Route::get('/mp_ingresos/data', [MpIngresoController::class, 'getData'])->name('mp_ingresos.data');
    Route::get('/mp_egresos', [MpEgresoController::class, 'index'])->name('mp_egresos.index');

    // Otras rutas relacionadas
    Route::get('/productos_categoria', [ProductoCategoriaController::class, 'index'])->name('productos_categoria.index');
    Route::get('/entregas_productos', [ListadoEntregaProductoController::class, 'index'])->name('entregas_productos.index');
    Route::get('/pedido-cliente/get-id-producto/{nroOf}', [PedidoClienteController::class, 'getIdProductoPorNroOf']);

    // Rutas para fabricación
    Route::get('fabricacion/show/{nroOF}', [RegistroDeFabricacionController::class, 'showByNroOF'])->name('fabricacion.showByNroOF');

    // Rutas para los recursos
    Route::resource('profile', ProfileController::class)->only(['index', 'edit', 'show', 'update', 'destroy']);
    Route::resource('productos', ProductoController::class);
    Route::resource('mp_ingresos', MpIngresoController::class);
    Route::resource('mp_egresos', MpEgresoController::class);
    Route::resource('categoria', ProductoCategoriaController::class);
    Route::resource('fabricacion', RegistroDeFabricacionController::class);
    Route::resource('entregas_productos', ListadoEntregaProductoController::class);
    Route::resource('pedido_cliente', PedidoClienteController::class);
    Route::resource('fechas_of', FechasOfController::class);
});

// Incluye las rutas de autenticación (si existe un archivo auth.php)
require __DIR__ . '/auth.php';
