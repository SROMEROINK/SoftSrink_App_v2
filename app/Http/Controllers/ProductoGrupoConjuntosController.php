<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\ProductoGrupoConjuntos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductoGrupoConjuntosController extends Controller
{
    use CheckForChanges;

    public function index()
    {
        $totalGrupos = ProductoGrupoConjuntos::count();

        return view('productos.grupos_conjuntos.index', compact('totalGrupos'));
    }

    public function resumen()
    {
        return response()->json([
            'total' => ProductoGrupoConjuntos::withTrashed()->count(),
            'activos' => ProductoGrupoConjuntos::where('reg_Status', 1)->count(),
            'eliminados' => ProductoGrupoConjuntos::onlyTrashed()->count(),
        ]);
    }

    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = ProductoGrupoConjuntos::query()
                ->leftJoin('productos as p', 'producto_grupo_conjuntos.Id_GrupoConjuntos', '=', 'p.Id_Prod_GrupoConjuntos')
                ->leftJoin('producto_subcategoria as ps', 'p.Id_Prod_SubCategoria', '=', 'ps.Id_SubCategoria');

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $baseQuery->where('producto_grupo_conjuntos.reg_Status', $status);
            }

            if ($request->filled('filtro_subcategoria')) {
                $baseQuery->where('ps.Nombre_SubCategoria', $request->filtro_subcategoria);
            }

            $nombres = (clone $baseQuery)->select('producto_grupo_conjuntos.Nombre_GrupoConjuntos')
                ->distinct()
                ->orderBy('producto_grupo_conjuntos.Nombre_GrupoConjuntos')
                ->pluck('producto_grupo_conjuntos.Nombre_GrupoConjuntos')
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
            Log::error('Error en getUniqueFilters ProductoGrupoConjuntos: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar filtros unicos.'
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        try {
            $query = ProductoGrupoConjuntos::query()
                ->leftJoin('productos as p', 'producto_grupo_conjuntos.Id_GrupoConjuntos', '=', 'p.Id_Prod_GrupoConjuntos')
                ->leftJoin('producto_subcategoria as ps', 'p.Id_Prod_SubCategoria', '=', 'ps.Id_SubCategoria')
                ->select([
                    'producto_grupo_conjuntos.Id_GrupoConjuntos',
                    'producto_grupo_conjuntos.Nombre_GrupoConjuntos',
                    'producto_grupo_conjuntos.reg_Status',
                    'producto_grupo_conjuntos.created_at',
                    'producto_grupo_conjuntos.updated_at',
                    DB::raw('MIN(ps.Nombre_SubCategoria) as Nombre_SubCategoria'),
                ])
                ->groupBy(
                    'producto_grupo_conjuntos.Id_GrupoConjuntos',
                    'producto_grupo_conjuntos.Nombre_GrupoConjuntos',
                    'producto_grupo_conjuntos.reg_Status',
                    'producto_grupo_conjuntos.created_at',
                    'producto_grupo_conjuntos.updated_at'
                )
                ->orderBy('producto_grupo_conjuntos.Nombre_GrupoConjuntos', 'asc');

            if ($request->filled('filtro_nombre')) {
                $query->whereRaw('LOWER(producto_grupo_conjuntos.Nombre_GrupoConjuntos) LIKE ?', ['%' . strtolower($request->filtro_nombre) . '%']);
            }

            if ($request->filled('filtro_subcategoria')) {
                $query->where('ps.Nombre_SubCategoria', $request->filtro_subcategoria);
            }

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $query->where('producto_grupo_conjuntos.reg_Status', $status);
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
            Log::error('Error en getData ProductoGrupoConjuntos: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los datos.'
            ], 500);
        }
    }

    public function create()
    {
        $ultimoGrupo = ProductoGrupoConjuntos::orderBy('Id_GrupoConjuntos', 'desc')->first();

        return view('productos.grupos_conjuntos.create', compact('ultimoGrupo'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Nombre_GrupoConjuntos' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('producto_grupo_conjuntos', 'Nombre_GrupoConjuntos')->whereNull('deleted_at'),
                ],
                'reg_Status' => 'required|in:0,1',
            ]);

            DB::beginTransaction();

            $validatedData['Nombre_GrupoConjuntos'] = trim($validatedData['Nombre_GrupoConjuntos']);
            $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];
            $validatedData['created_by'] = Auth::id();

            ProductoGrupoConjuntos::create($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Grupo de conjuntos creado correctamente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear producto_grupo_conjuntos', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el grupo de conjuntos: ' . $e->getMessage()
            ], 400);
        }
    }

    public function show(string $id)
    {
        $producto_grupo_conjuntos = ProductoGrupoConjuntos::findOrFail($id);

        return view('productos.grupos_conjuntos.show', compact('producto_grupo_conjuntos'));
    }

    public function edit(string $id)
    {
        $grupo = ProductoGrupoConjuntos::findOrFail($id);

        return view('productos.grupos_conjuntos.edit', compact('grupo'));
    }

    public function update(Request $request, string $id)
    {
        $grupo = ProductoGrupoConjuntos::findOrFail($id);

        $validatedData = $request->validate([
            'Nombre_GrupoConjuntos' => [
                'required',
                'string',
                'max:100',
                Rule::unique('producto_grupo_conjuntos', 'Nombre_GrupoConjuntos')
                    ->ignore($id, 'Id_GrupoConjuntos')
                    ->whereNull('deleted_at'),
            ],
            'reg_Status' => 'required|in:0,1',
        ]);

        $validatedData['Nombre_GrupoConjuntos'] = trim($validatedData['Nombre_GrupoConjuntos']);
        $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];

        return $this->updateIfChanged($grupo, $validatedData, [
            'success_redirect' => route('producto_grupo_conjuntos.index'),
            'success_message' => 'Grupo de conjuntos actualizado correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by' => true,
            'use_transaction' => true,
            'normalize_data' => false,
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $grupo = ProductoGrupoConjuntos::findOrFail($id);

            $grupo->deleted_by = Auth::id();
            $grupo->save();
            $grupo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Grupo de conjuntos eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar producto_grupo_conjuntos', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el grupo de conjuntos.'
            ], 400);
        }
    }

    public function showDeleted()
    {
        $gruposEliminados = ProductoGrupoConjuntos::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('productos.grupos_conjuntos.deleted', compact('gruposEliminados'));
    }

    public function restore(string $id)
    {
        try {
            $grupo = ProductoGrupoConjuntos::withTrashed()->findOrFail($id);
            $grupo->restore();

            return response()->json([
                'success' => true,
                'message' => 'Grupo de conjuntos restaurado con exito.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar producto_grupo_conjuntos', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el grupo de conjuntos.'
            ], 400);
        }
    }
}
