<?php
//app\Http\Controllers\ListadoOfController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Listado_OF;

class ListadoOfController extends Controller
{

        public function __construct()
    {
        $this->middleware('permission:ver produccion')->only('index');
        $this->middleware('permission:ver produccion')->only('show');
        $this->middleware('permission:editar produccion')->only(['create', 'store']);
        $this->middleware('permission:editar produccion')->only(['edit', 'update']);
        $this->middleware('permission:editar produccion')->only('destroy');
    }

// Controlador para listado_of
public function getData(Request $request)
{
    try {
        if ($request->ajax()) {
            $listado_of = Listado_OF::with(['producto', 'categoria', 'ingreso_mp'])
                ->select(
                    'Id_OF', 'Nro_OF', 'Estado_Planificacion', 'Estado', 'Producto_Id',
                    'Revision_Plano_2', 'Fecha_del_Pedido', 'Cant_Fabricacion',
                    'Nro_Maquina', 'Familia_Maquinas', 'MP_Id', 'Pedido_de_MP',
                    'Tiempo_Pieza_Real', 'Tiempo_Pieza_Aprox', 'Cant_Unidades_MP', 'Cant_Piezas_Por_Unidad_MP'
                );

            if ($request->has('filtro_estado_planificacion') && $request->filtro_estado_planificacion != '') {
                $listado_of->where('Estado_Planificacion', $request->filtro_estado_planificacion);
            }
            if ($request->has('filtro_estado') && $request->filtro_estado != '') {
                $listado_of->where('Estado', $request->filtro_estado);
            }
            if ($request->has('filtro_nro_maquina') && $request->filtro_nro_maquina != '') {
                $listado_of->where('Nro_Maquina', $request->filtro_nro_maquina);
            }
            if ($request->has('filtro_familia_maquinas') && $request->filtro_familia_maquinas != '') {
                $listado_of->where('Familia_Maquinas', $request->filtro_familia_maquinas);
            }
            if ($request->has('filtro_nro_of') && $request->filtro_nro_of != '') {
                $listado_of->where('Nro_OF', $request->filtro_nro_of);
            }
            if ($request->has('filtro_producto') && $request->filtro_producto != '') {
                $listado_of->whereHas('producto', function ($query) use ($request) {
                    $query->where('Prod_Codigo', 'like', '%' . $request->filtro_producto . '%');
                });
            }
            if ($request->has('filtro_descripción') && $request->filtro_Descripción != '') {
                $listado_of->whereHas('producto', function ($query) use ($request) {
                    $query->where('Prod_Descripcion', 'like', '%' . $request->filtro_Descripción . '%');
                });
            }
            if ($request->has('filtro_nombre_categoria') && $request->filtro_nombre_categoria != '') {
                    $listado_of->whereHas('producto.categoria', function ($query) use ($request) {
                    $query->where('Nombre_Categoria', $request->filtro_nombre_categoria);
                });
            }
            if ($request->has('filtro_nro_plano') && $request->filtro_nro_plano != '') {
                $listado_of->whereHas('producto', function ($query) use ($request) {
                    $query->where('Prod_N_Plano', $request->filtro_nro_plano);
                });
            }
            if ($request->has('filtro_nro_revision') && $request->filtro_nro_revision != '') {
                $listado_of->where('Revision_Plano_2', $request->filtro_nro_revision);
            }
            if ($request->has('filtro_fecha_pedido') && $request->filtro_fecha_pedido != '') {
                $listado_of->where('Fecha_del_Pedido', $request->filtro_fecha_pedido);
            }
            if ($request->has('filtro_cant_fabricacion') && $request->filtro_cant_fabricacion != '') {
                $listado_of->where('Cant_Fabricacion', $request->filtro_cant_fabricacion);
            }
            if ($request->has('filtro_mp_id') && $request->filtro_mp_id != '') {
                $listado_of->where('MP_Id', $request->filtro_mp_id);
            }
            if ($request->has('filtro_pedido_mp') && $request->filtro_pedido_mp != '') {
                $listado_of->where('Pedido_de_MP', $request->filtro_pedido_mp);
            }
            if ($request->has('filtro_tiempo_pieza_real') && $request->filtro_tiempo_pieza_real != '') {
                $listado_of->where('Tiempo_Pieza_Real', $request->filtro_tiempo_pieza_real);
            }
            if ($request->has('filtro_tiempo_pieza_aprox') && $request->filtro_tiempo_pieza_aprox != '') {
                $listado_of->where('Tiempo_Pieza_Aprox', $request->filtro_tiempo_pieza_aprox);
            }
            if ($request->has('filtro_cant_unidades_mp') && $request->filtro_cant_unidades_mp != '') {
                $listado_of->where('Cant_Unidades_MP', $request->filtro_cant_unidades_mp);
            }
            if ($request->has('filtro_cant_piezas_por_unidad_mp') && $request->filtro_cant_piezas_por_unidad_mp != '') {
                $listado_of->where('Cant_Piezas_Por_Unidad_MP', $request->filtro_cant_piezas_por_unidad_mp);
            }

            return DataTables::eloquent($listado_of)
                ->addColumn('Producto_Nombre', function ($of) {
                    return $of->producto->Prod_Codigo ?? '';
                })
                ->addColumn('Descripción', function ($of) {
                    return $of->producto->Prod_Descripcion ?? '';
                })
                ->addColumn('Nombre_Categoria', function ($of) {
                    return $of->producto->categoria->Nombre_Categoria ?? '';
                })
                ->addColumn('Prod_N_Plano', function ($of) {
                    return $of->producto->Prod_N_Plano ?? '';
                })
                ->addColumn('Revision_Plano_2', function ($of) {
                    return $of->Revision_Plano_2 ?? '';
                })
                ->addColumn('Nro_Ingreso_MP', function ($of) {
                    return $of->ingreso_mp->Nro_Ingreso_MP ?? '';
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
    return view('Listado_OF.index');
}
    
    


    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     // Obtener el valor del filtro de la solicitud
    //     $filtroNroOF = $request->query('filtroNroOF');
    
    //     if ($filtroNroOF) {
    //         $listados_of = Listado_OF::where('Nro_OF', $filtroNroOF)->get();
    //     } else {
    //         $listados_of = Listado_OF::all();
    //     }
    
    //     // Pasar los resultados a la vista
    //     return view('Listado_de_OF.index', compact('listados_of', 'filtroNroOF'));
    // }



    public function getIdProductoPorNroOf($nroOf)
    {
        try {
            $listado_of = Listado_OF::where('Nro_OF', $nroOf)->firstOrFail();
            return response()->json(['success' => true, 'id_producto' => $listado_of->Producto_Id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado.']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Devolver la vista de creación
        return view('Listado_OF.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'nro_of.*' => 'required|numeric',
        'estado_planificacion.*' => 'required|string',
        'estado.*' => 'required|string',
        'producto_id.*' => 'required|numeric',
        'revision_plano_1.*' => 'required|string',
        'revision_plano_2.*' => 'required|string',
        'fecha_del_pedido.*' => 'required|date',
        'cant_fabricacion.*' => 'required|numeric',
        'nro_maquina.*' => 'nullable|string',
        'familia_maquinas.*' => 'nullable|string',
        'mp_id.*' => 'required|numeric',
        'pedido_de_mp.*' => 'nullable|string',
        'tiempo_pieza_real.*' => 'nullable|numeric',
        'tiempo_pieza_aprox.*' => 'nullable|numeric',
    ]);

    foreach ($request->nro_of as $key => $nro_of) {
        Listado_OF::create([
            'Nro_OF' => $nro_of,
            'Estado_Planificacion' => $request->estado_planificacion[$key],
            'Estado' => $request->estado[$key],
            'Producto_Id' => $request->producto_id[$key],
            'Revision_Plano_1' => $request->revision_plano_1[$key],
            'Revision_Plano_2' => $request->revision_plano_2[$key],
            'Fecha_del_Pedido' => $request->fecha_del_pedido[$key],
            'Cant_Fabricacion' => $request->cant_fabricacion[$key],
            'Nro_Maquina' => $request->nro_maquina[$key] ?? null,
            'Familia_Maquinas' => $request->familia_maquinas[$key] ?? null,
            'MP_Id' => $request->mp_id[$key],
            'Pedido_de_MP' => $request->pedido_de_mp[$key] ?? null,
            'Tiempo_Pieza_Real' => $request->tiempo_pieza_real[$key] ?? null,
            'Tiempo_Pieza_Aprox' => $request->tiempo_pieza_aprox[$key] ?? null,
        ]);
    }

    return redirect()->route('listado_of.index')->with('success', 'Ordenes de fabricación creadas correctamente.');
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
}
