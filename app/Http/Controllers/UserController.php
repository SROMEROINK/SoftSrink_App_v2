<?php

// app\Http\Controllers\UserController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function show(string $id)
    {
        return view('user.profile', [
            'user' => User::findOrFail($id)
        ]);
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        Log::info('Datos recibidos para crear usuario:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|max:2048',
            'role' => 'required|string|exists:roles,name'
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
        } else {
            $path = 'default.jpg';
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'photo' => $path,
        ]);

        Log::info('Usuario creado:', $user->toArray());

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'Usuario creado con éxito.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8|confirmed',
        'photo' => 'nullable|image|max:10240',
        'role' => 'required|integer|exists:roles,id',
    ]);

    $changes = false;

    if ($user->name !== $validated['name']) {
        $user->name = $validated['name'];
        $changes = true;
    }

    if ($user->email !== $validated['email']) {
        $user->email = $validated['email'];
        $changes = true;
    }

    if (!empty($validated['password'])) {
        $user->password = Hash::make($validated['password']);
        $changes = true;
    }

    if ($request->has('remove_photo') && $request->boolean('remove_photo')) {
        $user->photo = null;
        $changes = true;
    } elseif ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('photos', 'public');
        $user->photo = $path;
        $changes = true;
    }

    if ($changes) {
        $user->save();
    }

    $roleName = Role::findById($validated['role'])->name;
    $user->syncRoles([$roleName]);

    return redirect()->route('users.index')->with('success', 'Usuario actualizado con éxito.');
}


    
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado con éxito.');
    }
}

