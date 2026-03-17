<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use Illuminate\Http\Request;
use App\Models\MarcasInsumos;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class MarcasInsumosController extends Controller
{
    use CheckForChanges;

    public function index()
    {
        $totalMarcas = MarcasInsumos::count();

        return view('marcas_insumos.index', compact('totalMarcas'));
    }

    public function resumen()
    {
        return response()->json([
            'total'      => MarcasInsumos::withTrashed()->count(),
            'activos'    => MarcasInsumos::where('reg_Status', 1)->count(),
            'eliminados' => MarcasInsumos::onlyTrashed()->count(),
        ]);
    }

    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = MarcasInsumos::query()
                ->leftJoin('proveedores', 'marcas_insumos.Id_Proveedor', '=', 'proveedores.Prov_Id');

            if ($request->filled('filtro_estado')) {
                $estado = $request->filtro_estado;

                if ($estado === '1' || strtolower($estado) === 'habilitado') {
                    $baseQuery->where('marcas_insumos.reg_Status', 1);
                } elseif ($estado === '0' || strtolower($estado) === 'deshabilitado') {
                    $baseQuery->where('marcas_insumos.reg_Status', 0);
                }
            }

            $proveedores = (clone $baseQuery)
                ->whereNotNull('proveedores.Prov_Nombre')
                ->distinct()
                ->orderBy('proveedores.Prov_Nombre')
                ->pluck('proveedores.Prov_Nombre')
                ->values();

            $status = collect(['Habilitado', 'Deshabilitado']);

            return response()->json([
                'proveedores' => $proveedores,
                'status'      => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getUniqueFilters MarcasInsumos: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los filtros únicos.'
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        try {
            $query = MarcasInsumos::leftJoin('proveedores', 'marcas_insumos.Id_Proveedor', '=', 'proveedores.Prov_Id')
                ->select([
                    'marcas_insumos.Id_Marca',
                    'marcas_insumos.Nombre_marca',
                    'marcas_insumos.reg_Status',
                    'marcas_insumos.created_at',
                    'marcas_insumos.updated_at',
                    'proveedores.Prov_Nombre as Proveedor',
                ])
                ->orderBy('marcas_insumos.Nombre_marca', 'asc');

            if ($request->filled('filtro_nombre_marca')) {
                $query->whereRaw(
                    'LOWER(marcas_insumos.Nombre_marca) LIKE ?',
                    ['%' . strtolower($request->filtro_nombre_marca) . '%']
                );
            }

            if ($request->filled('filtro_proveedor')) {
                $query->whereRaw(
                    'LOWER(proveedores.Prov_Nombre) = ?',
                    [strtolower($request->filtro_proveedor)]
                );
            }

            if ($request->filled('filtro_estado')) {
                $estado = $request->filtro_estado;

                if ($estado === '1' || strtolower($estado) === 'habilitado') {
                    $query->where('marcas_insumos.reg_Status', 1);
                } elseif ($estado === '0' || strtolower($estado) === 'deshabilitado') {
                    $query->where('marcas_insumos.reg_Status', 0);
                }
            }

            return datatables()->of($query)
                ->addColumn('Estado_Texto', function ($marca) {
                    return (int) $marca->reg_Status === 1 ? 'Habilitado' : 'Deshabilitado';
                })
                ->editColumn('created_at', function ($marca) {
                    return $marca->created_at
                        ? Carbon::parse($marca->created_at)->format('d/m/Y H:i')
                        : '-';
                })
                ->editColumn('updated_at', function ($marca) {
                    return $marca->updated_at
                        ? Carbon::parse($marca->updated_at)->format('d/m/Y H:i')
                        : '-';
                })
                ->addColumn('acciones', function ($marca) {
                    return '';
                })
                ->rawColumns(['acciones'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Error en getData MarcasInsumos: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los datos.'
            ], 500);
        }
    }

    public function show(MarcasInsumos $marcas_insumo)
    {
        $marcas_insumo->load(['proveedor', 'createdBy', 'updatedBy', 'deletedBy']);

        return view('marcas_insumos.show', [
            'marca' => $marcas_insumo
        ]);
    }

    public function create()
    {
        //$proveedores = Proveedor::where('Es_Proveedor_MP', 1)
        $proveedores = Proveedor::where('reg_Status', 1)
            ->orderBy('Prov_Nombre', 'asc')
            ->get();

        return view('marcas_insumos.create', compact('proveedores'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Nombre_marca' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('marcas_insumos', 'Nombre_marca')->whereNull('deleted_at'),
                ],
                'Id_Proveedor' => 'required|exists:proveedores,Prov_Id',
            ]);

            DB::beginTransaction();

            $validatedData['Nombre_marca'] = trim($validatedData['Nombre_marca']);
            $validatedData['created_by'] = Auth::id();
            $validatedData['reg_Status'] = 1;

            MarcasInsumos::create($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Marca de insumo creada exitosamente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear la marca de insumo', [
                'error'   => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la marca de insumo: ' . $e->getMessage()
            ], 400);
        }
    }

        public function edit($Id_Marca)
    {
        $marca = MarcasInsumos::findOrFail($Id_Marca);

        $proveedores = Proveedor::where('reg_Status', 1)
            ->orderBy('Prov_Nombre', 'asc')
            ->get();

        return view('marcas_insumos.edit', compact('marca', 'proveedores'));
    }

    public function update(Request $request, $id)
    {
        $marca = MarcasInsumos::findOrFail($id);

        $validatedData = $request->validate([
            'Nombre_marca' => [
                'required',
                'string',
                'max:255',
                Rule::unique('marcas_insumos', 'Nombre_marca')
                    ->ignore($id, 'Id_Marca')
                    ->whereNull('deleted_at'),
            ],
            'Id_Proveedor' => 'required|exists:proveedores,Prov_Id',
            'reg_Status'   => 'required|in:0,1',
        ]);

        $validatedData['Nombre_marca'] = trim($validatedData['Nombre_marca']);
        $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];

        return $this->updateIfChanged($marca, $validatedData, [
            'success_redirect'   => route('marcas_insumos.index'),
            'success_message'    => 'Marca de insumo actualizada correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by'     => true,
            'use_transaction'    => true,
            'normalize_data'     => false,
        ]);
    }

    public function destroy($Id_Marca)
    {
        try {
            DB::beginTransaction();

            $marca = MarcasInsumos::findOrFail($Id_Marca);

            $marca->deleted_by = Auth::id();
            $marca->save();

            $marca->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Marca de insumo eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al eliminar la marca de insumo', [
                'error'   => $e->getMessage(),
                'usuario' => Auth::id(),
                'id'      => $Id_Marca,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la marca de insumo.'
            ], 400);
        }
    }

    public function restore($id)
    {
        try {
            $marca = MarcasInsumos::withTrashed()->findOrFail($id);
            $marca->restore();

            return redirect()
                ->route('marcas_insumos.index')
                ->with('success', 'Marca de insumo restaurada con éxito');
        } catch (\Exception $e) {
            Log::error('Error al restaurar la marca de insumo', [
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('marcas_insumos.index')
                ->with('error', 'Error al restaurar la marca de insumo');
        }
    }

    public function showDeleted()
    {
        $marcasEliminadas = MarcasInsumos::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('marcas_insumos.deleted', compact('marcasEliminadas'));
    }
}