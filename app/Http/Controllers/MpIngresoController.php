<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MpIngreso;

class MpIngresoController extends Controller
{

    public function getUniqueFilters()
{
    try {
        // Obtener valores únicos de todos los registros
        $proveedores = MpIngreso::join('proveedores', 'mp_ingreso.Id_Proveedor', '=', 'proveedores.Prov_Id')
            ->distinct()
            ->pluck('proveedores.Prov_Nombre')
            ->sort()
            ->values();

        $materiasPrimas = MpIngreso::distinct()->pluck('mp_ingreso.Materia_Prima')->sort()->values();
        $diametros = MpIngreso::distinct()->pluck('mp_ingreso.Diametro_MP')->sort()->values();
        $codigos = MpIngreso::distinct()->pluck('mp_ingreso.Codigo_MP')->sort()->values();

        // Retornar los valores únicos como respuesta JSON
        return response()->json([
            'proveedores' => $proveedores,
            'materias_primas' => $materiasPrimas,
            'diametros' => $diametros,
            'codigos' => $codigos
        ]);

    } catch (\Exception $e) {
        \Log::error('Error en getUniqueFilters: ' . $e->getMessage());
        return response()->json(['error' => 'Error al recuperar los filtros únicos.'], 500);
    }
}

    
    public function getData(Request $request)
    {
        try {
            $ingresos_mp = MpIngreso::with('proveedor')
                ->select(
                    'mp_ingreso.Id_MP',
                    'mp_ingreso.Nro_Ingreso_MP',
                    'mp_ingreso.Nro_Pedido',
                    'mp_ingreso.Nro_Remito',
                    'mp_ingreso.Fecha_Ingreso',
                    'mp_ingreso.Nro_OC',
                    'mp_ingreso.Id_Proveedor',
                    'mp_ingreso.Materia_Prima',
                    'mp_ingreso.Diametro_MP',
                    'mp_ingreso.Codigo_MP',
                    'mp_ingreso.Nro_Certificado_MP',
                    'mp_ingreso.Detalle_Origen_MP',
                    'mp_ingreso.Unidades_MP',
                    'mp_ingreso.Longitud_Unidad_MP',
                    'mp_ingreso.Mts_Totales',
                    'mp_ingreso.Kilos_Totales',
                    'mp_ingreso.created_at AS mp_ingreso_created_at',
                    'mp_ingreso.updated_at AS mp_ingreso_updated_at'
                )
                ->join('proveedores', 'mp_ingreso.Id_Proveedor', '=', 'proveedores.Prov_Id');
    
            // Aplicar filtros exactos basados en los campos del request
            if ($request->filled('filtro_proveedor')) {
                $ingresos_mp->where('proveedores.Prov_Nombre', '=', $request->filtro_proveedor);
            }
    
            if ($request->filled('filtro_materia_prima')) {
                $ingresos_mp->where('mp_ingreso.Materia_Prima', '=', $request->filtro_materia_prima);
            }
    
            if ($request->filled('filtro_diametro')) {
                $ingresos_mp->where('mp_ingreso.Diametro_MP', '=', $request->filtro_diametro);
            }
    
            if ($request->filled('filtro_codigo_mp')) {
                $ingresos_mp->where('mp_ingreso.Codigo_MP', '=', $request->filtro_codigo_mp);
            }
    
            if ($request->filled('filtro_nro_oc')) {
                $ingresos_mp->where('mp_ingreso.Nro_OC', '=', $request->filtro_nro_oc);
            }
    
            if ($request->filled('filtro_nro_ingreso')) {
                $ingresos_mp->where('mp_ingreso.Nro_Ingreso_MP', '=', $request->filtro_nro_ingreso);
            }
    
            if ($request->filled('filtro_nro_pedido')) {
                $ingresos_mp->where('mp_ingreso.Nro_Pedido', '=', $request->filtro_nro_pedido);
            }
    
            if ($request->filled('filtro_nro_remito')) {
                $ingresos_mp->where('mp_ingreso.Nro_Remito', '=', $request->filtro_nro_remito);
            }
    
            if ($request->filled('filtro_certificado')) {
                $ingresos_mp->where('mp_ingreso.Nro_Certificado_MP', '=', $request->filtro_certificado);
            }
    
            if ($request->filled('filtro_detalle_origen')) {
                $ingresos_mp->where('mp_ingreso.Detalle_Origen_MP', '=', $request->filtro_detalle_origen);
            }
    
            if ($request->filled('filtro_unidades')) {
                $ingresos_mp->where('mp_ingreso.Unidades_MP', '=', $request->filtro_unidades);
            }
    
            if ($request->filled('filtro_longitud')) {
                $ingresos_mp->where('mp_ingreso.Longitud_Unidad_MP', '=', $request->filtro_longitud);
            }
    
            if ($request->filled('filtro_mts_totales')) {
                $ingresos_mp->where('mp_ingreso.Mts_Totales', '=', $request->filtro_mts_totales);
            }
    
            if ($request->filled('filtro_kilos_totales')) {
                $ingresos_mp->where('mp_ingreso.Kilos_Totales', '=', $request->filtro_kilos_totales);
            }
    
            return datatables()->of($ingresos_mp)
                ->addColumn('Proveedor', function ($mpIngreso) {
                    return $mpIngreso->proveedor ? $mpIngreso->proveedor->Prov_Nombre : 'No Asignado';
                })
                ->make(true);
    
        } catch (\Exception $e) {
            \Log::error('Error en getData: ' . $e->getMessage());
            return response()->json(['error' => 'Error al recuperar los datos.'], 500);
        }
    }
    
    
    
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener los Ingresos_mp de la base de datos y paginarlos
        $ingresos_mp = MpIngreso::paginate(10); // Esto paginará los resultados, mostrando 10 Ingresos_mp de por página
    
        // Pasar los Ingresos_mp paginados a la vista correspondiente
        return view('materia_prima.ingresos.index', compact('ingresos_mp'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
