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
use App\Http\Controllers\ListadoEntregaProductoController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;

// Ruta por defecto al iniciar la app
Route::get('/', function () {
    return redirect()->route('login');
})->name('default_login');

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Rutas para usuarios autenticados
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('home');
    })->name('dashboard');

    Route::resource('profile', ProfileController::class)->only(['index', 'edit', 'show', 'update', 'destroy']);
    Route::get('/home', [AdminHomeController::class, 'index'])->name('adminHome');
    Route::resource('productos', ProductoController::class);
    Route::resource('mp_ingresos', MpIngresoController::class);
    Route::resource('categoria', ProductoCategoriaController::class);
    Route::resource('fabricacion', RegistroDeFabricacionController::class);
    Route::resource('listado_de_entregas_productos', ListadoEntregaProductoController::class);
    
    Route::get('/materia_prima_ingresos', [MpIngresoController::class, 'index'])->name('materia_prima_ingresos.index');
    Route::get('/materia_prima_salidas', [MpSalidaController::class, 'index'])->name('materia_prima_salidas.index');
    Route::get('/listado_de_of', [ListadoOfController::class, 'index'])->name('listado_de_of.index');
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

// Rutas de prueba
Route::get('/check-user-permissions', function () {
    $user = \App\Models\User::find(1); // Cambia el ID del usuario según sea necesario
    return [
        'roles' => $user->roles->pluck('name'),
        'permissions_via_roles' => $user->getPermissionsViaRoles()->pluck('name'),
        'all_permissions' => $user->getAllPermissions()->pluck('name'),
    ];
});

Route::get('/assign-permission', function () {
    $user = \App\Models\User::find(1); // Cambia el ID del usuario según sea necesario
    $user->givePermissionTo('ver produccion');
    return "Permiso asignado";
});

require __DIR__.'/auth.php';
