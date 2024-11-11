<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MpEgreso;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Auth;

class MpEgresoController extends Controller
{
    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = MpEgreso::query();

            if ($request->filled('filtro_proveedor')) {
                $baseQuery->whereHas('proveedor', function ($q) use ($request) {
                    $q->where('Prov_Nombre', $request->filtro_proveedor);
                });
            }

            $proveedores = Proveedor::distinct()->pluck('Prov_Nombre')->sort()->values();

            // Añadir otros filtros si es necesario
            
            return response()->json(['proveedores' => $proveedores]);

        } catch (\Exception $e) {
            \Log::error('Error en getUniqueFilters: ' . $e->getMessage());
            return response()->json(['error' => 'Error al recuperar los filtros únicos.'], 500);
        }
    }

    public function getUltimoNroEgreso()
    {
        try {
            $ultimoNroEgreso = MpEgreso::orderBy('Id_Ingreso_MP', 'DESC')->value('Id_Ingreso_MP');
            $nuevoNroEgreso = $ultimoNroEgreso ? $ultimoNroEgreso + 1 : 1;

            return response()->json(['success' => true, 'nuevo_nro_egreso' => $nuevoNroEgreso]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getData(Request $request)
    {
        try {
            $egresos_mp = MpEgreso::with(['proveedor'])
                ->select(
                    'Id_Ingreso_MP',
                    'Id_OF_Salidas_MP',
                    'Cantidad_Unidades_MP',
                    'Cantidad_Unidades_MP_Preparadas',
                    'Cantidad_MP_Adicionales',
                    'Cant_Devoluciones',
                    'Total_Salidas_MP',
                    'Total_Mtros_Utilizados',
                    'Fecha_del_Pedido_Produccion',
                    'Responsable_Pedido_Produccion',
                    'Nro_Pedido_MP',
                    'Fecha_de_Entrega_Pedido_Calidad',
                    'Responsable_de_entrega_Calidad',
                    'created_at',
                    'updated_at'
                )->orderBy('Id_Ingreso_MP', 'desc');

            if ($request->filled('filtro_nro_of')) {
                $egresos_mp->where('Id_OF_Salidas_MP', '=', $request->filtro_nro_of);
            }

            if ($request->filled('filtro_proveedor')) {
                $egresos_mp->whereHas('proveedor', function ($query) use ($request) {
                    $query->whereRaw('LOWER(Prov_Nombre) = ?', [strtolower($request->filtro_proveedor)]);
                });
            }

            return datatables()->of($egresos_mp)->make(true);

        } catch (\Exception $e) {
            \Log::error('Error en getData: ' . $e->getMessage());
            return response()->json(['error' => 'Error al recuperar los datos.'], 500);
        }
    }

    public function index()
    {
        $totalEgresos = MpEgreso::count();
        $egresos_mp = MpEgreso::paginate(10);

        return view('materia_prima.egresos.index', compact('totalEgresos', 'egresos_mp'));
    }

    public function show(MpEgreso $mp_egreso)
    {
        return view('materia_prima.egresos.show', compact('mp_egreso'));
    }

    public function create()
    {
        $proveedores = Proveedor::where('Es_Proveedor_MP', 1)->where('reg_Status', 1)->get();

        return view('materia_prima.egresos.create', compact('proveedores'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Id_OF_Salidas_MP' => 'required|integer',
                'Cantidad_Unidades_MP' => 'required|integer',
                'Cantidad_Unidades_MP_Preparadas' => 'required|integer',
                'Cantidad_MP_Adicionales' => 'required|integer',
                'Cant_Devoluciones' => 'required|integer',
                'Total_Salidas_MP' => 'required|integer',
                'Total_Mtros_Utilizados' => 'required|numeric',
                'Fecha_del_Pedido_Produccion' => 'required|date',
                'Responsable_Pedido_Produccion' => 'sometimes|string|max:255',
                'Nro_Pedido_MP' => 'required|integer',
                'Fecha_de_Entrega_Pedido_Calidad' => 'required|date',
                'Responsable_de_entrega_Calidad' => 'sometimes|string|max:255',
            ]);

            \DB::beginTransaction();

            $validatedData['created_by'] = Auth::id();
            $validatedData['reg_Status'] = 1;
            MpEgreso::create($validatedData);

            \DB::commit();

            return response()->json(['success' => true, 'message' => 'Egreso de materia prima creado exitosamente.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al crear el egreso de materia prima:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al crear el egreso de materia prima: ' . $e->getMessage()], 400);
        }
    }

    public function edit($Id_Ingreso_MP)
    {
        $egreso = MpEgreso::findOrFail($Id_Ingreso_MP);
        $proveedores = Proveedor::where('Es_Proveedor_MP', 1)->where('reg_Status', 1)->get();

        return view('materia_prima.egresos.edit', compact('egreso', 'proveedores'));
    }

    public function update(Request $request, $id)
    {
        $egreso = MpEgreso::findOrFail($id);

        $validatedData = $request->validate([
            'Id_OF_Salidas_MP' => 'required|integer',
            'Cantidad_Unidades_MP' => 'required|integer',
            'Cantidad_Unidades_MP_Preparadas' => 'required|integer',
            'Cantidad_MP_Adicionales' => 'required|integer',
            'Cant_Devoluciones' => 'required|integer',
            'Total_Salidas_MP' => 'required|integer',
            'Total_Mtros_Utilizados' => 'required|numeric',
            'Fecha_del_Pedido_Produccion' => 'required|date',
            'Responsable_Pedido_Produccion' => 'sometimes|string|max:255',
            'Nro_Pedido_MP' => 'required|integer',
            'Fecha_de_Entrega_Pedido_Calidad' => 'required|date',
            'Responsable_de_entrega_Calidad' => 'sometimes|string|max:255',
            'reg_Status' => 'required|in:0,1',
        ]);

        $egreso->fill($validatedData);

        if ($egreso->isDirty()) {
            $egreso->updated_by = Auth::id();
            $egreso->save();
            return redirect()->route('mp_egresos.index')->with('success', 'Egreso de materia prima actualizado correctamente.');
        } else {
            return back()->with('warning', 'No se realizaron cambios.');
        }
    }

    public function destroy($Id_Ingreso_MP)
    {
        try {
            $egreso = MpEgreso::findOrFail($Id_Ingreso_MP);
            $egreso->deleted_by = Auth::id();
            $egreso->save();
            $egreso->delete();

            return response()->json(['success' => 'Egreso de materia prima eliminado correctamente']);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar el egreso de materia prima:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al eliminar el egreso de materia prima'], 400);
        }
    }

    public function restore($id)
    {
        try {
            $egreso = MpEgreso::withTrashed()->findOrFail($id);
            $egreso->restore();

            return redirect()->route('mp_egresos.index')->with('success', 'Egreso de materia prima restaurado con éxito');
        } catch (\Exception $e) {
            \Log::error('Error al restaurar el egreso de materia prima:', ['error' => $e->getMessage()]);
            return redirect()->route('mp_egresos.index')->with('error', 'Error al restaurar el egreso de materia prima');
        }
    }

    public function showDeleted()
    {
        $egresosEliminados = MpEgreso::onlyTrashed()->get();

        return view('materia_prima.egresos.deleted', ['egresosEliminados' => $egresosEliminados]);
    }
}
