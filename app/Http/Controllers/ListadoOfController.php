<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListadoOfController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only(['index', 'getData', 'resumen']);
    }

    protected function baseQuery(Request $request)
    {
        $query = DB::table('listado_of_db as lo')
            ->leftJoin('pedido_cliente as pc', function ($join) {
                $join->on('pc.Nro_OF', '=', 'lo.Nro_OF')
                    ->whereNull('pc.deleted_at');
            })
            ->leftJoin('pedido_cliente_mp as pm', function ($join) {
                $join->on('pm.Id_OF', '=', 'pc.Id_OF')
                    ->whereNull('pm.deleted_at');
            })
            ->leftJoin('productos as pr', 'pr.Prod_Codigo', '=', 'lo.Prod_Codigo')
            ->leftJoin('producto_categoria as cat', 'cat.Id_Categoria', '=', 'pr.Id_Prod_Categoria')
            ->select([
                'pc.Id_OF',
                'lo.Nro_OF',
                'lo.Estado_Planificacion',
                'lo.Estado',
                'lo.Prod_Codigo',
                'lo.Prod_Descripcion',
                'lo.Nombre_Categoria',
                'lo.Revision_Plano_1',
                'lo.Revision_Plano_2',
                'lo.Fecha_del_Pedido',
                'lo.Cant_Fabricacion',
                'lo.Nro_Maquina',
                'lo.Familia_Maquinas',
                'lo.MP_Id',
                'lo.Nro_Ingreso_MP',
                'lo.Codigo_MP',
                'lo.Nro_Certificado_MP',
                'lo.Pedido_de_MP',
                'lo.Prov_Nombre',
                'lo.Piezas_Fabricadas',
                'lo.Saldo_Fabricacion',
                'lo.Ultima_Fecha_Fabricacion',
                'pc.created_at',
                'pc.updated_at',
                'pm.Id_Pedido_MP',
            ]);

        if ($request->filled('filtro_nro_of')) {
            $query->where('lo.Nro_OF', $request->filtro_nro_of);
        }

        if ($request->filled('filtro_estado_planificacion')) {
            $query->where('lo.Estado_Planificacion', $request->filtro_estado_planificacion);
        }

        if ($request->filled('filtro_estado')) {
            $query->where('lo.Estado', $request->filtro_estado);
        }

        if ($request->filled('filtro_producto')) {
            $query->where('lo.Prod_Codigo', 'like', '%' . $request->filtro_producto . '%');
        }

        if ($request->filled('filtro_descripcion')) {
            $query->where('lo.Prod_Descripcion', 'like', '%' . $request->filtro_descripcion . '%');
        }

        if ($request->filled('filtro_categoria')) {
            $query->where('lo.Nombre_Categoria', $request->filtro_categoria);
        }

        if ($request->filled('filtro_fecha_pedido')) {
            $query->whereDate('lo.Fecha_del_Pedido', $request->filtro_fecha_pedido);
        }

        if ($request->filled('filtro_nro_maquina')) {
            $query->where('lo.Nro_Maquina', $request->filtro_nro_maquina);
        }

        if ($request->filled('filtro_familia_maquina')) {
            $query->where('lo.Familia_Maquinas', $request->filtro_familia_maquina);
        }

        if ($request->filled('filtro_nro_ingreso_mp')) {
            $query->where('lo.Nro_Ingreso_MP', $request->filtro_nro_ingreso_mp);
        }

        if ($request->filled('filtro_codigo_mp')) {
            $query->where('lo.Codigo_MP', 'like', '%' . $request->filtro_codigo_mp . '%');
        }

        if ($request->filled('filtro_certificado_mp')) {
            $query->where('lo.Nro_Certificado_MP', 'like', '%' . $request->filtro_certificado_mp . '%');
        }

        if ($request->filled('filtro_proveedor')) {
            $query->where('lo.Prov_Nombre', $request->filtro_proveedor);
        }

        if ($request->filled('filtro_piezas_fabricadas')) {
            $query->where('lo.Piezas_Fabricadas', (int) $request->filtro_piezas_fabricadas);
        }

        return $query->orderByDesc('lo.Nro_OF');
    }

    public function index()
    {
        return view('listado_of.index');
    }

    public function getData(Request $request)
    {
        try {
            $query = $this->baseQuery($request);

            return datatables()->query($query)
                ->editColumn('Fecha_del_Pedido', function ($row) {
                    return $row->Fecha_del_Pedido ? date('d/m/Y', strtotime($row->Fecha_del_Pedido)) : '';
                })
                ->editColumn('Ultima_Fecha_Fabricacion', function ($row) {
                    return $row->Ultima_Fecha_Fabricacion ? date('d/m/Y', strtotime($row->Ultima_Fecha_Fabricacion)) : '';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? date('Y-m-d H:i:s', strtotime($row->created_at)) : '';
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? date('Y-m-d H:i:s', strtotime($row->updated_at)) : '';
                })
                ->addColumn('acciones', function ($row) {
                    $botones = '<a href="' . route('pedido_cliente.show', $row->Id_OF) . '" class="btn btn-info btn-sm">Ver pedido</a> ';

                    if (!empty($row->Id_Pedido_MP)) {
                        $botones .= '<a href="' . route('pedido_cliente_mp.editMassive', $row->Id_Pedido_MP) . '" class="btn btn-success btn-sm">MP</a> ';
                    }

                    $botones .= '<a href="' . route('fabricacion.showByNroOF', $row->Id_OF) . '" class="btn btn-primary btn-sm">Fabricacion</a>';

                    return $botones;
                })
                ->rawColumns(['acciones'])
                ->toJson();
        } catch (\Throwable $e) {
            Log::error('Error en Listado OF getData', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'No se pudo cargar el listado OF.'], 500);
        }
    }

    public function resumen(Request $request)
    {
        try {
            $query = $this->baseQuery($request);

            return response()->json([
                'total_of' => (clone $query)->count(),
                'total_piezas_solicitadas' => (int) ((clone $query)->sum('lo.Cant_Fabricacion') ?? 0),
                'total_piezas_fabricadas' => (int) ((clone $query)->sum('lo.Piezas_Fabricadas') ?? 0),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en Listado OF resumen', ['error' => $e->getMessage()]);

            return response()->json([
                'total_of' => 0,
                'total_piezas_solicitadas' => 0,
                'total_piezas_fabricadas' => 0,
            ], 500);
        }
    }
}
