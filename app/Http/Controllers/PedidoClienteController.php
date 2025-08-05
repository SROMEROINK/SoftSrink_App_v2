<?php
//app\Http\Controllers\ListadoOfController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; // al inicio del controlador
use App\Models\PedidoCliente;
use App\Models\Producto;
use App\Models\ProductoCategoria;
use App\Models\ProductoSubCategoria;

class PedidoClienteController extends Controller
{

        public function __construct()
    {
        $this->middleware('permission:ver produccion')->only('index');
        $this->middleware('permission:ver produccion')->only('show');
        $this->middleware('permission:editar produccion')->only(['create', 'store']);
        $this->middleware('permission:editar produccion')->only(['edit', 'update']);
        $this->middleware('permission:editar produccion')->only('destroy');
    }

// Controlador para PedidoCliente
public function getData(Request $request)
{
    try {
        if ($request->ajax()) {
            $pedido_cliente = PedidoCliente::with(['producto.categoria', 'creator', 'updater'])
                ->select(
                    'Id_OF',
                    'Nro_OF',
                    'Producto_Id',
                    'Fecha_del_Pedido',
                    'Cant_Fabricacion',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by'
                    
                )->orderBy('Nro_OF', 'desc'); // Cambiar a descendente;

            // Filtros
            if ($request->filled('filtro_nro_of')) {
                $pedido_cliente->where('Nro_OF', $request->filtro_nro_of);
            }
            if ($request->filled('filtro_producto')) {
                $pedido_cliente->whereHas('producto', function ($q) use ($request) {
                    $q->where('Prod_Codigo', 'like', '%' . $request->filtro_producto . '%');
                });
            }
            if ($request->filled('filtro_descripción')) {
                $pedido_cliente->whereHas('producto', function ($q) use ($request) {
                    $q->where('Prod_Descripcion', 'like', '%' . $request->filtro_descripción . '%');
                });
            }
            if ($request->filled('filtro_nombre_categoria')) {
                $pedido_cliente->whereHas('producto.categoria', function ($q) use ($request) {
                    $q->where('Nombre_Categoria', $request->filtro_nombre_categoria);
                });
            }
            if ($request->filled('filtro_fecha_pedido')) {
                $pedido_cliente->where('Fecha_del_Pedido', $request->filtro_fecha_pedido);
            }
            if ($request->filled('filtro_cant_fabricacion')) {
                $pedido_cliente->where('Cant_Fabricacion', $request->filtro_cant_fabricacion);
            }

            return DataTables::eloquent($pedido_cliente)
                ->addColumn('Producto_Nombre', fn($of) => $of->producto->Prod_Codigo ?? '')
                ->addColumn('Descripción', fn($of) => $of->producto->Prod_Descripcion ?? '')
                ->addColumn('Nombre_Categoria', fn($of) => $of->producto->categoria->Nombre_Categoria ?? '')
                ->addColumn('creator', fn($of) => $of->creator->name ?? '')
                ->addColumn('updater', fn($of) => $of->updater->name ?? '')
                ->editColumn('created_at', fn($of) => $of->created_at?->format('Y-m-d H:i:s'))
                ->editColumn('updated_at', fn($of) => $of->updated_at?->format('Y-m-d H:i:s'))
                ->addColumn('Estado', function ($pedido) {
    $tieneFabricacion = DB::table('registro_de_fabricacion')
        ->where('Nro_OF', $pedido->Nro_OF)
        ->exists();

    return $tieneFabricacion
        ? '<span class="badge bg-success">Fabricado</span>'
        : '<span class="badge bg-secondary">Sin fabricación</span>';
})
->rawColumns(['Estado']) // <- MUY IMPORTANTE para que se renderice el HTML

                ->toJson();
        }

        return response()->json(['error' => 'Petición inválida'], 400);

    } catch (\Exception $e) {
        Log::error('Error en PedidoCliente getData: ' . $e->getMessage());
        return response()->json(['error' => 'Error al obtener datos'], 500);
    }
}
public function index()
{
    return view('pedido_cliente.index');
}
    
    






 //* Show the form for creating a new resource.

public function create()
{
    // Obtener las categorías de productos para cargarlas en el formulario
    $categorias = ProductoCategoria::select('Id_Categoria as id', 'Nombre_Categoria as nombre')->get();
    
    // Obtener las subcategorías si querés precargarlas (opcional)
    $subcategorias = ProductoSubCategoria::select('Id_Subcategoria as id', 'Nombre_Subcategoria as nombre')->get();
    
    return view('pedido_cliente.create', compact('categorias'));
}




/**
 * Store a newly created resource in storage.
*/
public function store(Request $request)
    {
        
        $pedido = new PedidoCliente();
        $pedido->created_by = Auth::id();
        $pedido->updated_by = Auth::id(); // <- Agregalo también acá
        
        $messages = [
            'nro_of.*.required' => 'El número de OF es obligatorio.',
    'nro_of.*.unique' => 'El número de OF ya ha sido registrado.',
    'producto_id.*.required' => 'El ID de producto es obligatorio.',
    'producto_id.*.numeric' => 'El ID de producto debe ser un número.',
    'fecha_del_pedido.*.required' => 'La fecha del pedido es obligatoria.',
    'fecha_del_pedido.*.date' => 'La fecha del pedido no tiene un formato válido.',
    'cant_fabricacion.*.required' => 'La cantidad de fabricación es obligatoria.',
    'cant_fabricacion.*.numeric' => 'La cantidad de fabricación debe ser un número.'
        ];
        
        $validated = $request->validate([
            'nro_of.*' => 'required|numeric|unique:pedido_cliente,Nro_OF',
            'producto_id.*' => 'required|numeric',
            'fecha_del_pedido.*' => 'required|date',
            'cant_fabricacion.*' => 'required|numeric'
        ], $messages);
        
        $duplicatedRows = [];
        if (!empty($request->nro_of)) {
            try {
                DB::beginTransaction();
                
                foreach ($request->nro_of as $index => $nro_of) {
                    if (isset($request->producto_id[$index], $request->fecha_del_pedido[$index], $request->cant_fabricacion[$index])) {
                        
                        $registroExistente = PedidoCliente::where('Nro_OF', $nro_of)->first();
                        
                        if ($registroExistente) {
                            $duplicatedRows[] = $index + 1; // Sumar 1 para alinearlo con los números de fila en la tabla
                        } else {
                            PedidoCliente::create([
                                'Nro_OF' => $nro_of,
                                'Producto_Id' => $request->producto_id[$index],
                                'Fecha_del_Pedido' => $request->fecha_del_pedido[$index],
                                'Cant_Fabricacion' => $request->cant_fabricacion[$index],
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id()
                            ]);
                        }
                    }
                }
                
                if (!empty($duplicatedRows)) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Algunas filas tienen errores de validación.', 'duplicatedRows' => $duplicatedRows], 400);
                }
                
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Órdenes de fabricación creadas correctamente.']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al guardar las órdenes de fabricación: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Ocurrió un error al intentar guardar las órdenes de fabricación.'], 500);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'El número de OF es obligatorio.'], 400);
        }
    }

    /**
     * Display the specified resource.
    */
    public function show(string $id)
    {
        //
    }
    
    /**
     * Show the form for editing the specified resource.
    */
    public function edit($id)
    
    {
        
        $productos = Producto::where('reg_Status', 1)
        ->select('Id_Producto', 'Prod_Codigo', 'Prod_Descripcion', 'Id_Prod_Clase_Familia', 'Id_Prod_Sub_Familia')
        ->get();
        
        $pedido = PedidoCliente::findOrFail($id);
        $productos = Producto::where('reg_Status', 1)->get();
        $categorias = ProductoCategoria::all(); // Si lo necesitás en un select
        $subcategorias = ProductoSubCategoria::all();
        
        return view('pedido_cliente.edit', [
            'pedido' => $pedido,
            'productos' => $productos,
            'categorias' => $categorias,
            'subcategorias' => $subcategorias,
        ]);
        
    }
    
    
    /**
     * Update the specified resource in storage.
    */
    public function update(Request $request, $id)

    
    {
        
        
        $pedido = PedidoCliente::findOrFail($id);
    $pedido->updated_by = Auth::id();
    
    
    Log::info('Intentando actualizar Pedido Cliente', [
        'id' => $id,
        'usuario' => Auth::id(),
        'request_data' => $request->all()
    ]);
    
    $validatedData = $request->validate([
        'Nro_OF' => [
            'required',
            'integer',
            Rule::unique('pedido_cliente', 'Nro_OF')
            ->ignore($id, 'Id_OF')
            ->whereNull('deleted_at'),
        ],
        'Producto_Id' => 'required|exists:productos,Id_Producto',
        'Fecha_del_Pedido' => 'required|date',
        'Cant_Fabricacion' => 'required|integer|min:1',
        'reg_Status' => 'required|in:0,1',
    ]);
    
    DB::beginTransaction();
    
    $pedido->fill($validatedData);
    
    if ($pedido->isDirty()) {
        $pedido->updated_by = Auth::id();
        $pedido->save();
        DB::commit();
        
        return redirect()->route('pedido_cliente.index')->with('success', 'Pedido actualizado correctamente.');
    } else {
        DB::rollBack();
        return back()->with('warning', 'No se detectaron cambios.');
    }
}




/**
 * Remove the specified resource from storage.
*/
public function destroy(string $id)
{
    try {
        // Buscar el pedido
        $pedido = PedidoCliente::findOrFail($id);
        
        // Verificar si existe alguna pieza fabricada con ese Nro_OF
        $existenFabricaciones = DB::table('registro_de_fabricacion')
        ->where('Nro_OF', $pedido->Nro_OF)
        ->exists();
        
        if ($existenFabricaciones) {
            return redirect()->route('pedido_cliente.index')
            ->with('error', 'No se puede eliminar el pedido. Ya existen piezas fabricadas asociadas a esta orden.');
        }
        
        // Si no hay fabricaciones, proceder con el soft delete
        $pedido->deleted_by = Auth::id();
        $pedido->save();
        $pedido->delete();
        
        return redirect()->route('pedido_cliente.index')
        ->with('success', 'Pedido eliminado correctamente.');
    } catch (\Exception $e) {
        Log::error('Error al eliminar pedido de cliente: ' . $e->getMessage());
        
        return redirect()->route('pedido_cliente.index')
        ->with('error', 'Ocurrió un error al intentar eliminar el pedido.');
    }
}


public function getCodigosProducto(Request $request)
{
    $productos = Producto::where('Id_Prod_Clase_Familia', $request->categoria)
        ->where('Id_Prod_Sub_Familia', $request->subcategoria)
        ->select('Id_Producto as id', 'Prod_Codigo as codigo')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $productos
    ]);
}


public function getSubcategorias(Request $request)
{
    $subcategorias = ProductoSubCategoria::where('Id_Categoria', $request->categoria)
                    ->select('Id_SubCategoria as id', 'Nombre_SubCategoria as nombre')
                    ->get();

    return response()->json([
        'success' => true,
        'data' => $subcategorias
    ]);
}




public function getIdProductoPorNroOf($nroOf)
{
 try {
     $pedido_cliente = PedidoCliente::where('Nro_OF', $nroOf)->firstOrFail();
     return response()->json([
         'success' => true,
         'id_producto' => $pedido_cliente->Producto_Id
        ]);
 } catch (\Exception $e) {
     return response()->json([
         'success' => false,
         'message' => 'Producto no encontrado.'
        ]);
    }
}


public function getUltimoNroOF()
{
    try {
        $ultimoOF = DB::table('pedido_cliente')
                       ->orderBy('Nro_OF', 'DESC')
                       ->value('Nro_OF');

        return response()->json(['success' => true, 'ultimo_nro_of' => $ultimoOF]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
}


}

