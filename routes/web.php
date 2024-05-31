<?php
// routes\web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MpIngresoController;
use App\Http\Controllers\MpSalidaController;
use App\Http\Controllers\ListadoOfController;
use App\Http\Controllers\ProductoCategoriaController;
use App\Http\Controllers\RegistroDeFabricacionController;
use App\Http\Controllers\ListadoEntregaProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionViewOnlyController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\PermissionController;

// Ruta por defecto al iniciar la app
Route::get('/', function () {
    return redirect()->route('login');
})->name('login');

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/home', [AdminHomeController::class, 'index'])->name('adminHome');
    Route::get('/materia_prima_ingresos', [MpIngresoController::class, 'index'])->name('materia_prima_ingresos.index');
    Route::get('/materia_prima_salidas', [MpSalidaController::class, 'index'])->name('materia_prima_salidas.index');
    Route::get('/listado_de_of', [ListadoOfController::class, 'index'])->name('listado_de_of.index');
    Route::get('/productos_categoria', [ProductoCategoriaController::class, 'index'])->name('productos_categoria.index');
    Route::get('/entregas_productos', [ListadoEntregaProductoController::class, 'index'])->name('entregas_productos.index');

    Route::get('/listado-of/get-id-producto/{nroOf}', [ListadoOfController::class, 'getIdProductoPorNroOf']);

    Route::resource('productos', ProductoController::class);
    Route::resource('mp_ingresos', MpIngresoController::class);
    Route::resource('categoria', ProductoCategoriaController::class);

    Route::put('/fabricacion/{id}', [RegistroDeFabricacionController::class, 'update'])->name('fabricacion.update');
    Route::delete('/fabricacion/{id}', [RegistroDeFabricacionController::class, 'destroy'])->name('fabricacion.destroy');
    Route::get('fabricacion/show/{nroOF}', [RegistroDeFabricacionController::class, 'showByNroOF'])->name('fabricacion.showByNroOF');
    Route::resource('fabricacion', RegistroDeFabricacionController::class);
    Route::resource('listado_de_entregas_productos', ListadoEntregaProductoController::class);
});

// Rutas protegidas por roles específicos
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::resource('users', UserController::class);
    Route::get('/admin', [ProfileController::class, 'index'])->name('admin.index');
    Route::resource('productos', ProductoController::class); // Asegúrate de incluir esta ruta aquí
});

Route::middleware(['auth', 'role:Producción'])->group(function () {
    Route::get('/produccion', [ProductionController::class, 'index'])->name('produccion.index');
    Route::resource('productos', ProductoController::class);
});

Route::middleware(['auth', 'role:Control de Calidad'])->group(function () {
    Route::get('/calidad', [QualityController::class, 'index'])->name('calidad.index');
});

Route::middleware(['auth', 'role:Producción View Only'])->group(function () {
    Route::get('/base-datos/productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('/programacion-produccion/listado', [ListadoOfController::class, 'index'])->name('listado_de_of.index');
    Route::get('/registro-fabricacion/listado', [RegistroDeFabricacionController::class, 'index'])->name('fabricacion.index');
});

Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RolePermissionController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RolePermissionController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RolePermissionController::class, 'destroy'])->name('roles.destroy');
});

// Rutas para permisos
Route::resource('permissions', PermissionController::class);

// Rutas para el perfil de usuario

Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');



require __DIR__.'/auth.php';
