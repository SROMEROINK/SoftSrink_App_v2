<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\SubCategoria;

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

    // Método para obtener las categorías de productos
    public function getCategorias()
    {
        $categorias = Categoria::select('Id_Categoria as id', 'Nombre_Categoria as nombre')->get();
        return response()->json(['success' => true, 'data' => $categorias]);
    }

    public function getSubcategorias(Request $request)
    {
        $categoriaId = $request->categoria;
    
        if ($categoriaId) {
            // Verificar el valor de $categoriaId
            // dd($categoriaId);
    
            $subcategorias = SubCategoria::where('Id_Categoria', $categoriaId)
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





    public function getData(Request $request)
    {
        try {
            if ($request->ajax()) {
                $productos = Producto::with([
                    'clasificacionPiezas',
                    'categoria',
                    'subCategoria',
                    'grupoSubCategoria',
                    'grupoConjuntos',
                    'cliente'
                ])->select(
                    'Id_Producto', 'Prod_Codigo', 'Prod_Descripcion', 
                    'Id_Prod_Clasificacion_Piezas', 'Id_Prod_Clase_Familia', 
                    'Id_Prod_Sub_Familia', 'Id_Prod_Grupos_de_Sub_Familia', 
                    'Id_Prod_Codigo_Conjuntos', 'Prod_CliId', 'Prod_N_Plano', 
                    'Prod_Plano_Ultima_Revisión', 'Prod_Material_MP', 'Prod_Diametro_de_MP', 
                    'Prod_Codigo_MP', 'Prod_Longitud_de_Pieza', 'Prod_Longitug_Total'
                );
    
                return DataTables::eloquent($productos)
                    ->addColumn('Nombre_Clasificacion', function ($producto) {
                        return $producto->clasificacionPiezas->Nombre_Clasificacion ?? '';
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
                    ->filter(function ($query) use ($request) {
                        // Aquí van los filtros adicionales
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
