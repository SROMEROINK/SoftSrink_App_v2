<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\AppliesExactNumericFilters;
use App\Models\MpEgreso;
use App\Models\PedidoClienteMp;
use App\Services\HistoricalPedidoMpEgresoImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MpEgresoController extends Controller
{
    use AppliesExactNumericFilters;
    public function getData(Request $request)
    {
        $hasPlanningColumns = Schema::hasColumn('pedido_cliente_mp', 'Fecha_Planificacion')
            && Schema::hasColumn('pedido_cliente_mp', 'Responsable_Planificacion');

        $query = DB::table('pedido_cliente_mp as pm')
            ->join('pedido_cliente as p', 'p.Id_OF', '=', 'pm.Id_OF')
            ->join('productos as prod', 'prod.Id_Producto', '=', 'p.Producto_Id')
            ->leftJoin('mp_salidas as s', function ($join) {
                $join->on('s.Id_OF_Salidas_MP', '=', 'pm.Id_OF')
                    ->whereNull('s.deleted_at');
            })
            ->whereNull('pm.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('prod.deleted_at')
            ->select([
                'pm.Id_Pedido_MP',
                'pm.Id_OF',
                'p.Nro_OF',
                'prod.Prod_Codigo',
                'prod.Prod_Descripcion',
                'pm.Nro_Ingreso_MP',
                'pm.Codigo_MP',
                'pm.Nro_Maquina',
                'pm.Cant_Barras_MP as Cant_Barras_Requeridas',
                'pm.Longitud_Un_MP',
                's.Id_Egresos_MP',
                's.Cantidad_Unidades_MP',
                's.Cantidad_Unidades_MP_Preparadas',
                's.Cantidad_MP_Adicionales',
                's.Cant_Devoluciones',
                's.Total_Salidas_MP',
                's.Total_Mtros_Utilizados',
                DB::raw(($hasPlanningColumns ? 'pm.Fecha_Planificacion' : 's.Fecha_del_Pedido_Produccion') . ' as Fecha_del_Pedido_Produccion'),
                DB::raw(($hasPlanningColumns ? 'pm.Responsable_Planificacion' : 's.Responsable_Pedido_Produccion') . ' as Responsable_Pedido_Produccion'),
                DB::raw(($hasPlanningColumns ? 'pm.Pedido_Material_Nro' : 's.Nro_Pedido_MP') . ' as Nro_Pedido_MP'),
                's.Fecha_de_Entrega_Pedido_Calidad',
                's.Responsable_de_entrega_Calidad',
                's.created_at',
                's.updated_at',
                DB::raw("CASE WHEN s.Fecha_de_Entrega_Pedido_Calidad IS NULL THEN '' ELSE DATE_FORMAT(s.Fecha_de_Entrega_Pedido_Calidad, '%d/%m/%Y') END AS Alerta_Calidad"),
                DB::raw("CASE WHEN s.Id_Egresos_MP IS NULL THEN 'PENDIENTE SALIDA' ELSE 'SALIDA CARGADA' END AS Estado_Salida"),
            ]);

        if ($request->filled('filtro_of')) {
            $this->applySmartFilter($query, 'p.Nro_OF', $request->filtro_of);
        }

        if ($request->filled('filtro_producto')) {
            $query->where('prod.Prod_Codigo', 'like', '%' . $request->filtro_producto . '%');
        }

        if ($request->filled('filtro_ingreso_mp')) {
            $this->applySmartFilter($query, 'pm.Nro_Ingreso_MP', $request->filtro_ingreso_mp);
        }

        if ($request->filled('filtro_estado_salida')) {
            if ($request->filtro_estado_salida === 'PENDIENTE SALIDA') {
                $query->whereNull('s.Id_Egresos_MP');
            } elseif ($request->filtro_estado_salida === 'SALIDA CARGADA') {
                $query->whereNotNull('s.Id_Egresos_MP');
            }
        }

        return datatables()->of($query)
            ->editColumn('Fecha_del_Pedido_Produccion', fn ($row) => $row->Fecha_del_Pedido_Produccion ? date('d/m/Y', strtotime($row->Fecha_del_Pedido_Produccion)) : '')
            ->editColumn('Fecha_de_Entrega_Pedido_Calidad', fn ($row) => $row->Fecha_de_Entrega_Pedido_Calidad ? date('d/m/Y', strtotime($row->Fecha_de_Entrega_Pedido_Calidad)) : '')
            ->editColumn('Cantidad_Unidades_MP', fn ($row) => $row->Cantidad_Unidades_MP !== null ? number_format((int) $row->Cantidad_Unidades_MP, 0, ',', '.') : '')
            ->editColumn('Cantidad_Unidades_MP_Preparadas', fn ($row) => $row->Cantidad_Unidades_MP_Preparadas !== null ? number_format((int) $row->Cantidad_Unidades_MP_Preparadas, 0, ',', '.') : '')
            ->editColumn('Total_Salidas_MP', fn ($row) => $row->Total_Salidas_MP !== null ? number_format((int) $row->Total_Salidas_MP, 0, ',', '.') : '')
            ->editColumn('Total_Mtros_Utilizados', fn ($row) => $row->Total_Mtros_Utilizados !== null ? number_format((float) $row->Total_Mtros_Utilizados, 2, ',', '.') : '')
            ->addColumn('acciones', function ($row) {
                if (!$row->Id_Egresos_MP) {
                    return '<a href="' . route('mp_egresos.create', ['pedido_mp' => $row->Id_Pedido_MP]) . '" class="btn btn-success btn-sm">Registrar</a>';
                }

                return '
                    <div class="acciones-grupo">
                        <a href="' . route('mp_egresos.show', $row->Id_Egresos_MP) . '" class="btn btn-info btn-sm">Ver</a>
                        <a href="' . route('mp_egresos.edit', $row->Id_Egresos_MP) . '" class="btn btn-primary btn-sm">Editar</a>
                        <button type="button" class="btn btn-danger btn-sm btn-delete-egreso" data-id="' . $row->Id_Egresos_MP . '">Eliminar</button>
                    </div>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function index(HistoricalPedidoMpEgresoImportService $historicalImportService)
    {
        $totalSalidas = MpEgreso::count();
        $salidasActivas = MpEgreso::where('reg_Status', 1)->count();
        $salidasPendientes = PedidoClienteMp::whereNull('deleted_at')
            ->whereDoesntHave('salidaInicial')
            ->count();
        $csvHistoricoDisponible = $historicalImportService->csvExists();
        $historicoImportado = $historicalImportService->historicalImportAlreadyApplied();
        $puedeImportarHistorico = $csvHistoricoDisponible && !$historicoImportado;

        return view('materia_prima.egresos.index', compact('totalSalidas', 'salidasActivas', 'salidasPendientes', 'csvHistoricoDisponible', 'historicoImportado', 'puedeImportarHistorico'));
    }

    public function create(Request $request)
    {
        $pedidosMp = $this->getPendientesQuery()->get();
        $selectedPedidoMpId = $request->integer('pedido_mp');

        return view('materia_prima.egresos.create', compact('pedidosMp', 'selectedPedidoMpId'));
    }

    public function createMassive()
    {
        return view('materia_prima.egresos.mass-create', $this->massiveCreateData());
    }

    public function importHistoricCsv(HistoricalPedidoMpEgresoImportService $historicalImportService)
    {
        if ($historicalImportService->historicalImportAlreadyApplied()) {
            return redirect()->route('mp_egresos.index')->with('warning', 'La importacion historica ya fue ejecutada y quedo bloqueada para evitar duplicados.');
        }

        try {
            DB::beginTransaction();
            $summary = $historicalImportService->importFromDefaultPath(Auth::id());
            DB::commit();

            $message = sprintf(
                'Importacion historica completada. Pedido MP: %d creados, %d actualizados. Egresos: %d creados, %d actualizados. Filas omitidas: %d.',
                $summary['created_pedido_mp'],
                $summary['updated_pedido_mp'],
                $summary['created_egreso'],
                $summary['updated_egreso'],
                $summary['omitted_incomplete'] + $summary['omitted_missing_of']
            );

            return redirect()->route('mp_egresos.index')->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al importar CSV historico de pedidos y egresos MP', ['error' => $e->getMessage()]);

            return redirect()->route('mp_egresos.index')->with('error', 'No se pudo importar el CSV historico de pedidos y egresos MP.');
        }
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        DB::beginTransaction();
        try {
            $pedidoMp = $this->findPedidoMpOrFail((int) $validated['Id_Pedido_MP']);

            if (MpEgreso::where('Id_OF_Salidas_MP', $pedidoMp->Id_OF)->whereNull('deleted_at')->exists()) {
                return $this->responseError($request, 'La OF seleccionada ya tiene una salida de materia prima registrada.', 422);
            }

            $data = $this->buildPayload($validated, $pedidoMp);
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            MpEgreso::create($data);

            DB::commit();

            return $this->responseSuccess($request, 'Salida de materia prima creada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear mp_egreso', ['error' => $e->getMessage()]);

            return $this->responseError($request, 'No se pudo crear la salida de materia prima.', 400);
        }
    }

    public function storeMassive(Request $request)
    {
        $rows = $this->buildMassiveRows($request);

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'Debes completar al menos una fila valida para guardar la carga masiva de egresos.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                MpEgreso::create($row);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Egresos masivos de materia prima creados correctamente.',
                'redirect' => route('mp_egresos.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear mp_egresos masivo', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar la carga masiva de egresos.',
            ], 400);
        }
    }

    public function show(MpEgreso $mp_egreso)
    {
        $mp_egreso->load(['pedidoMp.pedido.producto', 'pedidoMp.estadoPlanificacion']);

        return view('materia_prima.egresos.show', compact('mp_egreso'));
    }

    public function edit($id)
    {
        $egreso = MpEgreso::with(['pedidoMp.pedido.producto', 'pedidoMp.estadoPlanificacion'])->findOrFail($id);
        $pedidosMp = $this->getPendientesQuery(optional($egreso->pedidoMp)->Id_Pedido_MP)->get();
        $selectedPedidoMpId = optional($egreso->pedidoMp)->Id_Pedido_MP;

        return view('materia_prima.egresos.edit', compact('egreso', 'pedidosMp', 'selectedPedidoMpId'));
    }

    public function update(Request $request, $id)
    {
        $egreso = MpEgreso::findOrFail($id);
        $validated = $this->validateData($request);
        $pedidoMp = $this->findPedidoMpOrFail((int) $validated['Id_Pedido_MP']);

        if (MpEgreso::where('Id_OF_Salidas_MP', $pedidoMp->Id_OF)
            ->where('Id_Egresos_MP', '!=', $egreso->Id_Egresos_MP)
            ->whereNull('deleted_at')
            ->exists()) {
            return $this->responseError($request, 'La OF seleccionada ya tiene otra salida de materia prima registrada.', 422);
        }

        $data = $this->buildPayload($validated, $pedidoMp);
        $egreso->fill($data);

        if (!$egreso->isDirty()) {
            return $this->responseNoChanges($request, 'No se realizaron cambios.');
        }

        $egreso->updated_by = Auth::id();
        $egreso->save();

        return $this->responseSuccess($request, 'Salida de materia prima actualizada correctamente.');
    }

    public function destroy($id)
    {
        try {
            $egreso = MpEgreso::findOrFail($id);
            $egreso->deleted_by = Auth::id();
            $egreso->save();
            $egreso->delete();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar mp_egreso', ['error' => $e->getMessage()]);
            return response()->json(['success' => false], 400);
        }
    }

    public function showDeleted()
    {
        $egresosEliminados = MpEgreso::onlyTrashed()
            ->with(['pedidoMp.pedido.producto'])
            ->orderByDesc('deleted_at')
            ->get();

        return view('materia_prima.egresos.deleted', compact('egresosEliminados'));
    }

    public function restore($id)
    {
        try {
            $egreso = MpEgreso::withTrashed()->findOrFail($id);
            $egreso->restore();

            return redirect()->route('mp_egresos.index')->with('success', 'Salida de materia prima restaurada correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al restaurar mp_egreso', ['error' => $e->getMessage()]);
            return redirect()->route('mp_egresos.deleted')->with('error', 'No se pudo restaurar la salida de materia prima.');
        }
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'Id_Pedido_MP' => 'required|integer|exists:pedido_cliente_mp,Id_Pedido_MP',
            'Cantidad_Unidades_MP' => 'required|integer|min:0',
            'Cantidad_Unidades_MP_Preparadas' => 'required|integer|min:0',
            'Cantidad_MP_Adicionales' => 'nullable|integer|min:0',
            'Cant_Devoluciones' => 'nullable|integer|min:0',
            'Fecha_del_Pedido_Produccion' => 'nullable|date',
            'Responsable_Pedido_Produccion' => 'nullable|string|max:255',
            'Nro_Pedido_MP' => 'nullable|integer|min:0',
            'Fecha_de_Entrega_Pedido_Calidad' => 'nullable|date',
            'Responsable_de_entrega_Calidad' => 'nullable|string|max:255',
            'reg_Status' => 'required|in:0,1',
        ]);
    }

    protected function buildPayload(array $validated, PedidoClienteMp $pedidoMp): array
    {
        $cantidadSolicitada = (int) ($validated['Cantidad_Unidades_MP'] ?? 0);
        $cantidadPreparada = (int) ($validated['Cantidad_Unidades_MP_Preparadas'] ?? 0);
        $adicionales = (int) ($validated['Cantidad_MP_Adicionales'] ?? 0);
        $devoluciones = (int) ($validated['Cant_Devoluciones'] ?? 0);
        $totalSalidas = max($cantidadPreparada + $adicionales - $devoluciones, 0);
        $longitudUnidad = (float) ($pedidoMp->Longitud_Un_MP ?? 0);
        $totalMetros = $totalSalidas * $longitudUnidad;

        $fechaPlanificacion = $validated['Fecha_del_Pedido_Produccion'] ?? optional($pedidoMp->Fecha_Planificacion)->format('Y-m-d');
        $responsablePlanificacion = blank($validated['Responsable_Pedido_Produccion'] ?? null)
            ? $pedidoMp->Responsable_Planificacion
            : $validated['Responsable_Pedido_Produccion'];
        $pedidoMaterialNro = blank($validated['Nro_Pedido_MP'] ?? null)
            ? (is_numeric($pedidoMp->Pedido_Material_Nro ?? null) ? (int) $pedidoMp->Pedido_Material_Nro : null)
            : (int) $validated['Nro_Pedido_MP'];

        return [
            'Id_OF_Salidas_MP' => $pedidoMp->Id_OF,
            'Cantidad_Unidades_MP' => $cantidadSolicitada,
            'Cantidad_Unidades_MP_Preparadas' => $cantidadPreparada,
            'Cantidad_MP_Adicionales' => $adicionales,
            'Cant_Devoluciones' => $devoluciones,
            'Total_Salidas_MP' => $totalSalidas,
            'Total_Mtros_Utilizados' => round($totalMetros, 2),
            'Fecha_del_Pedido_Produccion' => $fechaPlanificacion,
            'Responsable_Pedido_Produccion' => blank($responsablePlanificacion) ? null : $responsablePlanificacion,
            'Nro_Pedido_MP' => $pedidoMaterialNro,
            'Fecha_de_Entrega_Pedido_Calidad' => $validated['Fecha_de_Entrega_Pedido_Calidad'] ?? null,
            'Responsable_de_entrega_Calidad' => blank($validated['Responsable_de_entrega_Calidad'] ?? null) ? null : $validated['Responsable_de_entrega_Calidad'],
            'reg_Status' => (int) ($validated['reg_Status'] ?? 1),
        ];
    }

    protected function findPedidoMpOrFail(int $id): PedidoClienteMp
    {
        return PedidoClienteMp::with(['pedido.producto', 'estadoPlanificacion'])
            ->where('Id_Pedido_MP', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    protected function getPendientesQuery(?int $includePedidoMpId = null)
    {
        return PedidoClienteMp::query()
            ->with(['pedido.producto', 'estadoPlanificacion'])
            ->whereNull('deleted_at')
            ->when($includePedidoMpId, function ($query) use ($includePedidoMpId) {
                $query->where(function ($subQuery) use ($includePedidoMpId) {
                    $subQuery->where('Id_Pedido_MP', $includePedidoMpId)
                        ->orWhereDoesntHave('salidaInicial');
                });
            }, function ($query) {
                $query->whereDoesntHave('salidaInicial');
            })
            ->orderBy('Id_OF');
    }

    protected function massiveCreateData(): array
    {
        $pendientes = $this->getPendientesQuery()->get();

        $pedidosCatalogo = $pendientes->map(function ($pedidoMp) {
            return [
                'Id_Pedido_MP' => (int) $pedidoMp->Id_Pedido_MP,
                'Id_OF' => (int) $pedidoMp->Id_OF,
                'Nro_OF' => (int) optional($pedidoMp->pedido)->Nro_OF,
                'Prod_Codigo' => optional(optional($pedidoMp->pedido)->producto)->Prod_Codigo ?? '',
                'Nro_Ingreso_MP' => $pedidoMp->Nro_Ingreso_MP,
                'Codigo_MP' => $pedidoMp->Codigo_MP,
                'Nro_Maquina' => $pedidoMp->Nro_Maquina,
                'Cant_Barras_MP' => (int) ($pedidoMp->Cant_Barras_MP ?? 0),
                'Longitud_Un_MP' => (float) ($pedidoMp->Longitud_Un_MP ?? 0),
            ];
        })->values();

        return [
            'pendingCount' => $pendientes->count(),
            'pendingMinNroOf' => optional($pendientes->sortBy(fn ($item) => optional($item->pedido)->Nro_OF)->first()->pedido)->Nro_OF,
            'pendingMaxNroOf' => optional($pendientes->sortByDesc(fn ($item) => optional($item->pedido)->Nro_OF)->first()->pedido)->Nro_OF,
            'pedidosCatalogo' => $pedidosCatalogo,
        ];
    }

    protected function buildMassiveRows(Request $request): array
    {
        $pedidoMpIds = $request->input('Id_Pedido_MP', []);
        $fechasEntrega = $request->input('Fecha_de_Entrega_Pedido_Calidad', []);
        $cantidadesPreparadas = $request->input('Cantidad_Unidades_MP_Preparadas', []);
        $regStatuses = $request->input('reg_Status', []);

        $maxRows = max(count($pedidoMpIds), count($fechasEntrega), count($cantidadesPreparadas));
        $preparedRows = [];
        $invalidRows = [];
        $duplicatedRows = [];
        $seenPedidoMp = [];

        for ($index = 0; $index < $maxRows; $index++) {
            $rowNumber = $index + 1;
            $pedidoMpId = $pedidoMpIds[$index] ?? null;
            $fechaEntrega = $fechasEntrega[$index] ?? null;
            $cantidadPreparada = $cantidadesPreparadas[$index] ?? null;
            $regStatus = $regStatuses[$index] ?? 1;

            $isEmptyRow = blank($pedidoMpId) && blank($fechaEntrega) && blank($cantidadPreparada);
            if ($isEmptyRow) {
                continue;
            }

            if (blank($pedidoMpId) || blank($fechaEntrega) || blank($cantidadPreparada)) {
                $invalidRows[] = $rowNumber;
                continue;
            }

            if (isset($seenPedidoMp[$pedidoMpId])) {
                $duplicatedRows[] = $rowNumber;
                $duplicatedRows[] = $seenPedidoMp[$pedidoMpId];
                continue;
            }
            $seenPedidoMp[$pedidoMpId] = $rowNumber;

            $pedidoMp = PedidoClienteMp::whereNull('deleted_at')->find($pedidoMpId);
            if (!$pedidoMp) {
                $invalidRows[] = $rowNumber;
                continue;
            }

            if (MpEgreso::where('Id_OF_Salidas_MP', $pedidoMp->Id_OF)->whereNull('deleted_at')->exists()) {
                $duplicatedRows[] = $rowNumber;
                continue;
            }

            $cantidadPreparada = (int) $cantidadPreparada;
            if ($cantidadPreparada < 0) {
                $invalidRows[] = $rowNumber;
                continue;
            }

            $preparedRows[] = array_merge(
                $this->buildPayload([
                    'Cantidad_Unidades_MP' => (int) ($pedidoMp->Cant_Barras_MP ?? 0),
                    'Cantidad_Unidades_MP_Preparadas' => $cantidadPreparada,
                    'Cantidad_MP_Adicionales' => 0,
                    'Cant_Devoluciones' => 0,
                    'Fecha_de_Entrega_Pedido_Calidad' => $fechaEntrega,
                    'Responsable_de_entrega_Calidad' => Auth::user()->name ?? null,
                    'reg_Status' => (int) $regStatus,
                ], $pedidoMp),
                [
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );
        }

        $duplicatedRows = array_values(array_unique($duplicatedRows));
        $invalidRows = array_values(array_unique($invalidRows));

        if (!empty($duplicatedRows)) {
            abort(response()->json([
                'success' => false,
                'message' => 'Hay OF duplicadas en la carga masiva o ya tienen egreso cargado en la base.',
                'duplicatedRows' => $duplicatedRows,
            ], 400));
        }

        if (!empty($invalidRows)) {
            abort(response()->json([
                'success' => false,
                'message' => 'Hay filas invalidas. Revisa OF, fecha de entrega y cantidad de barras entregadas.',
                'invalidRows' => $invalidRows,
            ], 400));
        }

        return $preparedRows;
    }

    protected function responseSuccess(Request $request, string $message)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('mp_egresos.index'),
            ]);
        }

        return redirect()->route('mp_egresos.index')->with('success', $message);
    }

    protected function responseNoChanges(Request $request, string $message)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'type' => 'no_changes',
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('warning', $message);
    }

    protected function responseError(Request $request, string $message, int $status)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }
}
