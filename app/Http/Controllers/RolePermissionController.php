<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
Use App\Models\User;

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
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array|nullable' // Permitir que 'permissions' sea null
        ]);
    
        // Actualizar el nombre del rol
        $role->update(['name' => $validated['name']]);
    
        // Sincronizar permisos del rol
        $role->syncPermissions($validated['permissions'] ?? []); // Usa un array vacío si no se proporcionan permisos
    
        return redirect()->route('roles.index')->with('success', 'Rol actualizado con éxito.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado con éxito.');
    }
}
