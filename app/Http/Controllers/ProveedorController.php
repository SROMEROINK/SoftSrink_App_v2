<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use Illuminate\Http\Request;
use App\Models\Proveedor;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    use CheckForChanges;

    public function index()
    {

        return view('proveedores.index');
    }

    public function resumen()
    {
        return response()->json([
            'total'      => Proveedor::withTrashed()->count(),
            'activos'    => Proveedor::where('reg_Status', 1)->count(),
            'eliminados' => Proveedor::onlyTrashed()->count(),
        ]);
    }

    public function getData(Request $request)
    {
        try {
            $query = Proveedor::select([
                'Prov_Id',
                'Prov_Nombre',
                'Prov_Detalle',
                'Es_Proveedor_MP',
                'Es_Proveedor_Herramientas',
                'Nombre_Contacto',
                'Nro_Telefono',
                'reg_Status',
            ])->orderBy('Prov_Nombre', 'asc');

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
                ->addColumn('ProveedorMPTexto', function ($proveedor) {
                    return (int) $proveedor->Es_Proveedor_MP === 1 ? 'proveedor_mp' : '';
                })
                ->addColumn('ProveedorHerramientasTexto', function ($proveedor) {
                    return (int) $proveedor->Es_Proveedor_Herramientas === 1 ? 'proveedor_herramientas' : '';
                })
                ->addColumn('EstadoTexto', function ($proveedor) {
                    return (int) $proveedor->reg_Status === 1 ? 'Activo' : 'Inactivo';
                })
                ->addColumn('acciones', function ($proveedor) {
                    return '';
                })
                ->rawColumns(['acciones'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Error en getData Proveedor: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los datos.'
            ], 500);
        }
    }

        public function show($Prov_Id)
    {
        $proveedor = Proveedor::with(['createdBy', 'updatedBy', 'deletedBy'])
            ->findOrFail($Prov_Id);

        return view('proveedores.show', compact('proveedor'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'Prov_Nombre' => [
            'required',
            'string',
            'max:255',
            Rule::unique('proveedores', 'Prov_Nombre')->whereNull('deleted_at'),
        ],
        'Prov_Detalle' => 'required|string|max:255',
        'Nombre_Contacto' => 'required|string|max:255',
        'Nro_Telefono' => 'required|string|max:255',
        'Es_Proveedor_MP' => 'required|in:0,1',
        'Es_Proveedor_Herramientas' => 'required|in:0,1',
        'reg_Status' => 'required|in:0,1',
    ], [
        'Prov_Nombre.required' => 'El nombre del proveedor es obligatorio.',
        'Prov_Nombre.unique' => 'El nombre del proveedor ya existe.',
        'Prov_Detalle.required' => 'El detalle del proveedor es obligatorio.',
        'Nombre_Contacto.required' => 'El nombre de contacto es obligatorio.',
        'Nro_Telefono.required' => 'El número de teléfono es obligatorio.',
        'Es_Proveedor_MP.required' => 'Debe indicar si es proveedor de materia prima.',
        'Es_Proveedor_Herramientas.required' => 'Debe indicar si es proveedor de herramientas.',
        'reg_Status.required' => 'El estado del proveedor es obligatorio.',
    ]);

    try {
        DB::beginTransaction();

        $validatedData['Prov_Nombre'] = trim($validatedData['Prov_Nombre']);
        $validatedData['Prov_Detalle'] = trim($validatedData['Prov_Detalle']);
        $validatedData['Nombre_Contacto'] = trim($validatedData['Nombre_Contacto']);
        $validatedData['Nro_Telefono'] = trim($validatedData['Nro_Telefono']);
        $validatedData['created_by'] = Auth::id();

        Proveedor::create($validatedData);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor creado exitosamente.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Error al crear proveedor', [
            'error' => $e->getMessage(),
            'usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al crear el proveedor: ' . $e->getMessage()
        ], 400);
    }
}

    public function edit($Prov_Id)
    {
        $proveedor = Proveedor::findOrFail($Prov_Id);

        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
{
    $proveedor = Proveedor::findOrFail($id);

    $validatedData = $request->validate([
        'Prov_Nombre' => [
            'required',
            'string',
            'max:255',
            Rule::unique('proveedores', 'Prov_Nombre')
                ->ignore($id, 'Prov_Id')
                ->whereNull('deleted_at'),
        ],
        'Prov_Detalle' => 'required|string|max:255',
        'Nombre_Contacto' => 'required|string|max:255',
        'Nro_Telefono' => 'required|string|max:255',
        'Es_Proveedor_MP' => 'required|in:0,1',
        'Es_Proveedor_Herramientas' => 'required|in:0,1',
        'reg_Status' => 'required|in:0,1',
    ]);

    return $this->updateIfChanged($proveedor, $validatedData, [
        'success_redirect'   => route('proveedores.index'),
        'success_message'    => 'Proveedor actualizado correctamente.',
        'no_changes_message' => 'No se realizaron cambios.',
        'set_updated_by'     => true,
        'use_transaction'    => true,
    ]);
}

    public function restore($id)
    {
        $proveedor = Proveedor::withTrashed()->findOrFail($id);
        $proveedor->restore();

        return redirect()->route('proveedores.deleted')->with('success', 'Proveedor restaurado con éxito');
    }

    public function destroy($Prov_Id)
    {
        try {
            DB::beginTransaction();

            $proveedor = Proveedor::findOrFail($Prov_Id);
            $proveedor->deleted_by = Auth::id();
            $proveedor->save();
            $proveedor->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el proveedor.'
            ], 400);
        }
    }

    public function showDeleted()
    {
        $proveedores = Proveedor::onlyTrashed()->get();

        return view('proveedores.deleted', ['proveedores' => $proveedores]);
    }
}