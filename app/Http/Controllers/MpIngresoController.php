<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MpIngreso;
use App\Models\Proveedor;  // Asegúrate de importar el modelo Proveedor correctamente
use App\Models\MpDiametro;
use App\Models\MpMateriaPrima;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; // al inicio del controlador




class MpIngresoController extends Controller


{

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
        // Ajustar la consulta para unirse con las tablas relacionadas
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
                'mp_materia_prima.Nombre_Materia AS Materia_Prima',  // Usar el alias correcto
                'mp_diametro.Valor_Diametro AS Diametro_MP',         // Usar el alias correcto
                'mp_ingreso.Codigo_MP',
                'mp_ingreso.Nro_Certificado_MP',
                'mp_ingreso.Detalle_Origen_MP',
                'mp_ingreso.Unidades_MP',
                'mp_ingreso.Longitud_Unidad_MP',
                'mp_ingreso.Mts_Totales',
                'mp_ingreso.Kilos_Totales',
                'mp_ingreso.created_at AS mp_ingreso_created_at',
                'mp_ingreso.updated_at AS mp_ingreso_updated_at'
            )->orderBy('Nro_Ingreso_MP', 'desc'); // Cambiar a descendente

        // Aplicar los filtros basados en los campos del request
        if ($request->filled('filtro_Nro_OC')) {
            $ingresos_mp->where('mp_ingreso.Nro_OC', '=', $request->filtro_Nro_OC);
        }

        if ($request->filled('filtro_proveedor')) {
            $ingresos_mp->whereRaw('LOWER(proveedores.Prov_Nombre) = ?', [strtolower($request->filtro_proveedor)]);
        }

        if ($request->filled('filtro_materia_prima')) {
            $ingresos_mp->whereRaw('LOWER(mp_materia_prima.Nombre_Materia) = ?', [strtolower($request->filtro_materia_prima)]);
        }

        if ($request->filled('filtro_diametro')) {
            $ingresos_mp->whereRaw('LOWER(mp_diametro.Valor_Diametro) = ?', [strtolower($request->filtro_diametro)]);
        }

        if ($request->filled('filtro_codigo_mp')) {
            $ingresos_mp->whereRaw('LOWER(mp_ingreso.Codigo_MP) = ?', [strtolower($request->filtro_codigo_mp)]);
        }

        if ($request->filled('filtro_nro_ingreso')) {
            $ingresos_mp->where('mp_ingreso.Nro_Ingreso_MP', '=', $request->filtro_nro_ingreso);
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
        Log::error('Error en getData: ' . $e->getMessage());
        return response()->json(['error' => 'Error al recuperar los datos.'], 500);
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
        // Obtener los datos de las tablas relacionadas
        $proveedores = Proveedor::where('Es_Proveedor_MP', 1)->where('reg_Status', 1)->get();
        $materiasPrimas = MpMateriaPrima::where('reg_Status', 1)->get();
        $diametros = MpDiametro::where('reg_Status', 1)->get();
    
        // Tipos de proveedores
        $tiposProveedores = [
            'mp' => 'Materia Prima',
            'herramientas' => 'Herramientas de Corte'
        ];
    
        // Enviar los datos a la vista 'mp_ingresos.create'
        return view('materia_prima.ingresos.create', compact('proveedores', 'materiasPrimas', 'diametros', 'tiposProveedores'));
    }

    public function store(Request $request)
    {
        try {
            // Validación de los datos del formulario
            $validatedData = $request->validate([
                'Nro_Ingreso_MP' => 'required|integer',
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
            ]);
    
            DB::beginTransaction();
    
            // Asigna el usuario y el estado al registro antes de guardarlo
            $validatedData['created_by'] = Auth::id();
            $validatedData['reg_Status'] = 1;
            MpIngreso::create($validatedData);
    
            DB::commit();
    
            return response()->json(['success' => true, 'message' => 'Ingreso de materia prima creado exitosamente.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
         Log::error('Error al crear ingreso de MP', [
            'error' => $e->getMessage(),
            'usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al crear el ingreso de materia prima: ' . $e->getMessage()
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

    DB::beginTransaction();

    Log::info('Datos validados correctamente para actualización:', $validatedData);

// Normalización de datos para evitar errores en isDirty()
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

    $ingreso->fill($validatedData);

    if ($ingreso->isDirty()) {
        Log::info('Campos modificados detectados', [
            'campos_modificados' => $ingreso->getDirty()
        ]);

        $ingreso->updated_by = Auth::id();
        $ingreso->save();
        DB::commit();

       
        return redirect()->route('mp_ingresos.index')->with('success', 'Ingreso de materia prima actualizado correctamente.');
    } else {
        DB::rollBack();
        Log::warning('No se realizaron cambios en el ingreso de MP', [
            'id' => $id,
            'datos_validados' => $validatedData
        ]);

        if ($request->ajax()) {
    return response()->json(['success' => false, 'warning' => 'No se realizaron cambios.'], 200);
}

return back()->with('warning', 'No se realizaron cambios.');
    }
}

public function destroy($id)
{
    $materia = MpMateriaPrima::findOrFail($id);

    $usadaEnFabricacion = $materia->registrosFabricacion()->exists();

    if ($usadaEnFabricacion) {
        return back()->with('error', 'Esta materia prima ya fue usada en fabricación y no puede ser eliminada.');
    }

    $materia->delete();

    return back()->with('success', 'Materia prima eliminada correctamente.');
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