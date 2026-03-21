<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\ProductoGrupoSubcategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductoGrupoSubcategoriaController extends Controller
{
    use CheckForChanges;

    public function index()
    {
        $totalGrupos = ProductoGrupoSubcategoria::count();

        return view('productos.grupos_subcategoria.index', compact('totalGrupos'));
    }

    public function resumen()
    {
        return response()->json([
            'total' => ProductoGrupoSubcategoria::withTrashed()->count(),
            'activos' => ProductoGrupoSubcategoria::where('reg_Status', 1)->count(),
            'eliminados' => ProductoGrupoSubcategoria::onlyTrashed()->count(),
        ]);
    }

    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = ProductoGrupoSubcategoria::query()
                ->leftJoin('productos as p', 'producto_grupo_subcategoria.Id_GrupoSubCategoria', '=', 'p.Id_Prod_GrupoSubcategoria')
                ->leftJoin('producto_subcategoria as ps', 'p.Id_Prod_SubCategoria', '=', 'ps.Id_SubCategoria');

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $baseQuery->where('producto_grupo_subcategoria.reg_Status', $status);
            }

            if ($request->filled('filtro_subcategoria')) {
                $baseQuery->where('ps.Nombre_SubCategoria', $request->filtro_subcategoria);
            }

            $nombres = (clone $baseQuery)->select('producto_grupo_subcategoria.Nombre_GrupoSubCategoria')
                ->distinct()
                ->orderBy('producto_grupo_subcategoria.Nombre_GrupoSubCategoria')
                ->pluck('producto_grupo_subcategoria.Nombre_GrupoSubCategoria')
                ->values();

            $subcategorias = (clone $baseQuery)->select('ps.Nombre_SubCategoria')
                ->whereNotNull('ps.Nombre_SubCategoria')
                ->distinct()
                ->orderBy('ps.Nombre_SubCategoria')
                ->pluck('ps.Nombre_SubCategoria')
                ->values();

            return response()->json([
                'nombres' => $nombres,
                'subcategorias' => $subcategorias,
                'status' => collect(['Activo', 'Inactivo']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getUniqueFilters ProductoGrupoSubcategoria: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar filtros unicos.'
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        try {
            $query = ProductoGrupoSubcategoria::query()
                ->leftJoin('productos as p', 'producto_grupo_subcategoria.Id_GrupoSubCategoria', '=', 'p.Id_Prod_GrupoSubcategoria')
                ->leftJoin('producto_subcategoria as ps', 'p.Id_Prod_SubCategoria', '=', 'ps.Id_SubCategoria')
                ->select([
                    'producto_grupo_subcategoria.Id_GrupoSubCategoria',
                    'producto_grupo_subcategoria.Nombre_GrupoSubCategoria',
                    'producto_grupo_subcategoria.reg_Status',
                    'producto_grupo_subcategoria.created_at',
                    'producto_grupo_subcategoria.updated_at',
                    DB::raw('MIN(ps.Nombre_SubCategoria) as Nombre_SubCategoria'),
                ])
                ->groupBy(
                    'producto_grupo_subcategoria.Id_GrupoSubCategoria',
                    'producto_grupo_subcategoria.Nombre_GrupoSubCategoria',
                    'producto_grupo_subcategoria.reg_Status',
                    'producto_grupo_subcategoria.created_at',
                    'producto_grupo_subcategoria.updated_at'
                )
                ->orderBy('producto_grupo_subcategoria.Nombre_GrupoSubCategoria', 'asc');

            if ($request->filled('filtro_nombre')) {
                $query->whereRaw('LOWER(producto_grupo_subcategoria.Nombre_GrupoSubCategoria) LIKE ?', ['%' . strtolower($request->filtro_nombre) . '%']);
            }

            if ($request->filled('filtro_subcategoria')) {
                $query->where('ps.Nombre_SubCategoria', $request->filtro_subcategoria);
            }

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $query->where('producto_grupo_subcategoria.reg_Status', $status);
            }

            return datatables()->of($query)
                ->addColumn('Status_Texto', function ($grupo) {
                    return (int) $grupo->reg_Status === 1 ? 'Activo' : 'Inactivo';
                })
                ->addColumn('acciones', function () {
                    return '';
                })
                ->rawColumns(['acciones'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error en getData ProductoGrupoSubcategoria: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los datos.'
            ], 500);
        }
    }

    public function create()
    {
        $ultimoGrupo = ProductoGrupoSubcategoria::orderBy('Id_GrupoSubCategoria', 'desc')->first();

        return view('productos.grupos_subcategoria.create', compact('ultimoGrupo'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Nombre_GrupoSubCategoria' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('producto_grupo_subcategoria', 'Nombre_GrupoSubCategoria')->whereNull('deleted_at'),
                ],
                'reg_Status' => 'required|in:0,1',
            ]);

            DB::beginTransaction();

            $validatedData['Nombre_GrupoSubCategoria'] = trim($validatedData['Nombre_GrupoSubCategoria']);
            $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];
            $validatedData['created_by'] = Auth::id();

            ProductoGrupoSubcategoria::create($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Grupo de subcategoria creado correctamente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear producto_grupo_subcategoria', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el grupo de subcategoria: ' . $e->getMessage()
            ], 400);
        }
    }

    public function show(string $id)
    {
        $producto_grupo_subcategoria = ProductoGrupoSubcategoria::findOrFail($id);

        return view('productos.grupos_subcategoria.show', compact('producto_grupo_subcategoria'));
    }

    public function edit(string $id)
    {
        $grupo = ProductoGrupoSubcategoria::findOrFail($id);

        return view('productos.grupos_subcategoria.edit', compact('grupo'));
    }

    public function update(Request $request, string $id)
    {
        $grupo = ProductoGrupoSubcategoria::findOrFail($id);

        $validatedData = $request->validate([
            'Nombre_GrupoSubCategoria' => [
                'required',
                'string',
                'max:100',
                Rule::unique('producto_grupo_subcategoria', 'Nombre_GrupoSubCategoria')
                    ->ignore($id, 'Id_GrupoSubCategoria')
                    ->whereNull('deleted_at'),
            ],
            'reg_Status' => 'required|in:0,1',
        ]);

        $validatedData['Nombre_GrupoSubCategoria'] = trim($validatedData['Nombre_GrupoSubCategoria']);
        $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];

        return $this->updateIfChanged($grupo, $validatedData, [
            'success_redirect' => route('producto_grupo_subcategoria.index'),
            'success_message' => 'Grupo de subcategoria actualizado correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by' => true,
            'use_transaction' => true,
            'normalize_data' => false,
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $grupo = ProductoGrupoSubcategoria::findOrFail($id);

            $grupo->deleted_by = Auth::id();
            $grupo->save();
            $grupo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Grupo de subcategoria eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar producto_grupo_subcategoria', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el grupo de subcategoria.'
            ], 400);
        }
    }

    public function showDeleted()
    {
        $gruposEliminados = ProductoGrupoSubcategoria::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('productos.grupos_subcategoria.deleted', compact('gruposEliminados'));
    }

    public function restore(string $id)
    {
        try {
            $grupo = ProductoGrupoSubcategoria::withTrashed()->findOrFail($id);
            $grupo->restore();

            return response()->json([
                'success' => true,
                'message' => 'Grupo de subcategoria restaurado con exito.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar producto_grupo_subcategoria', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el grupo de subcategoria.'
            ], 400);
        }
    }
}
