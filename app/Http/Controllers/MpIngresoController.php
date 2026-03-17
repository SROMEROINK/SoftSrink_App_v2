<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule; // al inicio del controlador
use App\Http\Controllers\Traits\CheckForChanges;
use Illuminate\Http\Request;
use App\Models\MpIngreso;
use App\Models\Proveedor;  // Asegúrate de importar el modelo Proveedor correctamente
use App\Models\MpDiametro;
use App\Models\MpMateriaPrima;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;






class MpIngresoController extends Controller
{
    use CheckForChanges;

    public function getUniqueFilters(Request $request)
{
    try {
        // Crear una consulta base sin filtrar
        $baseQuery = MpIngreso::query();

        // Aplicar filtros según el selector anterior para actualizar dinámicamente
        if ($request->filled('filtro_proveedor')) {
            $baseQuery->whereHas('proveedor', function ($q) use ($request) {
                $q->where('Prov_Nombre', $request->filtro_proveedor);
            });
        }

        // Obtener valores únicos con los filtros aplicados
        $proveedores = MpIngreso::join('proveedores', 'mp_ingreso.Id_Proveedor', '=', 'proveedores.Prov_Id')
            ->distinct()
            ->pluck('proveedores.Prov_Nombre')
            ->sort()
            ->values();

        $materiasPrimas = $baseQuery->join('mp_materia_prima', 'mp_ingreso.Id_Materia_Prima', '=', 'mp_materia_prima.Id_Materia_Prima')
            ->distinct()
            ->pluck('mp_materia_prima.Nombre_Materia')
            ->sort()
            ->values();

        $diametros = $baseQuery->join('mp_diametro', 'mp_ingreso.Id_Diametro_MP', '=', 'mp_diametro.Id_Diametro')
            ->distinct()
            ->pluck('mp_diametro.Valor_Diametro')
            ->sort()
            ->values();

        $codigos = $baseQuery->distinct()->pluck('mp_ingreso.Codigo_MP')->sort()->values();

        // Retornar los valores únicos como respuesta JSON
        return response()->json([
            'proveedores' => $proveedores,
            'materias_primas' => $materiasPrimas,
            'diametros' => $diametros,
            'codigos' => $codigos
        ]);

    } catch (\Exception $e) {
        Log::error('Error en getUniqueFilters: ' . $e->getMessage());
        return response()->json(['error' => 'Error al recuperar los filtros únicos.'], 500);
    }
}

public function getUltimoNroIngreso()
{
    try {
        $ultimoNroIngreso = MpIngreso::orderBy('Nro_Ingreso_MP', 'DESC')->value('Nro_Ingreso_MP');
        $nuevoNroIngreso = $ultimoNroIngreso ? $ultimoNroIngreso + 1 : 1; // Comienza desde 1 si no hay registros

        return response()->json(['success' => true, 'nuevo_nro_ingreso' => $nuevoNroIngreso]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
}

    
public function getData(Request $request)
{
    try {
        $ingresos_mp = MpIngreso::with(['proveedor'])
            ->leftJoin('mp_materia_prima', 'mp_ingreso.Id_Materia_Prima', '=', 'mp_materia_prima.Id_Materia_Prima')
            ->leftJoin('mp_diametro', 'mp_ingreso.Id_Diametro_MP', '=', 'mp_diametro.Id_Diametro')
            ->join('proveedores', 'mp_ingreso.Id_Proveedor', '=', 'proveedores.Prov_Id')
            ->select(
                'mp_ingreso.Id_MP',
                'mp_ingreso.Nro_Ingreso_MP',
                'mp_ingreso.Fecha_Ingreso',
                'mp_ingreso.Nro_OC',
                'mp_ingreso.Id_Proveedor',
                'mp_materia_prima.Nombre_Materia as Materia_Prima',
                'mp_diametro.Valor_Diametro as Diametro_MP',
                'mp_ingreso.Codigo_MP',
                'mp_ingreso.Nro_Certificado_MP',
                'mp_ingreso.Detalle_Origen_MP',
                'mp_ingreso.Unidades_MP',
                'mp_ingreso.Longitud_Unidad_MP',
                'mp_ingreso.Mts_Totales',
                'mp_ingreso.Kilos_Totales',
                'mp_ingreso.created_at as mp_ingreso_created_at',
                'mp_ingreso.updated_at as mp_ingreso_updated_at'
            )
            ->orderBy('mp_ingreso.Nro_Ingreso_MP', 'desc');

        // Filtro Nro_Ingreso_MP
        if ($request->filled('filtro_nro_ingreso')) {
            $ingresos_mp->where('mp_ingreso.Nro_Ingreso_MP', 'like', '%' . $request->filtro_nro_ingreso . '%');
        }

        // Filtro Fecha_Ingreso
        if ($request->filled('filtro_fecha_ingreso')) {
            $ingresos_mp->whereDate('mp_ingreso.Fecha_Ingreso','like', '%' . $request->filtro_fecha_ingreso . '%');
        }

        // Filtro Nro_OC  ✅ corregido
        if ($request->filled('filtro_nro_oc')) {
            $ingresos_mp->where('mp_ingreso.Nro_OC', 'like', '%' . $request->filtro_nro_oc . '%');
        }

        // Filtro Proveedor
        if ($request->filled('filtro_proveedor')) {
            $ingresos_mp->whereRaw('LOWER(proveedores.Prov_Nombre) = ?', [strtolower($request->filtro_proveedor)]);
        }

        // Filtro Materia Prima
        if ($request->filled('filtro_materia_prima')) {
            $ingresos_mp->whereRaw('LOWER(mp_materia_prima.Nombre_Materia) = ?', [strtolower($request->filtro_materia_prima)]);
        }

        // Filtro Diámetro
        if ($request->filled('filtro_diametro')) {
            $ingresos_mp->whereRaw('LOWER(mp_diametro.Valor_Diametro) = ?', [strtolower($request->filtro_diametro)]);
        }

        // Filtro Código MP
        if ($request->filled('filtro_codigo_mp')) {
            $ingresos_mp->whereRaw('LOWER(mp_ingreso.Codigo_MP) = ?', [strtolower($request->filtro_codigo_mp)]);
        }

        // Filtro Certificado  ✅ ahora parcial
        if ($request->filled('filtro_certificado')) {
            $ingresos_mp->where('mp_ingreso.Nro_Certificado_MP', 'like', '%' . $request->filtro_certificado . '%');
        }

        // Filtro Detalle Origen  ✅ ahora parcial
        if ($request->filled('filtro_detalle_origen')) {
            $ingresos_mp->where('mp_ingreso.Detalle_Origen_MP', 'like', '%' . $request->filtro_detalle_origen . '%');
        }

        // Filtro Unidades
        if ($request->filled('filtro_unidades')) {
            $ingresos_mp->where('mp_ingreso.Unidades_MP', 'like', '%' . $request->filtro_unidades . '%');
        }

        // Filtro Longitud  ✅ parcial
        if ($request->filled('filtro_longitud')) {
            $ingresos_mp->where('mp_ingreso.Longitud_Unidad_MP', 'like', '%' . $request->filtro_longitud . '%');
        }

        // Filtro Mts Totales  ✅ parcial
        if ($request->filled('filtro_mts_totales')) {
            $ingresos_mp->where('mp_ingreso.Mts_Totales', 'like', '%' . $request->filtro_mts_totales . '%');
        }

        // Filtro Kilos Totales  ✅ parcial
        if ($request->filled('filtro_kilos_totales')) {
            $ingresos_mp->where('mp_ingreso.Kilos_Totales', 'like', '%' . $request->filtro_kilos_totales . '%');
        }

        return datatables()->of($ingresos_mp)
            ->addColumn('Proveedor', function ($mpIngreso) {
                return $mpIngreso->proveedor ? $mpIngreso->proveedor->Prov_Nombre : 'No Asignado';
            })
            ->make(true);

    } catch (\Exception $e) {
        Log::error('Error en getData: ' . $e->getMessage());

        return response()->json([
            'error' => 'Error al recuperar los datos.'
        ], 500);
    }
}


public function resumenIngresos()
{
    $total = MpIngreso::withTrashed()->count();
    $activos = MpIngreso::count();
    $eliminados = MpIngreso::onlyTrashed()->count();

    return response()->json([
        'total' => $total,
        'activos' => $activos,
        'eliminados' => $eliminados
    ]);
}

    
    
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener los Ingresos_mp de la base de datos y paginarlos
        $ingresos_mp = MpIngreso::paginate(10); // Esto paginará los resultados, mostrando 10 Ingresos_mp de por página
        $totalIngresos = MpIngreso::count(); // Cuenta todas las materias primas
        // Pasar los Ingresos_mp paginados a la vista correspondiente
        return view('materia_prima.ingresos.index', compact('totalIngresos', 'ingresos_mp'));
    }

    // MpIngresoController.php

    public function show(MpIngreso $mp_ingreso)
    {
        return view('materia_prima.ingresos.show', compact('mp_ingreso'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    $proveedores = Proveedor::where('Es_Proveedor_MP', 1)
        ->where('reg_Status', 1)
        ->orderBy('Prov_Nombre', 'asc')
        ->get();

    $materiasPrimas = MpMateriaPrima::where('reg_Status', 1)
        ->orderBy('Nombre_Materia', 'asc')
        ->get();

    $diametros = MpDiametro::where('reg_Status', 1)
        ->orderBy('Valor_Diametro', 'asc')
        ->get();

    $ultimoIngreso = MpIngreso::orderBy('Nro_Ingreso_MP', 'desc')->first();
    $proximoNroIngreso = $ultimoIngreso ? ((int) $ultimoIngreso->Nro_Ingreso_MP + 1) : 1;

    return view('materia_prima.ingresos.create', compact(
        'proveedores',
        'materiasPrimas',
        'diametros',
        'ultimoIngreso',
        'proximoNroIngreso'
    ));
}


   public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            // Cabecera compartida
            'Nro_Pedido' => 'required|string|max:255',
            'Nro_Remito' => 'required|string|max:255',
            'Fecha_Ingreso' => 'required|date',
            'Nro_OC' => 'nullable|string|max:255',
            'Id_Proveedor' => 'required|exists:proveedores,Prov_Id',

            // Detalle por fila
            'Nro_Ingreso_MP' => 'required|array|min:1',
            'Nro_Ingreso_MP.*' => [
            'required',
            'integer',
            'distinct',
            Rule::unique('mp_ingreso', 'Nro_Ingreso_MP')->whereNull('deleted_at'),
        ],

            'Id_Materia_Prima' => 'required|array|min:1',
            'Id_Materia_Prima.*' => 'required|exists:mp_materia_prima,Id_Materia_Prima',

            'Id_Diametro_MP' => 'required|array|min:1',
            'Id_Diametro_MP.*' => 'required|exists:mp_diametro,Id_Diametro',

            'Codigo_MP' => 'required|array|min:1',
            'Codigo_MP.*' => 'required|string|max:255',

            'Nro_Certificado_MP' => 'nullable|array',
            'Nro_Certificado_MP.*' => 'nullable|string|max:255',

            'Detalle_Origen_MP' => 'nullable|array',
            'Detalle_Origen_MP.*' => 'nullable|string|max:255',

            'Unidades_MP' => 'required|array|min:1',
            'Unidades_MP.*' => 'required|integer|min:1',

            'Longitud_Unidad_MP' => 'required|array|min:1',
            'Longitud_Unidad_MP.*' => 'required|numeric|min:0',

            'Mts_Totales' => 'required|array|min:1',
            'Mts_Totales.*' => 'required|numeric|min:0',

            'Kilos_Totales' => 'nullable|array',
            'Kilos_Totales.*' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        foreach ($validatedData['Nro_Ingreso_MP'] as $index => $nroIngreso) {
            MpIngreso::create([
                'Nro_Ingreso_MP'      => $validatedData['Nro_Ingreso_MP'][$index],
                'Nro_Pedido'          => $validatedData['Nro_Pedido'],
                'Nro_Remito'          => $validatedData['Nro_Remito'],
                'Fecha_Ingreso'       => $validatedData['Fecha_Ingreso'],
                'Nro_OC'              => $validatedData['Nro_OC'] ?? null,
                'Id_Proveedor'        => $validatedData['Id_Proveedor'],

                'Id_Materia_Prima'    => $validatedData['Id_Materia_Prima'][$index],
                'Id_Diametro_MP'      => $validatedData['Id_Diametro_MP'][$index],
                'Codigo_MP'           => $validatedData['Codigo_MP'][$index],
                'Nro_Certificado_MP'  => $validatedData['Nro_Certificado_MP'][$index] ?? null,
                'Detalle_Origen_MP'   => $validatedData['Detalle_Origen_MP'][$index] ?? '',
                'Unidades_MP'         => $validatedData['Unidades_MP'][$index],
                'Longitud_Unidad_MP'  => $validatedData['Longitud_Unidad_MP'][$index],
                'Mts_Totales'         => $validatedData['Mts_Totales'][$index],
                'Kilos_Totales'       => $validatedData['Kilos_Totales'][$index] ?? null,

                'created_by'          => Auth::id(),
                'reg_Status'          => 1,
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Ingresos de materia prima creados exitosamente.',
            'redirect' => route('mp_ingresos.index'),
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Error al crear ingresos de MP', [
            'error' => $e->getMessage(),
            'usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al crear los ingresos de materia prima: ' . $e->getMessage()
        ], 400);
    }
}
    

/*

Este si funciona 09/11/2024:


    public function store(Request $request)
    {
        try {
            \DB::beginTransaction();
            
            $data = $request->all(); // Captura todos los datos directamente
            $data['created_by'] = Auth::id();
            
            MpIngreso::create($data); // Inserta los datos
            
            \DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Ingreso de materia prima creado exitosamente.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al crear el ingreso de materia prima:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al crear el ingreso de materia prima: ' . $e->getMessage()], 400);
        }
    }

*/

    public function edit($Id_MP)
    {
        $ingreso = MpIngreso::findOrFail($Id_MP);
        $proveedores = Proveedor::where('Es_Proveedor_MP', 1)->where('reg_Status', 1)->get();
        $materiasPrimas = MpMateriaPrima::where('reg_Status', 1)->get();
        $diametros = MpDiametro::where('reg_Status', 1)->get();

        return view('materia_prima.ingresos.edit', compact('ingreso', 'proveedores', 'materiasPrimas', 'diametros'));
    }

    public function update(Request $request, $id)
{
    $ingreso = MpIngreso::findOrFail($id);

    Log::info('Intentando actualizar MP', [
        'id' => $id,
        'usuario' => Auth::id(),
        'request_data' => $request->all()
    ]);

    $validatedData = $request->validate([
        'Nro_Ingreso_MP' => [
            'required',
            'integer',
            Rule::unique('mp_ingreso', 'Nro_Ingreso_MP')
                ->ignore($id, 'Id_MP')
                ->whereNull('deleted_at'),
        ],
        'Nro_Pedido' => 'required|string|max:255',
        'Nro_Remito' => 'required|string|max:255',
        'Fecha_Ingreso' => 'required|date',
        'Nro_OC' => 'required|string|max:255',
        'Id_Proveedor' => 'required|exists:proveedores,Prov_Id',
        'Id_Materia_Prima' => 'required|exists:mp_materia_prima,Id_Materia_Prima',
        'Id_Diametro_MP' => 'required|exists:mp_diametro,Id_Diametro',
        'Codigo_MP' => 'required|string|max:255',
        'Unidades_MP' => 'required|integer',
        'Longitud_Unidad_MP' => 'required|numeric',
        'Mts_Totales' => 'required|numeric',
        'Kilos_Totales' => 'required|numeric',
        'Nro_Certificado_MP' => 'sometimes|string|max:255',
        'Detalle_Origen_MP' => 'nullable|string|max:255',
        'reg_Status' => 'required|in:0,1',
    ]);

    Log::info('Datos validados correctamente para actualización:', $validatedData);

    // Normalización específica de datos para evitar errores en isDirty()
    $validatedData['Detalle_Origen_MP'] = $validatedData['Detalle_Origen_MP'] ?? '';
    $validatedData['Nro_Certificado_MP'] = $validatedData['Nro_Certificado_MP'] ?? '';
    $validatedData['Codigo_MP'] = trim($validatedData['Codigo_MP']);
    $validatedData['Kilos_Totales'] = number_format((float)$validatedData['Kilos_Totales'], 2, '.', '');
    $validatedData['Mts_Totales'] = number_format((float)$validatedData['Mts_Totales'], 2, '.', '');
    $validatedData['Longitud_Unidad_MP'] = number_format((float)$validatedData['Longitud_Unidad_MP'], 2, '.', '');
    $validatedData['Unidades_MP'] = (int) $validatedData['Unidades_MP'];
    $validatedData['Id_Proveedor'] = (int) $validatedData['Id_Proveedor'];
    $validatedData['Id_Materia_Prima'] = (int) $validatedData['Id_Materia_Prima'];
    $validatedData['Id_Diametro_MP'] = (int) $validatedData['Id_Diametro_MP'];
    $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];

    return $this->updateIfChanged($ingreso, $validatedData, [
        'success_redirect' => route('mp_ingresos.index'),
        'success_message' => 'Ingreso de materia prima actualizado correctamente.',
        'no_changes_message' => 'No se realizaron cambios.',
        'set_updated_by' => true,
        'use_transaction' => true,
        'normalize_data' => false, // Ya normalizado arriba
    ]);
}

public function destroy($id)
{
    try {
        $ingreso = MpIngreso::findOrFail($id);

        // Ejemplo: verificar si este ingreso ya fue usado en egresos o consumos
        $usadoEnMovimientos = false;

        // Reemplazar esta lógica por tu relación real
        // $usadoEnMovimientos = $ingreso->egresos()->exists();

        if ($usadoEnMovimientos) {
            return response()->json([
                'success' => false,
                'message' => 'Este ingreso de materia prima ya fue utilizado y no puede eliminarse.'
            ], 400);
        }

        $ingreso->deleted_by = Auth::id();
        $ingreso->save();
        $ingreso->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ingreso de materia prima eliminado correctamente.'
        ]);
    } catch (\Exception $e) {
        Log::error('Error al eliminar ingreso de MP', [
            'id' => $id,
            'error' => $e->getMessage(),
            'usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'No se pudo eliminar el ingreso de materia prima.'
        ], 400);
    }
}


public function restore($id)
{
    try {
        // Restaurar un ingreso de materia prima eliminado
        $ingreso = MpIngreso::withTrashed()->findOrFail($id);
        $ingreso->restore();

        return redirect()->route('mp_ingresos.index')->with('success', 'Ingreso de materia prima restaurado con éxito');
    } catch (\Exception $e) {
        Log::error('Error al restaurar el ingreso de materia prima:', ['error' => $e->getMessage()]);
        return redirect()->route('mp_ingresos.index')->with('error', 'Error al restaurar el ingreso de materia prima');
    }
}

public function showDeleted()
{
    // Recupera solo los ingresos de materia prima eliminados
    $ingresosEliminados = MpIngreso::onlyTrashed()->get();

    return view('materia_prima.ingresos.deleted', ['ingresosEliminados' => $ingresosEliminados]);
}



}