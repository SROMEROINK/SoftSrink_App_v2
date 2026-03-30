<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\EstadoPlanificacion;
use App\Models\PedidoCliente;
use App\Models\Producto;
use App\Models\ProductoCategoria;
use App\Models\ProductoSubcategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PedidoClienteController extends Controller
{
    use CheckForChanges;
    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only(['index', 'show']);
        $this->middleware('permission:editar produccion')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function getData(Request $request)
    {
        try {
            $pedidoCliente = PedidoCliente::with([
                'producto.categoria',
                'estadoPlanificacion',
                'definicionMp.estadoPlanificacion',
                'creator',
                'updater',
            ])
                ->select([
                    'Id_OF',
                    'Nro_OF',
                    'Producto_Id',
                    'Fecha_del_Pedido',
                    'Cant_Fabricacion',
                    'Estado_Plani_Id',
                    'reg_Status',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by',
                ])
                ->orderByDesc('Id_OF');

            if ($request->filled('filtro_nro_of')) {
                $pedidoCliente->where('Nro_OF', $request->filtro_nro_of);
            }

            if ($request->filled('filtro_producto')) {
                $pedidoCliente->whereHas('producto', function ($query) use ($request) {
                    $query->where('Prod_Codigo', 'like', '%' . $request->filtro_producto . '%');
                });
            }

            if ($request->filled('filtro_descripcion')) {
                $pedidoCliente->whereHas('producto', function ($query) use ($request) {
                    $query->where('Prod_Descripcion', 'like', '%' . $request->filtro_descripcion . '%');
                });
            }

            if ($request->filled('filtro_nombre_categoria')) {
                $pedidoCliente->whereHas('producto.categoria', function ($query) use ($request) {
                    $query->where('Nombre_Categoria', $request->filtro_nombre_categoria);
                });
            }

            if ($request->filled('filtro_fecha_pedido')) {
                $pedidoCliente->whereDate('Fecha_del_Pedido', $request->filtro_fecha_pedido);
            }

            if ($request->filled('filtro_cant_fabricacion')) {
                $pedidoCliente->where('Cant_Fabricacion', $request->filtro_cant_fabricacion);
            }

            if ($request->filled('filtro_estado_pedido')) {
                $pedidoCliente->where('Estado_Plani_Id', $request->filtro_estado_pedido);
            }

            return DataTables::eloquent($pedidoCliente)
                ->addColumn('Producto_Nombre', fn ($of) => $of->producto->Prod_Codigo ?? '')
                ->addColumn('Descripcion', fn ($of) => $of->producto->Prod_Descripcion ?? '')
                ->addColumn('Nombre_Categoria', fn ($of) => $of->producto->categoria->Nombre_Categoria ?? '')
                ->addColumn('Estado_Pedido', fn ($of) => $of->estadoPlanificacion->Nombre_Estado ?? '')
                ->addColumn('Estado_MP', fn ($of) => $of->definicionMp?->estadoPlanificacion?->Nombre_Estado ?? 'SIN DEFINIR MP')
                ->addColumn('Id_Pedido_MP', fn ($of) => $of->definicionMp?->Id_Pedido_MP)
                ->addColumn('creator', fn ($of) => $of->creator->name ?? '')
                ->addColumn('updater', fn ($of) => $of->updater->name ?? '')
                ->editColumn('Fecha_del_Pedido', fn ($of) => $of->Fecha_del_Pedido?->format('Y-m-d'))
                ->editColumn('created_at', fn ($of) => $of->created_at?->format('Y-m-d H:i:s'))
                ->editColumn('updated_at', fn ($of) => $of->updated_at?->format('Y-m-d H:i:s'))
                ->toJson();
        } catch (\Exception $e) {
            Log::error('Error en PedidoCliente getData: ' . $e->getMessage());

            return response()->json(['error' => 'Error al obtener datos'], 500);
        }
    }

    public function index()
    {
        return view('pedido_cliente.index');
    }

    public function indexPlain()
    {
        $pedidos = PedidoCliente::with([
            'producto.categoria',
            'producto.subCategoria',
            'estadoPlanificacion',
            'definicionMp.estadoPlanificacion',
        ])
            ->whereNull('deleted_at')
            ->orderByDesc('Id_OF')
            ->get();

        return view('pedido_cliente.index_plain', compact('pedidos'));
    }

    public function resumen()
    {
        return response()->json([
            'total' => PedidoCliente::withTrashed()->count(),
            'piezas' => (int) PedidoCliente::sum('Cant_Fabricacion'),
            'eliminados' => PedidoCliente::onlyTrashed()->count(),
        ]);
    }

    public function create()
    {
        $categorias = ProductoCategoria::select('Id_Categoria as id', 'Nombre_Categoria as nombre')->get();
        $subcategorias = ProductoSubcategoria::select('Id_SubCategoria as id', 'Id_Categoria', 'Nombre_SubCategoria as nombre')->get();
        $estadosPlanificacion = EstadoPlanificacion::where('Status', 1)
            ->orderBy('Estado_Plani_Id')
            ->get(['Estado_Plani_Id', 'Nombre_Estado']);
        $productosCatalogo = Producto::query()
            ->select([
                'Id_Producto',
                'Prod_Codigo',
                'Prod_Descripcion',
                'Id_Prod_Categoria',
                'Id_Prod_SubCategoria',
            ])
            ->where('reg_Status', 1)
            ->orderBy('Prod_Codigo')
            ->get();

        return view('pedido_cliente.create', compact('categorias', 'subcategorias', 'estadosPlanificacion', 'productosCatalogo'));
    }

    public function store(Request $request)
    {
        $messages = [
            'nro_of.*.required' => 'El numero de OF es obligatorio.',
            'nro_of.*.unique' => 'El numero de OF ya ha sido registrado.',
            'producto_id.*.required' => 'El ID de producto es obligatorio.',
            'producto_id.*.exists' => 'El producto seleccionado no existe en la base de datos.',
            'fecha_del_pedido.*.required' => 'La fecha del pedido es obligatoria.',
            'fecha_del_pedido.*.date' => 'La fecha del pedido no tiene un formato valido.',
            'cant_fabricacion.*.required' => 'La cantidad de fabricacion es obligatoria.',
            'cant_fabricacion.*.integer' => 'La cantidad de fabricacion debe ser un numero entero.',
            'cant_fabricacion.*.min' => 'La cantidad de fabricacion debe ser mayor a 0.',
            'estado_plani_id.*.required' => 'El estado del pedido es obligatorio.',
            'estado_plani_id.*.exists' => 'El estado del pedido no es valido.',
        ];

        $request->validate([
            'nro_of.*' => 'required|numeric|unique:pedido_cliente,Nro_OF',
            'producto_id.*' => 'required|exists:productos,Id_Producto',
            'fecha_del_pedido.*' => 'required|date',
            'cant_fabricacion.*' => 'required|integer|min:1',
            'estado_plani_id.*' => 'required|exists:estado_planificacion,Estado_Plani_Id',
        ], $messages);

        $duplicatedRows = [];
        $invalidRows = [];

        if (!empty($request->nro_of)) {
            try {
                DB::beginTransaction();

                foreach ($request->nro_of as $index => $nroOf) {
                    if (!isset($request->producto_id[$index], $request->fecha_del_pedido[$index], $request->cant_fabricacion[$index], $request->estado_plani_id[$index])) {
                        $invalidRows[] = $index + 1;
                        continue;
                    }

                    $registroExistente = PedidoCliente::where('Nro_OF', $nroOf)->first();

                    if ($registroExistente) {
                        $duplicatedRows[] = $index + 1;
                        continue;
                    }

                    $productoExiste = Producto::where('Id_Producto', $request->producto_id[$index])->exists();

                    if (!$productoExiste) {
                        $invalidRows[] = $index + 1;
                        continue;
                    }

                    PedidoCliente::create([
                        'Nro_OF' => $nroOf,
                        'Producto_Id' => $request->producto_id[$index],
                        'Fecha_del_Pedido' => $request->fecha_del_pedido[$index],
                        'Cant_Fabricacion' => $request->cant_fabricacion[$index],
                        'Estado_Plani_Id' => $request->estado_plani_id[$index],
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }

                if (!empty($duplicatedRows) || !empty($invalidRows)) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Algunas filas tienen errores de validacion.',
                        'duplicatedRows' => $duplicatedRows,
                        'invalidRows' => $invalidRows,
                    ], 400);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Ordenes de fabricacion creadas correctamente.',
                    'redirect' => route('pedido_cliente.index'),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al guardar las ordenes de fabricacion: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Ocurrio un error al intentar guardar las ordenes de fabricacion.',
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'El numero de OF es obligatorio.',
        ], 400);
    }

    public function show(string $id)
    {
        $pedido = PedidoCliente::with([
            'producto.categoria',
            'producto.subCategoria',
            'estadoPlanificacion',
        ])->findOrFail($id);

        return view('pedido_cliente.show', compact('pedido'));
    }

    public function edit($id)
    {
        $pedido = PedidoCliente::with(['producto', 'estadoPlanificacion'])->findOrFail($id);
        $productos = Producto::where('reg_Status', 1)
            ->select('Id_Producto', 'Prod_Codigo', 'Prod_Descripcion', 'Id_Prod_Categoria', 'Id_Prod_SubCategoria')
            ->get();
        $categorias = ProductoCategoria::all();
        $subcategorias = ProductoSubcategoria::all();
        $estadosPlanificacion = EstadoPlanificacion::where('Status', 1)
            ->orderBy('Estado_Plani_Id')
            ->get(['Estado_Plani_Id', 'Nombre_Estado']);

        return view('pedido_cliente.edit', compact('pedido', 'productos', 'categorias', 'subcategorias', 'estadosPlanificacion'));
    }

    public function update(Request $request, $id)
    {
        $pedido = PedidoCliente::findOrFail($id);

        Log::info('Intentando actualizar Pedido Cliente', [
            'id' => $id,
            'usuario' => Auth::id(),
            'request_data' => $request->all(),
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
            'Estado_Plani_Id' => 'required|exists:estado_planificacion,Estado_Plani_Id',
            'reg_Status' => 'required|in:0,1',
        ]);

        return $this->updateIfChanged($pedido, $validatedData, [
            'success_redirect' => route('pedido_cliente.index'),
            'success_message' => 'Pedido actualizado correctamente.',
            'no_changes_message' => 'No se detectaron cambios.',
            'set_updated_by' => true,
            'use_transaction' => true,
            'normalize_data' => false,
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $pedido = PedidoCliente::findOrFail($id);

            $existenFabricaciones = DB::table('registro_de_fabricacion')
                ->where('Nro_OF', $pedido->Nro_OF)
                ->exists();

            if ($existenFabricaciones) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar el pedido. Ya existen piezas fabricadas asociadas a esta orden.',
                    ], 400);
                }

                return redirect()->route('pedido_cliente.index')
                    ->with('error', 'No se puede eliminar el pedido. Ya existen piezas fabricadas asociadas a esta orden.');
            }

            $pedido->deleted_by = Auth::id();
            $pedido->save();
            $pedido->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pedido eliminado correctamente.',
                ]);
            }

            return redirect()->route('pedido_cliente.index')
                ->with('success', 'Pedido eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar pedido de cliente: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ocurrio un error al intentar eliminar el pedido.',
                ], 500);
            }

            return redirect()->route('pedido_cliente.index')
                ->with('error', 'Ocurrio un error al intentar eliminar el pedido.');
        }
    }

    public function getCodigosProducto(Request $request)
    {
        $productos = Producto::where('Id_Prod_Categoria', $request->categoria)
            ->where('Id_Prod_SubCategoria', $request->subcategoria)
            ->select('Id_Producto as id', 'Prod_Codigo as codigo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productos,
        ]);
    }

    public function getSubcategorias(Request $request)
    {
        $subcategorias = ProductoSubcategoria::where('Id_Categoria', $request->categoria)
            ->select('Id_SubCategoria as id', 'Nombre_SubCategoria as nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategorias,
        ]);
    }

    public function getIdProductoPorNroOf($nroOf)
    {
        try {
            $pedidoCliente = PedidoCliente::where('Nro_OF', $nroOf)->firstOrFail();

            return response()->json([
                'success' => true,
                'id_producto' => $pedidoCliente->Producto_Id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado.',
            ]);
        }
    }

    public function getUltimoNroOF()
    {
        try {
            $ultimoOF = DB::table('pedido_cliente')
                ->orderByDesc('Nro_OF')
                ->value('Nro_OF');

            return response()->json(['success' => true, 'ultimo_nro_of' => $ultimoOF]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
