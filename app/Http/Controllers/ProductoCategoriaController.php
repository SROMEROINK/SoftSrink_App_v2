<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\ProductoCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductoCategoriaController extends Controller
{
    use CheckForChanges;

    public function index()
    {
        $totalCategorias = ProductoCategoria::count();

        return view('productos.categorias.index', compact('totalCategorias'));
    }

    public function resumen()
    {
        return response()->json([
            'total' => ProductoCategoria::withTrashed()->count(),
            'activos' => ProductoCategoria::where('reg_Status', 1)->count(),
            'eliminados' => ProductoCategoria::onlyTrashed()->count(),
        ]);
    }

    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = ProductoCategoria::query();

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $baseQuery->where('reg_Status', $status);
            }

            $nombres = $baseQuery->select('Nombre_Categoria')
                ->distinct()
                ->orderBy('Nombre_Categoria')
                ->pluck('Nombre_Categoria')
                ->values();

            return response()->json([
                'nombres' => $nombres,
                'status' => collect(['Activo', 'Inactivo']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getUniqueFilters ProductoCategoria: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar filtros únicos.'
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        try {
            $query = ProductoCategoria::select([
                'Id_Categoria',
                'Nombre_Categoria',
                'reg_Status',
                'created_at',
                'updated_at',
            ])->orderBy('Id_Categoria', 'asc');

            if ($request->filled('filtro_id')) {
                $query->where('Id_Categoria', $request->filtro_id);
            }

            if ($request->filled('filtro_nombre')) {
                $query->whereRaw('LOWER(Nombre_Categoria) LIKE ?', ['%' . strtolower($request->filtro_nombre) . '%']);
            }

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $query->where('reg_Status', $status);
            }

            return datatables()->of($query)
                ->addColumn('Status_Texto', function ($categoria) {
                    return (int) $categoria->reg_Status === 1 ? 'Activo' : 'Inactivo';
                })
                ->addColumn('acciones', function () {
                    return '';
                })
                ->rawColumns(['acciones'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error en getData ProductoCategoria: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los datos.'
            ], 500);
        }
    }

    public function create()
    {
        $ultimaCategoria = ProductoCategoria::orderBy('Id_Categoria', 'desc')->first();

        return view('productos.categorias.create', compact('ultimaCategoria'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Nombre_Categoria' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('producto_categoria', 'Nombre_Categoria')->whereNull('deleted_at'),
                ],
                'reg_Status' => 'required|in:0,1',
            ]);

            DB::beginTransaction();

            $validatedData['Nombre_Categoria'] = trim($validatedData['Nombre_Categoria']);
            $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];
            $validatedData['created_by'] = Auth::id();

            ProductoCategoria::create($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Categoria de producto creada correctamente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear producto_categoria', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la categoria de producto: ' . $e->getMessage()
            ], 400);
        }
    }

    public function show(string $id)
    {
        $producto_categoria = ProductoCategoria::findOrFail($id);

        return view('productos.categorias.show', compact('producto_categoria'));
    }

    public function edit(string $id)
    {
        $categoria = ProductoCategoria::findOrFail($id);

        return view('productos.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, string $id)
    {
        $categoria = ProductoCategoria::findOrFail($id);

        $validatedData = $request->validate([
            'Nombre_Categoria' => [
                'required',
                'string',
                'max:100',
                Rule::unique('producto_categoria', 'Nombre_Categoria')
                    ->ignore($id, 'Id_Categoria')
                    ->whereNull('deleted_at'),
            ],
            'reg_Status' => 'required|in:0,1',
        ]);

        $validatedData['Nombre_Categoria'] = trim($validatedData['Nombre_Categoria']);
        $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];

        return $this->updateIfChanged($categoria, $validatedData, [
            'success_redirect' => route('producto_categoria.index'),
            'success_message' => 'Categoria de producto actualizada correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by' => true,
            'use_transaction' => true,
            'normalize_data' => false,
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $categoria = ProductoCategoria::findOrFail($id);

            $categoria->deleted_by = Auth::id();
            $categoria->save();
            $categoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoria de producto eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar producto_categoria', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la categoria de producto.'
            ], 400);
        }
    }

    public function showDeleted()
    {
        $categoriasEliminadas = ProductoCategoria::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('productos.categorias.deleted', compact('categoriasEliminadas'));
    }

    public function restore(string $id)
    {
        try {
            $categoria = ProductoCategoria::withTrashed()->findOrFail($id);
            $categoria->restore();

            return response()->json([
                'success' => true,
                'message' => 'Categoria de producto restaurada con éxito.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar producto_categoria', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar la categoria de producto.'
            ], 400);
        }
    }
}
