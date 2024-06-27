<?php

namespace App\Http\Controllers;

use App\Models\ListadoEntregaProducto;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;


     class ListadoEntregaProductoController extends Controller
     {

        public function getData(Request $request)
        {
            try {
                if ($request->ajax()) {
                    $entregas = ListadoEntregaProducto::with([
                        'listado_of.producto.categoria', 
                        'listado_of.producto', 
                        'listado_of.ingreso_mp.proveedor'
                    ])->select(
                        'Id_List_Entreg_Prod', 'Id_OF', 'Cant_Piezas_Entregadas', 
                        'Nro_Remito_Entrega_Calidad', 'Fecha_Entrega_Calidad', 'Nro_Parcial_Calidad', 
                        'Inspector_Calidad'
                    );
        
                    $data = DataTables::eloquent($entregas)
                        ->addColumn('Prod_Codigo', function ($entrega) {
                            return $entrega->listado_of->producto->Prod_Codigo ?? '';
                        })
                        ->addColumn('Prod_Descripcion', function ($entrega) {
                            return $entrega->listado_of->producto->Prod_Descripcion ?? '';
                        })
                        ->addColumn('Nombre_Categoria', function ($entrega) {
                            return $entrega->listado_of->producto->categoria->Nombre_Categoria ?? '';
                        })
                        ->addColumn('Nro_Maquina', function ($entrega) {
                            return $entrega->listado_of->Nro_Maquina ?? '';
                        })
                        ->addColumn('Nro_Ingreso_MP', function ($entrega) {
                            return $entrega->listado_of->ingreso_mp->Nro_Ingreso_MP ?? '';
                        })
                        ->addColumn('Codigo_MP', function ($entrega) {
                            return $entrega->listado_of->ingreso_mp->Codigo_MP ?? '';
                        })
                        ->addColumn('N_Certificado_MP', function ($entrega) {
                            return $entrega->listado_of->ingreso_mp->N°_Certificado_MP ?? '';
                        })
                        ->addColumn('Nombre_Proveedor', function ($entrega) {
                            return $entrega->listado_of->ingreso_mp->proveedor->Prov_Nombre ?? '';
                        })
                        ->filter(function ($query) use ($request) {
                            if ($request->has('filtro_clase_familia') && $request->filtro_clase_familia != '') {
                                $query->whereHas('listado_of.producto.categoria', function ($q) use ($request) {
                                    $q->where('Nombre_Categoria', $request->filtro_clase_familia);
                                });
                            }
                            if ($request->has('filtro_codigo_mp') && $request->filtro_codigo_mp != '') {
                                $query->whereHas('listado_of.ingreso_mp', function ($q) use ($request) {
                                    $q->where('Codigo_MP', $request->filtro_codigo_mp);
                                });
                            }
                            if ($request->has('filtro_nombre_proveedor') && $request->filtro_nombre_proveedor != '') {
                                $query->whereHas('listado_of.ingreso_mp.proveedor', function ($q) use ($request) {
                                    $q->where('Prov_Nombre', $request->filtro_nombre_proveedor);
                                });
                            }
                            if ($request->has('filtro_nombre_inspector') && $request->filtro_nombre_inspector != '') {
                                $query->where('Inspector_Calidad', 'like', "%{$request->filtro_nombre_inspector}%");
                            }
                            if ($request->has('filtro_id') && $request->filtro_id != '') {
                                $query->where('Id_List_Entreg_Prod', 'like', "%{$request->filtro_id}%");
                            }
                            if ($request->has('filtro_nro_of') && $request->filtro_nro_of != '') {
                                $query->where('Id_OF', 'like', "%{$request->filtro_nro_of}%");
                            }
                            if ($request->has('filtro_codigo_producto') && $request->filtro_codigo_producto != '') {
                                $query->whereHas('listado_of.producto', function ($q) use ($request) {
                                    $q->where('Prod_Codigo', 'like', "%{$request->filtro_codigo_producto}%");
                                });
                            }
                            if ($request->has('filtro_descripcion') && $request->filtro_descripcion != '') {
                                $query->whereHas('listado_of.producto', function ($q) use ($request) {
                                    $q->where('Prod_Descripcion', 'like', "%{$request->filtro_descripcion}%");
                                });
                            }
                            if ($request->has('filtro_nro_maquina') && $request->filtro_nro_maquina != '') {
                                $query->whereHas('listado_of', function ($q) use ($request) {
                                    $q->where('Nro_Maquina', 'like', "%{$request->filtro_nro_maquina}%");
                                });
                            }
                            if ($request->has('filtro_nro_ingreso_mp') && $request->filtro_nro_ingreso_mp != '') {
                                $query->whereHas('listado_of.ingreso_mp', function ($q) use ($request) {
                                    $q->where('Nro_Ingreso_MP', 'like', "%{$request->filtro_nro_ingreso_mp}%");
                                });
                            }
                            if ($request->has('filtro_nro_certificado_mp') && $request->filtro_nro_certificado_mp != '') {
                                $query->whereHas('listado_of.ingreso_mp', function ($q) use ($request) {
                                    $q->where('N°_Certificado_MP', 'like', "%{$request->filtro_nro_certificado_mp}%");
                                });
                            }
                            if ($request->has('filtro_nro_parcial_of') && $request->filtro_nro_parcial_of != '') {
                                $query->where('Nro_Parcial_Calidad', 'like', "%{$request->filtro_nro_parcial_of}%");
                            }
                            if ($request->has('filtro_cant_piezas') && $request->filtro_cant_piezas != '') {
                                $query->where('Cant_Piezas_Entregadas', 'like', "%{$request->filtro_cant_piezas}%");
                            }
                            if ($request->has('filtro_nro_remito') && $request->filtro_nro_remito != '') {
                                $query->where('Nro_Remito_Entrega_Calidad', 'like', "%{$request->filtro_nro_remito}%");
                            }
                            if ($request->has('filtro_fecha_entrega') && $request->filtro_fecha_entrega != '') {
                                $query->where('Fecha_Entrega_Calidad', 'like', "%{$request->filtro_fecha_entrega}%");
                            }
                        })
                        ->make(true);
        
                    return $data;
                }
            } catch (\Exception $e) {
                Log::error('Error in getData: ' . $e->getMessage());
                return response()->json(['error' => 'Error fetching data'], 500);
            }
        } 
        
        
        public function index()
        {
            return view('entregas_productos.index');
        }
    
    // public function index(Request $request)
    // {
    //     // Obtener el valor del filtro de la solicitud
    //     $filtroNroOF_entregas = $request->query('filtroNroOF_entregas');
    //     $entrega_productos = ListadoEntregaProducto::with('listado_of.producto')->get();
    
    //     // Pasar los Ingresos_mp paginados a la vista correspondiente
    //     return view('Entregas_Productos.index', compact('entrega_productos','filtroNroOF_entregas'));
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('entregas_productos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
