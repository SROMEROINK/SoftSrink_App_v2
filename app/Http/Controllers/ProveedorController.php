<?php

// app\Http\Controllers\ProveedorController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends Controller
{
  

public function getData(Request $request)
{
    if ($request->ajax()) {
        $query = Proveedor::select('Prov_Id', 'Prov_Nombre', 'Prov_Detalle', 'Es_Proveedor_MP', 'Es_Proveedor_Herramientas', 'Nombre_Contacto', 'Nro_Telefono', 'reg_Status')->orderBy('Prov_Nombre', 'asc');

        // Filtros dinámicos
        if ($request->filled('filtro_nombre')) {
            $query->where('Prov_Nombre', 'like', '%' . $request->filtro_nombre . '%');
        }
        if ($request->filled('filtro_detalle')) {
            $query->where('Prov_Detalle', 'like', '%' . $request->filtro_detalle . '%');
        }
        if ($request->filled('filtro_proveedor_mp')) {
            $query->where('Es_Proveedor_MP', $request->filtro_proveedor_mp);
        }
        if ($request->filled('filtro_proveedor_herramientas')) {
            $query->where('Es_Proveedor_Herramientas', $request->filtro_proveedor_herramientas);
        }
        if ($request->filled('filtro_estado')) {
            $query->where('reg_Status', $request->filtro_estado);
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function ($data) {
                return '<button type="button" class="btn btn-primary btn-sm">Editar</button>';
            })
            ->make(true);
    }
}

public function index()
{
    $totalProveedores = Proveedor::count(); // Cuenta todos los proveedores
    $deletedRouteUrl = route('proveedores.deleted'); // Asegura que se establezca el valor de la ruta eliminada
    return view('proveedores.index', compact('totalProveedores', 'deletedRouteUrl'));
}


public function create()
{
    $proveedores = Proveedor::all();  // Asume que tienes un modelo Proveedor y quieres cargar todos los proveedores existentes
    return view('proveedores.create', compact('proveedores'));
}

public function store(Request $request)
{
    $validatedData = $request->validate([
        'Prov_Nombre' => 'required|string|max:255|unique:proveedores,Prov_Nombre,NULL,Prov_Id,deleted_at,NULL',
        'Prov_Detalle' => 'required|string|max:255',
        'Nombre_Contacto' => 'required|string|max:255',
        'Nro_Telefono' => 'required|string|max:255',
        'reg_Status' => 'required|in:0,1', // Validación para el campo reg_Status
    ], [
        'Prov_Nombre.required' => 'El nombre del proveedor es obligatorio.',
        'Prov_Nombre.unique' => 'El nombre del proveedor ya existe, incluso entre los eliminados.',
        'Prov_Detalle.required' => 'El detalle del proveedor es obligatorio.',
        'Nombre_Contacto.required' => 'El nombre de contacto es obligatorio.',
        'Nro_Telefono.required' => 'El número de teléfono es obligatorio.',
        'reg_Status.required' => 'El estado del proveedor es obligatorio.',
    ]);

    try {
        \DB::beginTransaction();
        $validatedData['created_by'] = Auth::id(); // Asignar el ID del usuario que crea el registro
        $proveedor = Proveedor::create($validatedData);
        \DB::commit();
        return response()->json(['success' => true, 'message' => 'Proveedor creado exitosamente.']);
    } catch (\Exception $e) {
        \DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Error al crear el proveedor: ' . $e->getMessage()], 400);
    }
}


public function edit($Prov_Id) {
    $proveedor = Proveedor::findOrFail($Prov_Id);
    return view('proveedores.edit', compact('proveedor'));
}

public function update(Request $request, $id) {
    $proveedor = Proveedor::findOrFail($id);

    // Validación de los datos
    $validatedData = $request->validate([
        'Prov_Nombre' => 'required|string|max:255',
        'Prov_Detalle' => 'required|string|max:255',
        'Nombre_Contacto' => 'required|string|max:255',
        'Nro_Telefono' => 'required|string|max:255',
        'reg_Status' => 'required|in:0,1', // Validación para el campo reg_Status
    ]);

    // Comparar los cambios
    $proveedor->fill($validatedData);
    if ($proveedor->isDirty()) {
        $proveedor->updated_by = Auth::id(); // Asignar el ID del usuario que actualiza el registro
        $proveedor->save();
        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    } else {
        return back()->with('warning', 'No se realizaron cambios.');
    }
}

public function restore($id)
{
    $proveedor = Proveedor::withTrashed()->findOrFail($id);
    $proveedor->restore();
    return redirect()->route('proveedores.deleted')->with('success', 'Proveedor restaurado con éxito');
}



public function destroy($Prov_Id) {
    try {
        $proveedor = Proveedor::findOrFail($Prov_Id);
        $proveedor->deleted_by = Auth::id(); // Asignar el ID del usuario que elimina el registro
        $proveedor->save(); // Guardar el cambio antes de eliminar
        $proveedor->delete();
        return response()->json(['success' => 'Proveedor eliminado correctamente']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error al eliminar el proveedor']);
    }
}

 // Método para mostrar proveedores eliminados
 public function showDeleted()
 {
     $proveedores = Proveedor::onlyTrashed()->get(); // Recupera solo los proveedores eliminados
     return view('proveedores.deleted', ['proveedores' => $proveedores]);
 }

}

