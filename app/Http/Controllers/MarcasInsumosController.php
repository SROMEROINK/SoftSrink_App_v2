<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarcasInsumos;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MarcasInsumosController extends Controller
{
    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = MarcasInsumos::query();

            if ($request->filled('filtro_proveedor')) {
                $baseQuery->whereHas('proveedor', function ($q) use ($request) {
                    $q->where('Prov_Nombre', $request->filtro_proveedor);
                });
            }

            $proveedores = MarcasInsumos::join('proveedores', 'marcas_insumos.Id_Proveedor', '=', 'proveedores.Prov_Id')
                ->distinct()
                ->pluck('proveedores.Prov_Nombre')
                ->sort()
                ->values();

            $marcas = $baseQuery->distinct()->pluck('marcas_insumos.Nombre_marca')->sort()->values();

            return response()->json([
                'proveedores' => $proveedores,
                'marcas' => $marcas
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en getUniqueFilters: ' . $e->getMessage());
            return response()->json(['error' => 'Error al recuperar los filtros únicos.'], 500);
        }
    }

    public function getData(Request $request)
{
    try {
        $marcas_insumos = MarcasInsumos::with(['proveedor'])
            ->join('proveedores', 'marcas_insumos.Id_Proveedor', '=', 'proveedores.Prov_Id')
            ->select(
                'marcas_insumos.Id_Marca',
                'marcas_insumos.Nombre_marca',
                'marcas_insumos.Id_Proveedor',
                'marcas_insumos.reg_Status',
                \DB::raw("DATE_FORMAT(marcas_insumos.created_at, '%d-%m-%Y %H:%i') as created_at"), // Formato personalizado
                \DB::raw("DATE_FORMAT(marcas_insumos.updated_at, '%d-%m-%Y %H:%i') as updated_at") // Formato personalizado
            )->orderBy('Nombre_marca', 'asc');

        // Filtro por proveedor
        if ($request->filled('filtro_proveedor')) {
            $marcas_insumos->whereRaw('LOWER(proveedores.Prov_Nombre) = ?', [strtolower($request->filtro_proveedor)]);
        }

        // Filtro por nombre de marca
        if ($request->filled('filtro_nombre_marca')) {
            $marcas_insumos->whereRaw('LOWER(marcas_insumos.Nombre_marca) LIKE ?', ['%' . strtolower($request->filtro_nombre_marca) . '%']);
        }

        // Filtro por estado
        if ($request->filled('filtro_estado')) {
            $marcas_insumos->where('marcas_insumos.reg_Status', $request->filtro_estado);
        }

        return datatables()->of($marcas_insumos)
            ->addColumn('Proveedor', function ($marca) {
                return $marca->proveedor ? $marca->proveedor->Prov_Nombre : 'No Asignado';
            })
            ->make(true);

    } catch (\Exception $e) {
        \Log::error('Error en getData: ' . $e->getMessage());
        return response()->json(['error' => 'Error al recuperar los datos.'], 500);
    }
}

    public function index()
    {
        $marcas_insumos = MarcasInsumos::paginate(10);
        $totalMarcas = MarcasInsumos::count();

        return view('marcas_insumos.index', compact('totalMarcas', 'marcas_insumos'));
    }

    public function show(MarcasInsumos $marca)
    {
        return view('marcas_insumos.show', compact('marca'));
    }

    public function create()
    {
        $proveedores = Proveedor::where('Es_Proveedor_MP', 1)->where('reg_Status', 1)->get();

        return view('marcas_insumos.create', compact('proveedores'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Nombre_marca' => 'required|string|max:255|unique:marcas_insumos',
                'Id_Proveedor' => 'required|exists:proveedores,Prov_Id',
            ]);

            \DB::beginTransaction();

            $validatedData['created_by'] = Auth::id();
            $validatedData['reg_Status'] = 1;
            MarcasInsumos::create($validatedData);

            \DB::commit();

            return response()->json(['success' => true, 'message' => 'Marca de insumo creada exitosamente.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al crear la marca de insumo:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al crear la marca de insumo: ' . $e->getMessage()], 400);
        }
    }

    public function edit($Id_Marca)
    {
        $marca = MarcasInsumos::findOrFail($Id_Marca);
        $proveedores = Proveedor::all(); // Obtener todos los proveedores
       // $proveedores = Proveedor::where('Es_Proveedor_MP', 1)->where('reg_Status', 1)->get();

        return view('marcas_insumos.edit', compact('marca', 'proveedores'));
    }

    public function update(Request $request, $id)
    {
        $marca = MarcasInsumos::findOrFail($id);

        $validatedData = $request->validate([
            'Nombre_marca' => 'required|string|max:255|unique:marcas_insumos,Nombre_marca,' . $id . ',Id_Marca',
            'Id_Proveedor' => 'required|exists:proveedores,Prov_Id',
            'reg_Status' => 'required|in:0,1',
        ]);

        $marca->fill($validatedData);

        if ($marca->isDirty()) {
            $marca->updated_by = Auth::id();
            $marca->save();
            return redirect()->route('marcas_insumos.index')->with('success', 'Marca de insumo actualizada correctamente.');
        } else {
            return back()->with('warning', 'No se realizaron cambios.');
        }
    }

    public function destroy($Id_Marca)
    {
        try {
            $marca = MarcasInsumos::findOrFail($Id_Marca);

            $marca->deleted_by = Auth::id();
            $marca->save();

            $marca->delete();

            return response()->json(['success' => 'Marca de insumo eliminada correctamente']);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar la marca de insumo:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al eliminar la marca de insumo'], 400);
        }
    }

    public function restore($id)
    {
        try {
            $marca = MarcasInsumos::withTrashed()->findOrFail($id);
            $marca->restore();

            return redirect()->route('marcas_insumos.index')->with('success', 'Marca de insumo restaurada con éxito');
        } catch (\Exception $e) {
            \Log::error('Error al restaurar la marca de insumo:', ['error' => $e->getMessage()]);
            return redirect()->route('marcas_insumos.index')->with('error', 'Error al restaurar la marca de insumo');
        }
    }

    public function showDeleted()
    {
        $marcasEliminadas = MarcasInsumos::onlyTrashed()->get();

        return view('marcas_insumos.deleted', ['marcasEliminadas' => $marcasEliminadas]);
    }
}
