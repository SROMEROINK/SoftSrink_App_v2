<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionController extends Controller
{
    public function index()
    {
        $users = User::all();
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles', 'users'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return redirect()->route('roles.index')->with('success', 'Rol creado con éxito.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array|exists:permissions,name',
        ]);

        $role->update(['name' => $validated['name']]);

        $permissions = Permission::whereIn('name', $validated['permissions'])->get();
        $role->syncPermissions($permissions);

        // Actualizar permisos de los usuarios que tienen este rol
        $users = User::role($role->name)->get();
        foreach ($users as $user) {
            $user->syncPermissions($user->getPermissionsViaRoles());
        }

        return redirect()->route('roles.index')->with('success', 'Rol actualizado con éxito.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado con éxito.');
    }
}
