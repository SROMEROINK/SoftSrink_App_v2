<?php
//app\Http\Controllers\ListadoOfController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\PedidoCliente;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\SubCategoria;

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
            $pedido_cliente = PedidoCliente::with(['producto', 'categoria'])
                ->select(
                    'Id_OF', 'Nro_OF', 'Producto_Id',
                    'Fecha_del_Pedido', 'Cant_Fabricacion'
                    
                )->orderBy('Nro_OF', 'desc'); // Cambiar a descendente;

            if ($request->has('filtro_nro_of') && $request->filtro_nro_of != '') {
                $pedido_cliente->where('Nro_OF', $request->filtro_nro_of);
            }
            if ($request->has('filtro_producto') && $request->filtro_producto != '') {
                $pedido_cliente->whereHas('producto', function ($query) use ($request) {
                    $query->where('Prod_Codigo', 'like', '%' . $request->filtro_producto . '%');
                });
            }
            if ($request->has('filtro_descripción') && $request->filtro_Descripción != '') {
                $pedido_cliente->whereHas('producto', function ($query) use ($request) {
                    $query->where('Prod_Descripcion', 'like', '%' . $request->filtro_Descripción . '%');
                });
            }
            if ($request->has('filtro_nombre_categoria') && $request->filtro_nombre_categoria != '') {
                    $pedido_cliente->whereHas('producto.categoria', function ($query) use ($request) {
                    $query->where('Nombre_Categoria', $request->filtro_nombre_categoria);
                });
            }
            if ($request->has('filtro_fecha_pedido') && $request->filtro_fecha_pedido != '') {
                $pedido_cliente->where('Fecha_del_Pedido', $request->filtro_fecha_pedido);
            }
            if ($request->has('filtro_cant_fabricacion') && $request->filtro_cant_fabricacion != '') {
                $pedido_cliente->where('Cant_Fabricacion', $request->filtro_cant_fabricacion);
            }

            return DataTables::eloquent($pedido_cliente)
                ->addColumn('Producto_Nombre', function ($of) {
                    return $of->producto->Prod_Codigo ?? '';
                })
                ->addColumn('Descripción', function ($of) {
                    return $of->producto->Prod_Descripcion ?? '';
                })
                ->addColumn('Nombre_Categoria', function ($of) {
                    return $of->producto->categoria->Nombre_Categoria ?? '';
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
    return view('pedido_cliente.index');
}
    
    


public function getUltimoNroOF()
{
    try {
        $ultimoOF = \DB::table('pedido_cliente')
                       ->orderBy('Nro_OF', 'DESC')
                       ->value('Nro_OF');

        return response()->json(['success' => true, 'ultimo_nro_of' => $ultimoOF]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
}





    public function getIdProductoPorNroOf($nroOf)
    {
        try {
            $pedido_cliente = pedido_cliente::where('Nro_OF', $nroOf)->firstOrFail();
            return response()->json(['success' => true, 'id_producto' => $pedido_cliente->Producto_Id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado.']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener las categorías de productos para cargarlas en el formulario
        $categorias = Categoria::select('Id_Categoria as id', 'Nombre_Categoria as nombre')->get();
    
        // Devolver la vista de creación con las categorías cargadas
        return view('pedido_cliente.create', compact('categorias'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
                \DB::beginTransaction();
    
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
                    \DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Algunas filas tienen errores de validación.', 'duplicatedRows' => $duplicatedRows], 400);
                }
    
                \DB::commit();
                return response()->json(['success' => true, 'message' => 'Órdenes de fabricación creadas correctamente.']);
            } catch (\Exception $e) {
                \DB::rollBack();
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getCodigoMp($mp_id)
    {
        try {
            $mpIngreso = Ingreso_mp::where('Id_MP', $mp_id)->firstOrFail();
            return response()->json(['success' => true, 'codigo_mp' => $mpIngreso->Codigo_MP]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Materia Prima no encontrada.']);
        }
    }
}
