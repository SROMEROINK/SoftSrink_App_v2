<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Producto;
use App\Models\ProductoCategoria;
use App\Models\ProductoSubCategoria;
use App\Models\ProductoGrupoSubcategoria;
use App\Models\ProductoTipo;
use App\Models\Cliente;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only('index');
        $this->middleware('permission:ver produccion')->only('show');
        $this->middleware('permission:editar produccion')->only(['create', 'store']);
        $this->middleware('permission:editar produccion')->only(['edit', 'update']);
        $this->middleware('permission:editar produccion')->only('destroy');
    }


    public function getFamilias()
    {
        // Obtener todas las familias únicas desde la base de datos
        $familias = ProductoCategoria::select('Nombre_Categoria')->distinct()->get();
    
        return response()->json([
            'success' => true,
            'data' => $familias
        ]);
    }
    

    // Método para obtener las categorías de productos
    public function getCategorias()
    {
        $categorias = ProductoCategoria::select('Id_Categoria as id', 'Nombre_Categoria as nombre')->get();
        return response()->json(['success' => true, 'data' => $categorias]);
    }

    public function getSubcategorias(Request $request)
    {
        $categoriaId = $request->categoria;
    
        if ($categoriaId) {
            // Verificar el valor de $categoriaId
            // dd($categoriaId);
    
            $subcategorias = ProductoSubCategoria::where('Id_Categoria', $categoriaId)
                ->select('Id_SubCategoria as id', 'Nombre_SubCategoria as nombre')
                ->get();
    
            return response()->json(['success' => true, 'data' => $subcategorias]);
        }
    
        return response()->json(['success' => false, 'message' => 'Categoría no seleccionada.']);
    }
    

// Método para obtener los códigos de productos basado en los filtros de categoría y subfamilia
public function getCodigosProducto(Request $request)
{
    $query = Producto::query();

    if ($request->has('categoria') && !empty($request->categoria)) {
        $query->where('Id_Prod_Clase_Familia', $request->categoria);
    }

    if ($request->has('subcategoria') && !empty($request->subcategoria)) {
        $query->where('Id_Prod_Sub_Familia', $request->subcategoria);
    }

    $productos = $query->select('Id_Producto as id', 'Prod_Codigo as codigo')->get();

    return response()->json([
        'success' => true,
        'data' => $productos
    ]);
}

public function getDescripcionProducto($id)
{
    try {
        // Obtener la descripción del producto basado en su ID
        $producto = Producto::findOrFail($id);
        return response()->json(['success' => true, 'descripcion' => $producto->Prod_Descripcion]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => 'Producto no encontrado.']);
    }
}



// Método para obtener subcategorías basadas en la familia seleccionada
public function getSubcategoriasPorFamilia(Request $request)
{
    $familiaId = $request->familia_id;

    if ($familiaId) {
        $subcategorias = ProductoSubCategoria::where('Id_Categoria', $familiaId)
            ->select('Id_SubCategoria as id', 'Nombre_SubCategoria as nombre')
            ->get();

        return response()->json(['success' => true, 'data' => $subcategorias]);
    }

    return response()->json(['success' => false, 'message' => 'Familia no seleccionada.']);
}

// Método para obtener grupos de subcategoría basados en la subcategoría seleccionada
public function getGruposPorSubcategoria(Request $request)
{
    $subcategoriaId = $request->subcategoria_id;

    if ($subcategoriaId) {
        $grupos = ProductoGrupoSubcategoria::where('Id_SubCategoria', $subcategoriaId)
            ->select('Id_GrupoSubCategoria as id', 'Nombre_GrupoSubCategoria as nombre')
            ->get();

        return response()->json(['success' => true, 'data' => $grupos]);
    }

    return response()->json(['success' => false, 'message' => 'Subcategoría no seleccionada.']);
}


public function getTipos()
{
    $tipos = ProductoTipo::select('Nombre_Tipo')->distinct()->get();
    return response()->json([
        'success' => true,
        'data' => $tipos
    ]);
}

public function getUniqueFilters()
{
    try {
        // Obtener los valores únicos desde la base de datos para cada filtro
        $familias = ProductoCategoria::select('Nombre_Categoria')->distinct()->get();
        $tipos = ProductoTipo::select('Nombre_Tipo')->distinct()->get();
        $subfamilias = ProductoSubCategoria::select('Nombre_SubCategoria')->distinct()->get();
        $materialesMP = Producto::select('Prod_Material_MP')->distinct()->get();
        $clientes = Cliente::select('Cli_Nombre')->distinct()->get();

        // Devolver los datos en formato JSON
        return response()->json([
            'success' => true,
            'familias' => $familias,
            'tipos' => $tipos,
            'subfamilias' => $subfamilias,
            'materialesMP' => $materialesMP,
            'clientes' => $clientes,
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching unique filters: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Error fetching filters'], 500);
    }
}




public function getData(Request $request)
{
    try {
        if ($request->ajax()) {
            $query = Producto::with([
                'productoTipo',
                'categoria',
                'subCategoria',
                'grupoSubCategoria',
                'grupoConjuntos',
                'cliente'
            ]);

            // Aplicar filtros a las columnas
            if ($request->filtro_tipo) {
                $query->whereHas('productoTipo', function ($q) use ($request) {
                    $q->where('Nombre_Tipo', $request->filtro_tipo);
                });
            }

            if ($request->filtro_familia) {
                $query->whereHas('categoria', function ($q) use ($request) {
                    $q->where('Nombre_Categoria', $request->filtro_familia);
                });
            }

            if ($request->filtro_sub_familia) {
                $query->whereHas('subCategoria', function ($q) use ($request) {
                    $q->where('Nombre_SubCategoria', $request->filtro_sub_familia);
                });
            }

            if ($request->filtro_grupo_sub_categoria) {
                $query->whereHas('grupoSubCategoria', function ($q) use ($request) {
                    $q->where('Nombre_GrupoSubCategoria', $request->filtro_grupo_sub_categoria);
                });
            }

            if ($request->filtro_codigo_conjunto) {
                $query->whereHas('grupoConjuntos', function ($q) use ($request) {
                    $q->where('Nombre_GrupoConjuntos', $request->filtro_codigo_conjunto);
                });
            }

            // Filtros adicionales para otras columnas
            if ($request->filtro_material_mp) {
                $query->where('Prod_Material_MP', $request->filtro_material_mp);
            }

            if ($request->filtro_diametro_mp) {
                $query->where('Prod_Diametro_de_MP', $request->filtro_diametro_mp);
            }

            if ($request->filtro_codigo_mp) {
                $query->where('Prod_Codigo_MP', $request->filtro_codigo_mp);
            }

            if ($request->filtro_descripcion) {
                $query->where('Prod_Descripcion', 'like', '%' . $request->filtro_descripcion . '%');
            }

            // Realizar la consulta y devolver los datos
            $productos = $query->select(
                'Id_Producto', 'Prod_Codigo', 'Prod_Descripcion', 
                'Id_Prod_Tipo', 'Id_Prod_Clase_Familia', 
                'Id_Prod_Sub_Familia', 'Id_Prod_Grupos_de_Sub_Familia', 
                'Id_Prod_Codigo_Conjuntos', 'Prod_CliId', 'Prod_N_Plano', 
                'Prod_Plano_Ultima_Revisión', 'Prod_Material_MP', 'Prod_Diametro_de_MP', 
                'Prod_Codigo_MP', 'Prod_Longitud_de_Pieza', 'Prod_Longitug_Total'
            );

            return DataTables::eloquent($productos)
                ->addColumn('Nombre_Tipo', function ($producto) {
                    return $producto->productoTipo->Nombre_Tipo ?? '';
                })
                ->addColumn('Nombre_Categoria', function ($producto) {
                    return $producto->categoria->Nombre_Categoria ?? '';
                })
                ->addColumn('Nombre_SubCategoria', function ($producto) {
                    return $producto->subCategoria->Nombre_SubCategoria ?? '';
                })
                ->addColumn('Nombre_GrupoSubCategoria', function ($producto) {
                    return $producto->grupoSubCategoria->Nombre_GrupoSubCategoria ?? '';
                })
                ->addColumn('Nombre_GrupoConjuntos', function ($producto) {
                    return $producto->grupoConjuntos->Nombre_GrupoConjuntos ?? '';
                })
                ->addColumn('Cli_Nombre', function ($producto) {
                    return $producto->cliente->Cli_Nombre ?? '';
                })
                ->make(true);
        }
    } catch (\Exception $e) {
        Log::error('Error in getData: ' . $e->getMessage());
        return response()->json(['error' => 'Error fetching data'], 500);
    }
}

    public function index()
    {
        return view('Productos.index');
    }

    public function create()
    {
        // Devolver la vista de creación
        return view('Productos.create');
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            // Añade más validaciones según tus campos
        ]);

        // Crear un nuevo producto
        Producto::create($request->all());

        // Redirigir a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto creado con éxito');
    }

    public function show(Producto $producto)
    {
        // Mostrar la vista de detalles del producto
        return view('Productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        // Devolver la vista de edición y pasar el producto a esa vista
        return view('Productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            // Añade más validaciones según tus campos
        ]);

        // Actualizar el producto
        $producto->update($request->all());

        // Redirigir a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto actualizado con éxito');
    }

    public function destroy(Producto $producto)
    {
        // Eliminar el producto
        $producto->delete();

        // Redirigir a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto eliminado con éxito');
    }
}
