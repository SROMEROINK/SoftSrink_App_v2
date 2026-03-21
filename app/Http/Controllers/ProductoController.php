<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\ProductoCategoria;
use App\Models\ProductoGrupoConjuntos;
use App\Models\ProductoGrupoSubcategoria;
use App\Models\ProductoSubcategoria;
use App\Models\ProductoTipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProductoController extends Controller
{
    use CheckForChanges;

    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only('index');
        $this->middleware('permission:ver produccion')->only('show');
        $this->middleware('permission:ver produccion')->only('showDeleted');
        $this->middleware('permission:editar produccion')->only(['create', 'store']);
        $this->middleware('permission:editar produccion')->only(['edit', 'update']);
        $this->middleware('permission:editar produccion')->only(['destroy', 'restore']);
    }

    public function getFamilias()
    {
        $familias = ProductoCategoria::select('Nombre_Categoria')
            ->whereNotNull('Nombre_Categoria')
            ->distinct()
            ->orderBy('Nombre_Categoria')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $familias,
        ]);
    }

    public function getCategorias()
    {
        $categorias = ProductoCategoria::select('Id_Categoria as id', 'Nombre_Categoria as nombre')
            ->whereNotNull('Nombre_Categoria')
            ->orderBy('Nombre_Categoria')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categorias,
        ]);
    }

    public function getSubcategorias(Request $request)
    {
        if (!$request->filled('categoria')) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no seleccionada.',
            ]);
        }

        $subcategorias = ProductoSubcategoria::where('Id_Categoria', $request->categoria)
            ->select('Id_SubCategoria as id', 'Nombre_SubCategoria as nombre')
            ->orderBy('Nombre_SubCategoria')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategorias,
        ]);
    }

    public function getCodigosProducto(Request $request)
    {
        $query = Producto::query();

        if ($request->filled('categoria')) {
            $query->where('Id_Prod_Categoria', $request->categoria);
        }

        if ($request->filled('subcategoria')) {
            $query->where('Id_Prod_SubCategoria', $request->subcategoria);
        }

        $productos = $query->select('Id_Producto as id', 'Prod_Codigo as codigo')
            ->orderBy('Prod_Codigo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productos,
        ]);
    }

    public function getDescripcionProducto($id)
    {
        try {
            $producto = Producto::findOrFail($id);

            return response()->json([
                'success' => true,
                'descripcion' => $producto->Prod_Descripcion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Producto no encontrado.',
            ]);
        }
    }

    public function getSubcategoriasPorFamilia(Request $request)
    {
        if (!$request->filled('familia_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Familia no seleccionada.',
            ]);
        }

        $subcategorias = ProductoSubcategoria::where('Id_Categoria', $request->familia_id)
            ->select('Id_SubCategoria as id', 'Nombre_SubCategoria as nombre')
            ->orderBy('Nombre_SubCategoria')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategorias,
        ]);
    }

    public function getGruposPorSubcategoria(Request $request)
    {
        if (!$request->filled('subcategoria_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategoría no seleccionada.',
            ]);
        }

        $grupos = ProductoGrupoSubcategoria::where('Id_SubCategoria', $request->subcategoria_id)
            ->select('Id_GrupoSubCategoria as id', 'Nombre_GrupoSubCategoria as nombre')
            ->orderBy('Nombre_GrupoSubCategoria')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $grupos,
        ]);
    }

    public function getTipos()
    {
        $tipos = ProductoTipo::select('Nombre_Tipo')
            ->whereNotNull('Nombre_Tipo')
            ->distinct()
            ->orderBy('Nombre_Tipo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tipos,
        ]);
    }

    public function getClientes()
    {
        $clientes = Cliente::select('Cli_Id as id', 'Cli_Nombre')
            ->whereNotNull('Cli_Nombre')
            ->orderBy('Cli_Nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $clientes,
        ]);
    }

    public function getMaterialesMP()
    {
        $materiales = Producto::select('Prod_Material_MP')
            ->whereNotNull('Prod_Material_MP')
            ->where('Prod_Material_MP', '<>', '')
            ->distinct()
            ->orderBy('Prod_Material_MP')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $materiales,
        ]);
    }

    public function getUniqueFilters()
    {
        try {
            $familias = ProductoCategoria::select('Nombre_Categoria')
                ->whereNotNull('Nombre_Categoria')
                ->where('Nombre_Categoria', '<>', '')
                ->distinct()
                ->orderBy('Nombre_Categoria')
                ->get();

            $tipos = ProductoTipo::select('Nombre_Tipo')
                ->whereNotNull('Nombre_Tipo')
                ->distinct()
                ->orderBy('Nombre_Tipo')
                ->get();

            $subfamilias = ProductoSubcategoria::select('Nombre_SubCategoria')
                ->whereNotNull('Nombre_SubCategoria')
                ->where('Nombre_SubCategoria', '<>', '')
                ->distinct()
                ->orderBy('Nombre_SubCategoria')
                ->get();

            $gruposSubcategoria = ProductoGrupoSubcategoria::select('Nombre_GrupoSubCategoria')
                ->whereNotNull('Nombre_GrupoSubCategoria')
                ->where('Nombre_GrupoSubCategoria', '<>', '')
                ->distinct()
                ->orderBy('Nombre_GrupoSubCategoria')
                ->get();

            $materialesMP = Producto::select('Prod_Material_MP')
                ->whereNotNull('Prod_Material_MP')
                ->where('Prod_Material_MP', '<>', '')
                ->distinct()
                ->orderBy('Prod_Material_MP')
                ->get();

            $diametrosMP = Producto::select('Prod_Diametro_de_MP')
                ->whereNotNull('Prod_Diametro_de_MP')
                ->where('Prod_Diametro_de_MP', '<>', '')
                ->distinct()
                ->orderBy('Prod_Diametro_de_MP')
                ->get();

            $codigosMP = Producto::select('Prod_Codigo_MP')
                ->whereNotNull('Prod_Codigo_MP')
                ->where('Prod_Codigo_MP', '<>', '')
                ->distinct()
                ->orderBy('Prod_Codigo_MP')
                ->get();

            $clientes = Cliente::select('Cli_Nombre')
                ->whereNotNull('Cli_Nombre')
                ->distinct()
                ->orderBy('Cli_Nombre')
                ->get();

            return response()->json([
                'success' => true,
                'familias' => $familias,
                'tipos' => $tipos,
                'subfamilias' => $subfamilias,
                'gruposSubcategoria' => $gruposSubcategoria,
                'materialesMP' => $materialesMP,
                'diametrosMP' => $diametrosMP,
                'codigosMP' => $codigosMP,
                'clientes' => $clientes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching unique filters: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching filters',
            ], 500);
        }
    }

    public function getDependentFilters(Request $request)
    {
        try {
            $material = strtolower(trim((string) $request->input('material_mp', '')));
            $diametro = strtolower(trim((string) $request->input('diametro_mp', '')));

            $subcategorias = ProductoSubcategoria::query()
                ->when($request->filled('categoria'), function ($query) use ($request) {
                    $query->whereHas('categoria', function ($subQuery) use ($request) {
                        $subQuery->whereRaw('LOWER(TRIM(Nombre_Categoria)) = ?', [strtolower(trim($request->categoria))]);
                    });
                })
                ->select('Nombre_SubCategoria')
                ->whereNotNull('Nombre_SubCategoria')
                ->where('Nombre_SubCategoria', '<>', '')
                ->distinct()
                ->orderBy('Nombre_SubCategoria')
                ->get();

            $grupos = Producto::query()
                ->leftJoin('producto_grupo_subcategoria as pgs', 'productos.Id_Prod_GrupoSubcategoria', '=', 'pgs.Id_GrupoSubCategoria')
                ->leftJoin('producto_subcategoria as ps', 'productos.Id_Prod_SubCategoria', '=', 'ps.Id_SubCategoria')
                ->leftJoin('producto_categoria as pc', 'productos.Id_Prod_Categoria', '=', 'pc.Id_Categoria')
                ->when($request->filled('categoria'), function ($query) use ($request) {
                    $query->whereRaw('LOWER(TRIM(pc.Nombre_Categoria)) = ?', [strtolower(trim($request->categoria))]);
                })
                ->when($request->filled('subcategoria'), function ($query) use ($request) {
                    $query->whereRaw('LOWER(TRIM(ps.Nombre_SubCategoria)) = ?', [strtolower(trim($request->subcategoria))]);
                })
                ->select('pgs.Nombre_GrupoSubCategoria')
                ->whereNotNull('pgs.Nombre_GrupoSubCategoria')
                ->where('pgs.Nombre_GrupoSubCategoria', '<>', '')
                ->distinct()
                ->orderBy('pgs.Nombre_GrupoSubCategoria')
                ->get();

            $diametros = Producto::query()
                ->when($material !== '', function ($query) use ($material) {
                    $query->whereRaw('LOWER(TRIM(Prod_Material_MP)) = ?', [$material]);
                })
                ->select('Prod_Diametro_de_MP')
                ->whereNotNull('Prod_Diametro_de_MP')
                ->where('Prod_Diametro_de_MP', '<>', '')
                ->distinct()
                ->orderBy('Prod_Diametro_de_MP')
                ->get();

            $codigosMp = Producto::query()
                ->when($material !== '', function ($query) use ($material) {
                    $query->whereRaw('LOWER(TRIM(Prod_Material_MP)) = ?', [$material]);
                })
                ->when($diametro !== '', function ($query) use ($diametro) {
                    $query->whereRaw('LOWER(TRIM(Prod_Diametro_de_MP)) = ?', [$diametro]);
                })
                ->select('Prod_Codigo_MP')
                ->whereNotNull('Prod_Codigo_MP')
                ->where('Prod_Codigo_MP', '<>', '')
                ->distinct()
                ->orderBy('Prod_Codigo_MP')
                ->get();

            return response()->json([
                'success' => true,
                'subcategorias' => $subcategorias,
                'gruposSubcategoria' => $grupos,
                'diametrosMP' => $diametros,
                'codigosMP' => $codigosMp,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching dependent filters: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching dependent filters',
            ], 500);
        }
    }

    public function getFormDependencies(Request $request)
    {
        try {
            $tipoId = $request->input('tipo_id');
            $categoriaId = $request->input('categoria_id');
            $subcategoriaId = $request->input('subcategoria_id');
            $grupoSubcategoriaId = $request->input('grupo_subcategoria_id');

            $baseQuery = Producto::query()
                ->when($tipoId, fn ($query) => $query->where('Id_Prod_Tipo', $tipoId))
                ->when($categoriaId, fn ($query) => $query->where('Id_Prod_Categoria', $categoriaId))
                ->when($subcategoriaId, fn ($query) => $query->where('Id_Prod_SubCategoria', $subcategoriaId))
                ->when($grupoSubcategoriaId, fn ($query) => $query->where('Id_Prod_GrupoSubcategoria', $grupoSubcategoriaId));

            $categorias = (clone $baseQuery)
                ->join('producto_categoria as pc', 'productos.Id_Prod_Categoria', '=', 'pc.Id_Categoria')
                ->select('pc.Id_Categoria', 'pc.Nombre_Categoria')
                ->distinct()
                ->orderBy('pc.Nombre_Categoria')
                ->get();

            $subcategorias = (clone $baseQuery)
                ->join('producto_subcategoria as ps', 'productos.Id_Prod_SubCategoria', '=', 'ps.Id_SubCategoria')
                ->select('ps.Id_SubCategoria', 'ps.Nombre_SubCategoria')
                ->distinct()
                ->orderBy('ps.Nombre_SubCategoria')
                ->get();

            $gruposSubcategoria = (clone $baseQuery)
                ->join('producto_grupo_subcategoria as pgs', 'productos.Id_Prod_GrupoSubcategoria', '=', 'pgs.Id_GrupoSubCategoria')
                ->select('pgs.Id_GrupoSubCategoria', 'pgs.Nombre_GrupoSubCategoria')
                ->distinct()
                ->orderBy('pgs.Nombre_GrupoSubCategoria')
                ->get();

            $gruposConjuntos = (clone $baseQuery)
                ->join('producto_grupo_conjuntos as pgc', 'productos.Id_Prod_GrupoConjuntos', '=', 'pgc.Id_GrupoConjuntos')
                ->select('pgc.Id_GrupoConjuntos', 'pgc.Nombre_GrupoConjuntos')
                ->distinct()
                ->orderBy('pgc.Nombre_GrupoConjuntos')
                ->get();

            return response()->json([
                'success' => true,
                'categorias' => $categorias,
                'subcategorias' => $subcategorias,
                'gruposSubcategoria' => $gruposSubcategoria,
                'gruposConjuntos' => $gruposConjuntos,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching form dependencies: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching form dependencies',
            ], 500);
        }
    }

    public function resumen()
    {
        return response()->json([
            'total' => Producto::withTrashed()->count(),
            'activos' => Producto::where('reg_Status', 1)->count(),
            'eliminados' => Producto::onlyTrashed()->count(),
        ]);
    }

    public function getData(Request $request)
    {
        try {
            $query = Producto::with([
                'productoTipo',
                'categoria',
                'subCategoria',
                'grupoSubCategoria',
                'grupoConjuntos',
                'cliente',
            ]);

            if ($request->filled('filtro_codigo')) {
                $query->where('Prod_Codigo', 'like', '%' . $request->filtro_codigo . '%');
            }

            if ($request->filled('filtro_descripcion')) {
                $query->where('Prod_Descripcion', 'like', '%' . $request->filtro_descripcion . '%');
            }

            if ($request->filled('filtro_tipo')) {
                $query->whereHas('productoTipo', function ($subQuery) use ($request) {
                    $subQuery->whereRaw('LOWER(Nombre_Tipo) LIKE ?', ['%' . strtolower($request->filtro_tipo) . '%']);
                });
            }

            if ($request->filled('filtro_familia')) {
                $query->whereHas('categoria', function ($subQuery) use ($request) {
                    $subQuery->whereRaw('LOWER(TRIM(Nombre_Categoria)) = ?', [strtolower(trim($request->filtro_familia))]);
                });
            }

            if ($request->filled('filtro_sub_familia')) {
                $query->whereHas('subCategoria', function ($subQuery) use ($request) {
                    $subQuery->whereRaw('LOWER(TRIM(Nombre_SubCategoria)) = ?', [strtolower(trim($request->filtro_sub_familia))]);
                });
            }

            if ($request->filled('filtro_grupo_sub_categoria')) {
                $query->whereHas('grupoSubCategoria', function ($subQuery) use ($request) {
                    $subQuery->whereRaw('LOWER(TRIM(Nombre_GrupoSubCategoria)) = ?', [strtolower(trim($request->filtro_grupo_sub_categoria))]);
                });
            }

            if ($request->filled('filtro_codigo_conjunto')) {
                $query->whereHas('grupoConjuntos', function ($subQuery) use ($request) {
                    $subQuery->whereRaw('LOWER(Nombre_GrupoConjuntos) LIKE ?', ['%' . strtolower($request->filtro_codigo_conjunto) . '%']);
                });
            }

            if ($request->filled('filtro_cliente')) {
                $query->whereHas('cliente', function ($subQuery) use ($request) {
                    $subQuery->whereRaw('LOWER(Cli_Nombre) LIKE ?', ['%' . strtolower($request->filtro_cliente) . '%']);
                });
            }

            if ($request->filled('filtro_material_mp')) {
                $query->whereRaw('LOWER(TRIM(Prod_Material_MP)) = ?', [strtolower(trim($request->filtro_material_mp))]);
            }

            if ($request->filled('filtro_diametro_mp')) {
                $query->whereRaw('LOWER(TRIM(Prod_Diametro_de_MP)) = ?', [strtolower(trim($request->filtro_diametro_mp))]);
            }

            if ($request->filled('filtro_codigo_mp')) {
                $query->whereRaw('LOWER(TRIM(Prod_Codigo_MP)) = ?', [strtolower(trim($request->filtro_codigo_mp))]);
            }

            if ($request->filled('filtro_plano')) {
                $query->where('Prod_N_Plano', 'like', '%' . $request->filtro_plano . '%');
            }

            if ($request->filled('filtro_revision_plano')) {
                $query->where('Prod_Plano_Ultima_Revision', 'like', '%' . $request->filtro_revision_plano . '%');
            }

            if ($request->filled('filtro_longitud_pieza')) {
                $query->where('Prod_Longitud_de_Pieza', 'like', '%' . $request->filtro_longitud_pieza . '%');
            }

            $productos = $query->select([
                'Id_Producto',
                'Prod_Codigo',
                'Prod_Descripcion',
                'Id_Prod_Tipo',
                'Id_Prod_Categoria',
                'Id_Prod_SubCategoria',
                'Id_Prod_GrupoSubcategoria',
                'Id_Prod_GrupoConjuntos',
                'Prod_CliId',
                'Prod_N_Plano',
                'Prod_Plano_Ultima_Revision',
                'Prod_Material_MP',
                'Prod_Diametro_de_MP',
                'Prod_Codigo_MP',
                'Prod_Longitud_de_Pieza',
                'reg_Status',
                'created_at',
                'updated_at',
            ]);

            return DataTables::eloquent($productos)
                ->addColumn('Nombre_Tipo', fn ($producto) => $producto->productoTipo->Nombre_Tipo ?? '')
                ->addColumn('Nombre_Categoria', fn ($producto) => $producto->categoria->Nombre_Categoria ?? '')
                ->addColumn('Nombre_SubCategoria', fn ($producto) => $producto->subCategoria->Nombre_SubCategoria ?? '')
                ->addColumn('Nombre_GrupoSubCategoria', fn ($producto) => $producto->grupoSubCategoria->Nombre_GrupoSubCategoria ?? '')
                ->addColumn('Nombre_GrupoConjuntos', fn ($producto) => $producto->grupoConjuntos->Nombre_GrupoConjuntos ?? '')
                ->addColumn('Cli_Nombre', fn ($producto) => $producto->cliente->Cli_Nombre ?? '')
                ->addColumn('Estado_Texto', fn ($producto) => (int) $producto->reg_Status === 1 ? 'Activo' : 'Inactivo')
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Error in getData: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error fetching data',
            ], 500);
        }
    }

    public function index()
    {
        return view('productos.index');
    }

    public function create()
    {
        $tipos = ProductoTipo::orderBy('Nombre_Tipo')->get();
        $categorias = ProductoCategoria::orderBy('Nombre_Categoria')->get();
        $subcategorias = ProductoSubcategoria::orderBy('Nombre_SubCategoria')->get();
        $gruposSubcategoria = ProductoGrupoSubcategoria::orderBy('Nombre_GrupoSubCategoria')->get();
        $gruposConjuntos = ProductoGrupoConjuntos::orderBy('Nombre_GrupoConjuntos')->get();
        $clientes = Cliente::orderBy('Cli_Nombre')->get();
        $materialesMp = Producto::query()
            ->select('Prod_Material_MP')
            ->whereNotNull('Prod_Material_MP')
            ->where('Prod_Material_MP', '<>', '')
            ->distinct()
            ->orderBy('Prod_Material_MP')
            ->get();

        return view('productos.create', compact(
            'tipos',
            'categorias',
            'subcategorias',
            'gruposSubcategoria',
            'gruposConjuntos',
            'clientes',
            'materialesMp'
        ));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $this->validateProducto($request);
            $validatedData['created_by'] = Auth::id();
            $validatedData['reg_Status'] = (int) ($validatedData['reg_Status'] ?? 1);

            DB::beginTransaction();
            Producto::create($validatedData);
            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto creado correctamente.',
                    'redirect' => route('productos.index'),
                ]);
            }

            return redirect()->route('productos.index')->with('success', 'Producto creado con éxito');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear producto', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el producto: ' . $e->getMessage(),
                ], 400);
            }

            return back()->withInput()->with('error', 'Error al crear el producto.');
        }
    }

    public function show(Producto $producto)
    {
        $producto->load([
            'productoTipo',
            'categoria',
            'subCategoria',
            'grupoSubCategoria',
            'grupoConjuntos',
            'cliente',
        ]);

        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $tipos = ProductoTipo::orderBy('Nombre_Tipo')->get();
        $categorias = ProductoCategoria::orderBy('Nombre_Categoria')->get();
        $subcategorias = ProductoSubcategoria::orderBy('Nombre_SubCategoria')->get();
        $gruposSubcategoria = ProductoGrupoSubcategoria::orderBy('Nombre_GrupoSubCategoria')->get();
        $gruposConjuntos = ProductoGrupoConjuntos::orderBy('Nombre_GrupoConjuntos')->get();
        $clientes = Cliente::orderBy('Cli_Nombre')->get();
        $materialesMp = Producto::query()
            ->select('Prod_Material_MP')
            ->whereNotNull('Prod_Material_MP')
            ->where('Prod_Material_MP', '<>', '')
            ->distinct()
            ->orderBy('Prod_Material_MP')
            ->get();
        $diametrosMp = Producto::query()
            ->select('Prod_Diametro_de_MP')
            ->whereNotNull('Prod_Diametro_de_MP')
            ->where('Prod_Diametro_de_MP', '<>', '')
            ->where('Prod_Material_MP', $producto->Prod_Material_MP)
            ->distinct()
            ->orderBy('Prod_Diametro_de_MP')
            ->get();

        return view('productos.edit', compact(
            'producto',
            'tipos',
            'categorias',
            'subcategorias',
            'gruposSubcategoria',
            'gruposConjuntos',
            'clientes',
            'materialesMp',
            'diametrosMp'
        ));
    }

    public function update(Request $request, Producto $producto)
    {
        $validatedData = $this->validateProducto($request, $producto->Id_Producto);

        return $this->updateIfChanged($producto, $validatedData, [
            'success_redirect' => route('productos.index'),
            'success_message' => 'Producto actualizado correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by' => true,
            'use_transaction' => true,
            'normalize_data' => true,
        ]);
    }

    public function destroy(Producto $producto)
    {
        try {
            $producto->deleted_by = Auth::id();
            $producto->save();
            $producto->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto eliminado correctamente.',
                ]);
            }

            return redirect()->route('productos.index')->with('success', 'Producto eliminado con éxito');
        } catch (\Exception $e) {
            Log::error('Error al eliminar producto', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo eliminar el producto.',
                ], 400);
            }

            return back()->with('error', 'No se pudo eliminar el producto.');
        }
    }

    public function showDeleted()
    {
        $productosEliminados = Producto::with([
            'categoria',
            'subCategoria',
            'productoTipo',
        ])
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('productos.deleted', compact('productosEliminados'));
    }

    public function restore(string $id)
    {
        try {
            $producto = Producto::withTrashed()->findOrFail($id);
            $producto->deleted_by = null;
            $producto->save();
            $producto->restore();

            return response()->json([
                'success' => true,
                'message' => 'Producto restaurado con exito.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar producto', [
                'error' => $e->getMessage(),
                'producto_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el producto.',
            ], 400);
        }
    }

    protected function validateProducto(Request $request, ?int $productoId = null): array
    {
        $validated = $request->validate([
            'Prod_Codigo' => [
                'required',
                'string',
                'max:255',
                Rule::unique('productos', 'Prod_Codigo')
                    ->ignore($productoId, 'Id_Producto')
                    ->whereNull('deleted_at'),
            ],
            'Prod_Descripcion' => ['required', 'string', 'max:255'],
            'Id_Prod_Tipo' => ['required', 'integer', 'exists:producto_tipo,Id_Tipo'],
            'Id_Prod_Categoria' => ['required', 'integer', 'exists:producto_categoria,Id_Categoria'],
            'Id_Prod_SubCategoria' => ['required', 'integer', 'exists:producto_subcategoria,Id_SubCategoria'],
            'Id_Prod_GrupoSubcategoria' => ['nullable', 'integer', 'exists:producto_grupo_subcategoria,Id_GrupoSubCategoria'],
            'Id_Prod_GrupoConjuntos' => ['nullable', 'integer', 'exists:producto_grupo_conjuntos,Id_GrupoConjuntos'],
            'Prod_CliId' => ['required', 'integer', 'exists:clientes,Cli_Id'],
            'Prod_N_Plano' => ['nullable', 'integer'],
            'Prod_Plano_Ultima_Revision' => ['required', 'string', 'max:50'],
            'Prod_Material_MP' => ['nullable', 'string', 'max:255'],
            'Prod_Diametro_de_MP' => ['nullable', 'string', 'max:255'],
            'Prod_Codigo_MP' => ['nullable', 'string', 'max:50'],
            'Prod_Longitud_de_Pieza' => ['required', 'numeric'],
            'reg_Status' => ['required', 'in:0,1'],
        ]);

        return $this->normalizeProductoData($validated);
    }

    protected function normalizeProductoData(array $validated): array
    {
        $validated['Prod_Material_MP'] = $this->normalizeNullableText($validated['Prod_Material_MP'] ?? null);
        $validated['Prod_Diametro_de_MP'] = $this->normalizeNullableText($validated['Prod_Diametro_de_MP'] ?? null);

        if (($validated['Prod_Material_MP'] && !$validated['Prod_Diametro_de_MP'])
            || (!$validated['Prod_Material_MP'] && $validated['Prod_Diametro_de_MP'])) {
            throw ValidationException::withMessages([
                'Prod_Material_MP' => 'Material MP y Diametro MP deben cargarse juntos o quedar ambos vacios.',
                'Prod_Diametro_de_MP' => 'Material MP y Diametro MP deben cargarse juntos o quedar ambos vacios.',
            ]);
        }

        $validated['Prod_Codigo_MP'] = $validated['Prod_Material_MP'] && $validated['Prod_Diametro_de_MP']
            ? $validated['Prod_Material_MP'] . '_' . $validated['Prod_Diametro_de_MP']
            : null;

        return $validated;
    }

    protected function normalizeNullableText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
