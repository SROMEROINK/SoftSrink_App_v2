<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\ProductoCategoria;
use App\Models\ProductoSubcategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductoSubcategoriaController extends Controller
{
    use CheckForChanges;

    public function index()
    {
        $totalSubcategorias = ProductoSubcategoria::count();

        return view('productos.subcategorias.index', compact('totalSubcategorias'));
    }

    public function resumen()
    {
        return response()->json([
            'total' => ProductoSubcategoria::withTrashed()->count(),
            'activos' => ProductoSubcategoria::where('reg_Status', 1)->count(),
            'eliminados' => ProductoSubcategoria::onlyTrashed()->count(),
        ]);
    }

    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = ProductoSubcategoria::with('categoria');

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $baseQuery->where('reg_Status', $status);
            }

            $nombres = $baseQuery->select('Nombre_SubCategoria')
                ->distinct()
                ->orderBy('Nombre_SubCategoria')
                ->pluck('Nombre_SubCategoria')
                ->values();

            return response()->json([
                'nombres' => $nombres,
                'status' => collect(['Activo', 'Inactivo']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getUniqueFilters ProductoSubcategoria: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar filtros únicos.'
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        try {
            $query = ProductoSubcategoria::with('categoria')
                ->select([
                    'Id_SubCategoria',
                    'Id_Categoria',
                    'Nombre_SubCategoria',
                    'reg_Status',
                    'created_at',
                    'updated_at',
                ])
                ->orderBy('Id_SubCategoria', 'asc');

            if ($request->filled('filtro_nombre')) {
                $query->whereRaw('LOWER(Nombre_SubCategoria) LIKE ?', ['%' . strtolower($request->filtro_nombre) . '%']);
            }

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $query->where('reg_Status', $status);
            }

            return datatables()->of($query)
                ->addColumn('Nombre_Categoria', function ($subcategoria) {
                    return $subcategoria->categoria->Nombre_Categoria ?? '';
                })
                ->addColumn('Status_Texto', function ($subcategoria) {
                    return (int) $subcategoria->reg_Status === 1 ? 'Activo' : 'Inactivo';
                })
                ->addColumn('acciones', function () {
                    return '';
                })
                ->rawColumns(['acciones'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error en getData ProductoSubcategoria: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los datos.'
            ], 500);
        }
    }

    public function create()
    {
        $ultimaSubcategoria = ProductoSubcategoria::orderBy('Id_SubCategoria', 'desc')->first();
        $categorias = ProductoCategoria::orderBy('Nombre_Categoria')->get();

        return view('productos.subcategorias.create', compact('ultimaSubcategoria', 'categorias'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Id_Categoria' => ['required', 'integer', 'exists:producto_categoria,Id_Categoria'],
                'Nombre_SubCategoria' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('producto_subcategoria', 'Nombre_SubCategoria')->whereNull('deleted_at'),
                ],
                'reg_Status' => 'required|in:0,1',
            ]);

            DB::beginTransaction();

            $validatedData['Nombre_SubCategoria'] = trim($validatedData['Nombre_SubCategoria']);
            $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];
            $validatedData['created_by'] = Auth::id();

            ProductoSubcategoria::create($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subcategoria de producto creada correctamente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear producto_subcategoria', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la subcategoria de producto: ' . $e->getMessage()
            ], 400);
        }
    }

    public function show(string $id)
    {
        $producto_subcategoria = ProductoSubcategoria::with('categoria')->findOrFail($id);

        return view('productos.subcategorias.show', compact('producto_subcategoria'));
    }

    public function edit(string $id)
    {
        $subcategoria = ProductoSubcategoria::findOrFail($id);
        $categorias = ProductoCategoria::orderBy('Nombre_Categoria')->get();

        return view('productos.subcategorias.edit', compact('subcategoria', 'categorias'));
    }

    public function update(Request $request, string $id)
    {
        $subcategoria = ProductoSubcategoria::findOrFail($id);

        $validatedData = $request->validate([
            'Id_Categoria' => ['required', 'integer', 'exists:producto_categoria,Id_Categoria'],
            'Nombre_SubCategoria' => [
                'required',
                'string',
                'max:100',
                Rule::unique('producto_subcategoria', 'Nombre_SubCategoria')
                    ->ignore($id, 'Id_SubCategoria')
                    ->whereNull('deleted_at'),
            ],
            'reg_Status' => 'required|in:0,1',
        ]);

        $validatedData['Nombre_SubCategoria'] = trim($validatedData['Nombre_SubCategoria']);
        $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];

        return $this->updateIfChanged($subcategoria, $validatedData, [
            'success_redirect' => route('producto_subcategoria.index'),
            'success_message' => 'Subcategoria de producto actualizada correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by' => true,
            'use_transaction' => true,
            'normalize_data' => false,
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $subcategoria = ProductoSubcategoria::findOrFail($id);

            $subcategoria->deleted_by = Auth::id();
            $subcategoria->save();
            $subcategoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subcategoria de producto eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar producto_subcategoria', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la subcategoria de producto.'
            ], 400);
        }
    }

    public function showDeleted()
    {
        $subcategoriasEliminadas = ProductoSubcategoria::with('categoria')
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('productos.subcategorias.deleted', compact('subcategoriasEliminadas'));
    }

    public function restore(string $id)
    {
        try {
            $subcategoria = ProductoSubcategoria::withTrashed()->findOrFail($id);
            $subcategoria->restore();

            return response()->json([
                'success' => true,
                'message' => 'Subcategoria de producto restaurada con éxito.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar producto_subcategoria', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar la subcategoria de producto.'
            ], 400);
        }
    }
}
