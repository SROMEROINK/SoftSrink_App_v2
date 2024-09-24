<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MpIngresoController;
use App\Http\Controllers\MpSalidaController;
use App\Http\Controllers\ListadoOfController;
use App\Http\Controllers\ProductoCategoriaController;
use App\Http\Controllers\RegistroDeFabricacionController;
use App\Http\Controllers\FechasOfController;
use App\Http\Controllers\ListadoEntregaProductoController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\AjaxController;

// Ruta por defecto al iniciar la app
Route::get('/', function () {
    return redirect()->route('login');
})->name('default_login');

//Modificado el dia 12/9/2024, porque no existe el controlador FabricacionController
//Route::post('/fabricacion/check-nro-of-parcial', [FabricacionController::class, 'checkNroOFParcial']);


// Ruta específica para DataTables

Route::get('productos/data', [ProductoController::class, 'getData'])->name('productos.data');
Route::get('listado_of/data', [ListadoOFController::class, 'getData'])->name('listado_of.data');
Route::get('fabricacion/data', [RegistroDeFabricacionController::class, 'getData'])->name('fabricacion.data');
Route::get('fechas_of/data', [FechasOfController::class, 'getData'])->name('fechas_of.data');
Route::get('/entregas_productos/data', [ListadoEntregaProductoController::class, 'getData'])->name('entregas_productos.data');
Route::get('/fabricacion/withFiltro', [RegistroDeFabricacionController::class, 'indexWithFiltro'])->name('fabricacion.withFiltro');



// Rutas para usuarios autenticados
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('home');
    })->name('dashboard');

    Route::resource('profile', ProfileController::class)->only(['index', 'edit', 'show', 'update', 'destroy']);
    
    //Modificado el dia 12/9/2024, porque no existe el controlador Admin\HomeController
    Route::get('/home', [AdminHomeController::class, 'index'])->name('adminHome');

    Route::resource('productos', ProductoController::class);
    Route::resource('mp_ingresos', MpIngresoController::class);
    Route::resource('categoria', ProductoCategoriaController::class);
    Route::resource('fabricacion', RegistroDeFabricacionController::class);
    Route::resource('entregas_productos', ListadoEntregaProductoController::class);
    Route::resource('listado_of', ListadoOfController::class);
    Route::resource('fechas_of', FechasOfController::class);
    
    Route::get('/materia_prima_ingresos', [MpIngresoController::class, 'index'])->name('materia_prima_ingresos.index');
    Route::get('/materia_prima_salidas', [MpSalidaController::class, 'index'])->name('materia_prima_salidas.index');
    // Route::get('/listado_of', [ListadoOfController::class, 'index'])->name('listado_de_of.index');
    Route::get('/productos_categoria', [ProductoCategoriaController::class, 'index'])->name('productos_categoria.index');
    Route::get('/entregas_productos', [ListadoEntregaProductoController::class, 'index'])->name('entregas_productos.index');
    Route::get('/listado-of/get-id-producto/{nroOf}', [ListadoOfController::class, 'getIdProductoPorNroOf']);
});

// Rutas para fabricación showByNroOF
Route::get('fabricacion/show/{nroOF}', [RegistroDeFabricacionController::class, 'showByNroOF'])->name('fabricacion.showByNroOF');



// Rutas protegidas por roles específicos
Route::middleware(['role:Administrador'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('roles', RolePermissionController::class);
    Route::resource('permissions', PermissionController::class);
    // Ya está definida en el grupo de rutas autenticadas
    // Route::resource('productos', ProductoController::class);
});



// Route::get('/fabricacion/data1', function () {
//     $registros_fabricacion = RegistroDeFabricacion::all();
//     return view('Fabricacion.index', compact('registros_fabricacion'));
// });

require __DIR__.'/auth.php';
