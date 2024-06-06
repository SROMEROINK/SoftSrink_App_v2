<?php

namespace App\Http\Controllers;
// app\Http\Controllers\PermissionController.php
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::paginate(10);
        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions'
        ]);

        Permission::create(['name' => $validated['name']]);

        return response()->json(['success' => true, 'message' => 'Permiso creado con éxito.']);
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id
        ]);

        $permission->update(['name' => $validated['name']]);

        return response()->json(['success' => true, 'message' => 'Permiso actualizado con éxito.']);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(['success' => true, 'message' => 'Permiso eliminado con éxito.']);
    }
}
