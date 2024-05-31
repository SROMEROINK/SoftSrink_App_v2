<?php
// app\Http\Controllers\AuthController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return $this->authenticated($request, Auth::user());
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->hasRole('Administrador')) {
            return redirect()->route('admin.index');
        } elseif ($user->hasRole('Producción')) {
            return redirect()->route('produccion.index');
        } elseif ($user->hasRole('Control de Calidad')) {
            return redirect()->route('calidad.index');
        } elseif ($user->hasRole('Producción View Only')) {
            return redirect()->route('produccion.view.index');
        }

        return redirect('/home');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
