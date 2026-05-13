<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\AppliesExactNumericFilters;
use App\Models\MpIngreso;
use App\Models\MpSalidaInicial;
use App\Services\HistoricalMpSalidaInicialImportService;
use App\Services\PedidoClienteMpPlannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpSalidaInicialController extends Controller
{
    use AppliesExactNumericFilters;
    protected string $historicalCutoffDate = '2026-03-29';

    public function index()
    {
        $totalAjustes = MpSalidaInicial::count();
        $ajustesActivos = MpSalidaInicial::where('reg_Status', 1)->count();
        $ajustesPendientes = MpIngreso::whereNull('deleted_at')
            ->whereDoesntHave('salidaInicial')
            ->count();
        $ultimoIngresoPendiente = MpIngreso::whereNull('deleted_at')
            ->whereDoesntHave('salidaInicial')
            ->orderBy('Nro_Ingreso_MP')
            ->value('Nro_Ingreso_MP');

        return view('materia_prima.salidas_iniciales.index', compact(
            'totalAjustes',
            'ajustesActivos',
            'ajustesPendientes',
            'ultimoIngresoPendiente'
        ));
    }

    public function importHistoricCsv(HistoricalMpSalidaInicialImportService $historicalImportService)
    {
        try {
            DB::beginTransaction();
            $summary = $historicalImportService->replaceFromDefaultPath(Auth::id());
            DB::commit();

            $message = sprintf(
                'Recarga historica completada. Filas CSV procesadas: %d. Ajustes recargados: %d. Ingresos omitidos: %d.',
                $summary['processed'],
                $summary['reloaded'],
                $summary['omitted_missing_ingreso']
            );

            return redirect()->route('mp_salidas_iniciales.index')->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al importar CSV historico de salidas iniciales MP', ['error' => $e->getMessage()]);

            return redirect()->route('mp_salidas_iniciales.index')->with('error', 'No se pudo recargar el CSV historico de salidas iniciales.');
        }
    }

    public function getData(Request $request)
    {
        $query = DB::table('mp_ingreso as i')
            ->leftJoin('mp_salidas_iniciales as si', function ($join) {
                $join->on('si.Id_Ingreso_MP', '=', 'i.Id_MP')
                    ->whereNull('si.deleted_at');
            })
            ->whereNull('i.deleted_at')
            ->select([
                'i.Id_MP',
                'i.Nro_Ingreso_MP',
                'i.Unidades_MP as Cant_Unid',
                'i.Longitud_Unidad_MP',
                'si.Id_Ingreso_MP as Ajuste_Id',
                DB::raw('COALESCE(si.Stock_Inicial, i.Unidades_MP, 0) as Stock_Inicial'),
                DB::raw('COALESCE(si.Devoluciones_Proveedor, 0) as Devoluciones_Proveedor'),
                DB::raw('COALESCE(si.Ajuste_Stock, 0) as Ajuste_Stock'),
                DB::raw('COALESCE(si.Total_Salidas_MP, COALESCE(si.Stock_Inicial, i.Unidades_MP, 0) - COALESCE(si.Devoluciones_Proveedor, 0) + COALESCE(si.Ajuste_Stock, 0)) as Total_Salidas_MP'),
                DB::raw('COALESCE(si.Total_mm_Utilizados, 0) as Total_mm_Utilizados'),
                'si.reg_Status',
                DB::raw("CASE WHEN si.Id_Ingreso_MP IS NULL THEN 'PENDIENTE AJUSTE' ELSE 'AJUSTE CARGADO' END AS Estado_Ajuste"),
            ])
            ->orderBy('i.Nro_Ingreso_MP', 'asc');

        if ($request->filled('filtro_ingreso')) {
            $this->applySmartFilter($query, 'i.Nro_Ingreso_MP', $request->filtro_ingreso);
        }

        if ($request->filled('filtro_estado')) {
            if ($request->filtro_estado === 'PENDIENTE AJUSTE') {
                $query->whereNull('si.Id_Ingreso_MP');
            } elseif ($request->filtro_estado === 'AJUSTE CARGADO') {
                $query->whereNotNull('si.Id_Ingreso_MP');
            }
        }

        return datatables()->of($query)
            ->editColumn('Cant_Unid', fn ($row) => $this->formatInt($row->Cant_Unid))
            ->editColumn('Longitud_Unidad_MP', fn ($row) => $this->formatDecimal($row->Longitud_Unidad_MP))
            ->editColumn('Stock_Inicial', fn ($row) => $this->formatInt($row->Stock_Inicial))
            ->editColumn('Devoluciones_Proveedor', fn ($row) => $this->formatInt($row->Devoluciones_Proveedor))
            ->editColumn('Ajuste_Stock', fn ($row) => $this->formatSignedInt($row->Ajuste_Stock))
            ->editColumn('Total_Salidas_MP', fn ($row) => $this->formatInt($row->Total_Salidas_MP))
            ->editColumn('Total_mm_Utilizados', fn ($row) => $this->formatDecimal($row->Total_mm_Utilizados))
            ->addColumn('acciones', function ($row) {
                if (!$row->Ajuste_Id) {
                    return '<a href="' . route('mp_salidas_iniciales.create', ['ingreso_mp' => $row->Id_MP]) . '" class="btn btn-success btn-sm">Registrar</a>';
                }

                return '
                    <div class="acciones-grupo">
                        <a href="' . route('mp_salidas_iniciales.show', $row->Ajuste_Id) . '" class="btn btn-info btn-sm">Ver</a>
                        <a href="' . route('mp_salidas_iniciales.edit', $row->Ajuste_Id) . '" class="btn btn-primary btn-sm">Editar</a>
                        <button type="button" class="btn btn-danger btn-sm btn-delete-salida-inicial" data-id="' . $row->Ajuste_Id . '">Eliminar</button>
                    </div>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $ingresos = $this->getPendientesQuery()->get();
        $selectedIngresoId = $request->integer('ingreso_mp');

        return view('materia_prima.salidas_iniciales.create', compact('ingresos', 'selectedIngresoId'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        DB::beginTransaction();
        try {
            $ingreso = $this->findIngresoOrFail($validated['Id_Ingreso_MP']);

            if (MpSalidaInicial::where('Id_Ingreso_MP', $ingreso->Id_MP)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ese ingreso ya tiene un ajuste de salida inicial registrado.',
                ], 422);
            }

            $data = $this->buildPayload($validated, $ingreso);
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            MpSalidaInicial::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salida inicial de materia prima creada correctamente.',
                'redirect' => route('mp_salidas_iniciales.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear mp_salidas_iniciales', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo crear la salida inicial de materia prima.',
            ], 400);
        }
    }

    public function show($id)
    {
        $salidaInicial = MpSalidaInicial::with(['ingresoMp.proveedor', 'ingresoMp.materiaPrima', 'ingresoMp.diametro'])->findOrFail($id);
        $this->hydrateCalculatedFields($salidaInicial);

        return view('materia_prima.salidas_iniciales.show', compact('salidaInicial'));
    }

    public function edit($id)
    {
        return redirect()->route('mp_salidas_iniciales.editMassive', ['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $salidaInicial = MpSalidaInicial::findOrFail($id);
        $validated = $this->validateData($request);
        $ingreso = $this->findIngresoOrFail($validated['Id_Ingreso_MP']);

        $data = $this->buildPayload($validated, $ingreso);
        $data['updated_by'] = Auth::id();

        $salidaInicial->fill($data);

        $returnTo = $request->input('return_to', 'salidas_iniciales');
        $returnSelected = $request->input('return_selected');

        if (!$salidaInicial->isDirty()) {
            return redirect()->to($this->resolveReturnUrl($returnTo, $returnSelected))->with('warning', 'No se realizaron cambios.');
        }

        $salidaInicial->save();

        return redirect()->to($this->resolveReturnUrl($returnTo, $returnSelected))->with('success', 'Salida inicial actualizada correctamente.');
    }

    public function destroy($id)
    {
        try {
            $salidaInicial = MpSalidaInicial::findOrFail($id);
            $salidaInicial->deleted_by = Auth::id();
            $salidaInicial->save();
            $salidaInicial->delete();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar mp_salidas_iniciales', ['error' => $e->getMessage()]);
            return response()->json(['success' => false], 400);
        }
    }

    public function showDeleted()
    {
        $salidasEliminadas = MpSalidaInicial::onlyTrashed()
            ->with(['ingresoMp.proveedor', 'ingresoMp.materiaPrima', 'ingresoMp.diametro'])
            ->orderByDesc('deleted_at')
            ->get();

        $salidasEliminadas->each(fn (MpSalidaInicial $salidaInicial) => $this->hydrateCalculatedFields($salidaInicial));

        return view('materia_prima.salidas_iniciales.deleted', compact('salidasEliminadas'));
    }

    public function restore($id)
    {
        try {
            $salidaInicial = MpSalidaInicial::withTrashed()->findOrFail($id);
            $salidaInicial->restore();

            return redirect()->route('mp_salidas_iniciales.index')->with('success', 'Salida inicial restaurada correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al restaurar mp_salidas_iniciales', ['error' => $e->getMessage()]);
            return redirect()->route('mp_salidas_iniciales.deleted')->with('error', 'No se pudo restaurar la salida inicial.');
        }
    }

    public function editMassive(Request $request, $id = null)
    {
        $selectedId = $id ? (int) $id : $request->integer('selected');
        $scope = $request->query('scope', 'without_movements');

        if ($scope === 'without_movements') {
            $this->syncHistoricalRowsWithoutMovements();
            $salidas = MpSalidaInicial::with(['ingresoMp.proveedor', 'ingresoMp.materiaPrima', 'ingresoMp.diametro'])
                ->whereNull('deleted_at')
                ->whereIn('Id_Ingreso_MP', $this->getIngresoIdsWithoutOperationalMovements())
                ->get()
                ->sortBy(fn (MpSalidaInicial $salidaInicial) => (int) ($salidaInicial->ingresoMp->Nro_Ingreso_MP ?? PHP_INT_MAX))
                ->values();
        } elseif ($scope === 'adjustments') {
            $salidas = $this->buildAdjustmentRows();
        } else {
            $salidas = MpSalidaInicial::with(['ingresoMp.proveedor', 'ingresoMp.materiaPrima', 'ingresoMp.diametro'])
                ->whereNull('deleted_at')
                ->get()
                ->sortBy(fn (MpSalidaInicial $salidaInicial) => (int) ($salidaInicial->ingresoMp->Nro_Ingreso_MP ?? PHP_INT_MAX))
                ->values();
        }

        $salidas->each(fn (MpSalidaInicial $salidaInicial) => $this->hydrateCalculatedFields($salidaInicial));
        $selectedSalida = $selectedId
            ? $salidas->firstWhere('Id_Ingreso_MP', $selectedId)
            : null;

        $returnTo = $request->query('return_to', 'salidas_iniciales');

        return view('materia_prima.salidas_iniciales.edit-massive', [
            'salidas' => $salidas,
            'selectedId' => $selectedId,
            'selectedSalida' => $selectedSalida,
            'returnTo' => $returnTo,
            'returnSelected' => $request->query('return_selected'),
            'scope' => $scope,
            'filteredCount' => $salidas->count(),
        ]);
    }

    public function updateMassive(Request $request)
    {
        $rows = $request->input('rows', []);

        $returnTo = $request->input('return_to', 'salidas_iniciales');
        $returnSelected = $request->input('return_selected');
        $scope = $request->input('scope', 'without_movements');

        if (empty($rows)) {
            return redirect()->route('mp_salidas_iniciales.editMassive', [
                'return_to' => $returnTo,
                'return_selected' => $returnSelected,
                'scope' => $scope,
            ])
                ->with('warning', 'No se recibieron filas para actualizar.');
        }

        DB::beginTransaction();

        try {
            $actualizados = 0;

            foreach ($rows as $idIngresoMp => $row) {
                $salidaInicial = MpSalidaInicial::where('Id_Ingreso_MP', (int) $idIngresoMp)
                    ->whereNull('deleted_at')
                    ->first();

                $ingreso = $this->findIngresoOrFail((int) $idIngresoMp);
                if (!$salidaInicial) {
                    if ($scope !== 'adjustments') {
                        continue;
                    }

                    $salidaInicial = new MpSalidaInicial([
                        'Id_Ingreso_MP' => (int) $idIngresoMp,
                        'Stock_Inicial' => (int) ($ingreso->Unidades_MP ?? 0),
                        'Devoluciones_Proveedor' => 0,
                        'Ajuste_Stock' => 0,
                        'Total_Salidas_MP' => (int) ($ingreso->Unidades_MP ?? 0),
                        'Total_mm_Utilizados' => round((int) ($ingreso->Unidades_MP ?? 0) * (float) ($ingreso->Longitud_Unidad_MP ?? 0), 2),
                        'reg_Status' => 1,
                        'created_by' => Auth::id(),
                    ]);
                }

                $payload = $this->buildPayload([
                    'Id_Ingreso_MP' => (int) $idIngresoMp,
                    'Stock_Inicial' => $row['Stock_Inicial'] ?? $salidaInicial->Stock_Inicial ?? ($ingreso->Unidades_MP ?? 0),
                    'Devoluciones_Proveedor' => $row['Devoluciones_Proveedor'] ?? $salidaInicial->Devoluciones_Proveedor,
                    'Ajuste_Stock' => $row['Ajuste_Stock'] ?? $salidaInicial->Ajuste_Stock,
                    'reg_Status' => $row['reg_Status'] ?? $salidaInicial->reg_Status,
                ], $ingreso, $scope);
                $payload['updated_by'] = Auth::id();

                $salidaInicial->fill($payload);

                if ($salidaInicial->isDirty()) {
                    $salidaInicial->save();
                    $actualizados++;
                }
            }

            DB::commit();

            return redirect()->to($this->resolveReturnUrl($returnTo, $returnSelected))
                ->with('success', $actualizados > 0
                    ? 'Ajustes iniciales actualizados correctamente.'
                    : 'No habia cambios para guardar.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar masivamente mp_salidas_iniciales', ['error' => $e->getMessage()]);

            return redirect()->to($this->resolveReturnUrl($returnTo, $returnSelected))
                ->with('error', 'No se pudieron actualizar los ajustes iniciales.');
        }
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'Id_Ingreso_MP' => 'required|integer|exists:mp_ingreso,Id_MP',
            'Stock_Inicial' => 'nullable|integer|min:0',
            'Devoluciones_Proveedor' => 'nullable|integer|min:0',
            'Ajuste_Stock' => 'nullable|integer',
            'reg_Status' => 'required|in:0,1',
        ]);
    }

    protected function buildPayload(array $validated, MpIngreso $ingreso, string $scope = 'default'): array
    {
        $stockInicial = (int) ($validated['Stock_Inicial'] ?? ($ingreso->Unidades_MP ?? 0));
        $devolucionesProveedor = (int) ($validated['Devoluciones_Proveedor'] ?? 0);
        $ajusteStock = $scope === 'without_movements'
            ? 0
            : (int) ($validated['Ajuste_Stock'] ?? 0);
        $longitud = (float) ($ingreso->Longitud_Unidad_MP ?? 0);
        $totalSalidas = $scope === 'without_movements'
            ? max(0, $stockInicial - $devolucionesProveedor)
            : max(0, $stockInicial - $devolucionesProveedor + $ajusteStock);
        $totalMtsUtilizados = round($totalSalidas * $longitud, 2);

        return [
            'Id_Ingreso_MP' => $ingreso->Id_MP,
            'Stock_Inicial' => $stockInicial,
            'Devoluciones_Proveedor' => $devolucionesProveedor,
            'Ajuste_Stock' => $ajusteStock,
            'Total_Salidas_MP' => $totalSalidas,
            'Total_mm_Utilizados' => $totalMtsUtilizados,
            'reg_Status' => (int) $validated['reg_Status'],
        ];
    }

    protected function findIngresoOrFail(int $id): MpIngreso
    {
        return MpIngreso::where('Id_MP', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    protected function resolveReturnUrl(?string $returnTo, $returnSelected = null): string
    {
        if ($returnTo === 'stock_mp') {
            $params = [];

            if (!blank($returnSelected)) {
                $params['selected'] = (int) $returnSelected;
            }

            return route('mp_stock.index', $params);
        }

        return route('mp_salidas_iniciales.index');
    }

    protected function getPendientesQuery()
    {
        return MpIngreso::query()
            ->with(['proveedor', 'materiaPrima', 'diametro'])
            ->whereNull('deleted_at')
            ->whereDoesntHave('salidaInicial')
            ->orderBy('Nro_Ingreso_MP');
    }

    protected function getIngresoIdsWithoutOperationalMovements(): array
    {
        return DB::table('mp_salidas_iniciales as si')
            ->join('mp_ingreso as i', function ($join) {
                $join->on('i.Id_MP', '=', 'si.Id_Ingreso_MP')
                    ->whereNull('i.deleted_at')
                    ->where('i.reg_Status', 1);
            })
            ->whereNull('si.deleted_at')
            ->whereDate('i.Fecha_Ingreso', '<', $this->historicalCutoffDate)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pedido_cliente_mp as pm')
                    ->join('mp_salidas as s', function ($join) {
                        $join->on('s.Id_OF_Salidas_MP', '=', 'pm.Id_OF')
                            ->whereNull('s.deleted_at');
                    })
                    ->whereNull('pm.deleted_at')
                    ->whereColumn('pm.Nro_Ingreso_MP', 'i.Nro_Ingreso_MP');
            })
            ->orderBy('i.Nro_Ingreso_MP')
            ->pluck('si.Id_Ingreso_MP')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function getIngresoIdsWithOperationalMovements(): array
    {
        return DB::table('mp_ingreso as i')
            ->whereNull('i.deleted_at')
            ->where('i.reg_Status', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pedido_cliente_mp as pm')
                    ->join('mp_salidas as s', function ($join) {
                        $join->on('s.Id_OF_Salidas_MP', '=', 'pm.Id_OF')
                            ->whereNull('s.deleted_at');
                    })
                    ->whereNull('pm.deleted_at')
                    ->whereColumn('pm.Nro_Ingreso_MP', 'i.Nro_Ingreso_MP');
            })
            ->orderBy('i.Nro_Ingreso_MP')
            ->pluck('i.Id_MP')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function syncHistoricalRowsWithoutMovements(): void
    {
        $ingresos = MpIngreso::query()
            ->whereNull('deleted_at')
            ->where('reg_Status', 1)
            ->whereIn('Id_MP', $this->getIngresoIdsWithoutOperationalMovementsSource())
            ->get();

        foreach ($ingresos as $ingreso) {
            $stockInicial = (int) ($ingreso->Unidades_MP ?? 0);
            $totalMts = round($stockInicial * (float) ($ingreso->Longitud_Unidad_MP ?? 0), 2);

            MpSalidaInicial::query()->firstOrCreate(
                ['Id_Ingreso_MP' => $ingreso->Id_MP],
                [
                    'Stock_Inicial' => $stockInicial,
                    'Devoluciones_Proveedor' => 0,
                    'Ajuste_Stock' => 0,
                    'Total_Salidas_MP' => $stockInicial,
                    'Total_mm_Utilizados' => $totalMts,
                    'reg_Status' => 1,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );
        }
    }

    protected function getIngresoIdsWithoutOperationalMovementsSource(): array
    {
        return DB::table('mp_ingreso as i')
            ->whereNull('i.deleted_at')
            ->where('i.reg_Status', 1)
            ->whereDate('i.Fecha_Ingreso', '<', $this->historicalCutoffDate)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pedido_cliente_mp as pm')
                    ->join('mp_salidas as s', function ($join) {
                        $join->on('s.Id_OF_Salidas_MP', '=', 'pm.Id_OF')
                            ->whereNull('s.deleted_at');
                    })
                    ->whereNull('pm.deleted_at')
                    ->whereColumn('pm.Nro_Ingreso_MP', 'i.Nro_Ingreso_MP');
            })
            ->orderBy('i.Nro_Ingreso_MP')
            ->pluck('i.Id_MP')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function buildAdjustmentRows()
    {
        $stockRowsById = collect(app(PedidoClienteMpPlannerService::class)->buildStockDashboard()['rows'] ?? [])
            ->keyBy('Id_MP');

        $ingresos = MpIngreso::query()
            ->with(['proveedor', 'materiaPrima', 'diametro', 'salidaInicial'])
            ->whereNull('deleted_at')
            ->where('reg_Status', 1)
            ->whereIn('Id_MP', $this->getIngresoIdsWithOperationalMovements())
            ->orderBy('Nro_Ingreso_MP')
            ->get();

        return $ingresos->map(function (MpIngreso $ingreso) use ($stockRowsById) {
            $stockRow = $stockRowsById->get((int) $ingreso->Id_MP, []);
            $salidaInicial = $ingreso->salidaInicial ?: new MpSalidaInicial([
                'Id_Ingreso_MP' => (int) $ingreso->Id_MP,
                'Stock_Inicial' => (int) ($ingreso->Unidades_MP ?? 0),
                'Devoluciones_Proveedor' => 0,
                'Ajuste_Stock' => 0,
                'Total_Salidas_MP' => (int) ($ingreso->Unidades_MP ?? 0),
                'Total_mm_Utilizados' => round((int) ($ingreso->Unidades_MP ?? 0) * (float) ($ingreso->Longitud_Unidad_MP ?? 0), 2),
                'reg_Status' => 1,
            ]);

            $salidaInicial->setRelation('ingresoMp', $ingreso);
            $salidaInicial->setAttribute('Planner_Barras_Solicitadas_OF', (int) ($stockRow['Barras_Solicitadas_OF'] ?? 0));
            $salidaInicial->setAttribute('Planner_Barras_Preparadas_OF', (int) ($stockRow['Cant_Barras_Of_Iniciales'] ?? 0));
            $salidaInicial->setAttribute('Planner_Cantidad_Adicionales_Stock', (int) ($stockRow['Cantidad_Adicionales_Stock'] ?? 0));
            $salidaInicial->setAttribute('Planner_Cantidad_Devoluciones_Stock', (int) ($stockRow['Cantidad_Devoluciones_Stock'] ?? 0));
            $salidaInicial->setAttribute('Planner_Barras_Ingresos_Iniciales', (int) ($stockRow['Barras_Ingresos_Iniciales'] ?? 0));
            $salidaInicial->setAttribute('Planner_Cantidad_Total_OF', (int) ($stockRow['Cantidad_Total_OF'] ?? 0));
            $salidaInicial->setAttribute('Planner_Barras_Disponibles', (int) ($stockRow['Barras_Disponibles'] ?? 0));
            $salidaInicial->setAttribute('Planner_Mts_Disponibles', (float) ($stockRow['Mts_Disponibles'] ?? 0));

            return $salidaInicial;
        })->values();
    }

    protected function hydrateCalculatedFields(MpSalidaInicial $salidaInicial): void
    {
        $ingreso = $salidaInicial->ingresoMp;
        $stockInicial = (int) ($salidaInicial->Stock_Inicial ?? 0);
        $devolucionesProveedor = (int) ($salidaInicial->Devoluciones_Proveedor ?? 0);
        $ajusteStock = (int) ($salidaInicial->Ajuste_Stock ?? 0);
        $totalSalidas = (int) ($salidaInicial->Total_Salidas_MP ?? ($stockInicial - $devolucionesProveedor + $ajusteStock));
        $longitud = (float) ($ingreso->Longitud_Unidad_MP ?? 0);
        $totalMtsUtilizados = (float) ($salidaInicial->Total_mm_Utilizados ?? round($totalSalidas * $longitud, 2));

        $salidaInicial->setAttribute('Unidades_Ingresadas', (int) ($ingreso->Unidades_MP ?? 0));
        $salidaInicial->setAttribute('Longitud_Unidad_Calculada', $longitud);
        $salidaInicial->setAttribute('Stock_Inicial_Calculado', $stockInicial);
        $salidaInicial->setAttribute('Devoluciones_Proveedor_Calculadas', $devolucionesProveedor);
        $salidaInicial->setAttribute('Ajuste_Stock_Calculado', $ajusteStock);
        $salidaInicial->setAttribute('Total_Salidas_Calculadas', $totalSalidas);
        $salidaInicial->setAttribute('Total_mm_Utilizados_Calculados', $totalMtsUtilizados);
    }

    protected function formatInt($value): string
    {
        return number_format((int) $value, 0, ',', '.');
    }

    protected function formatSignedInt($value): string
    {
        $value = (int) $value;
        if ($value > 0) {
            return '+' . number_format($value, 0, ',', '.');
        }

        return number_format($value, 0, ',', '.');
    }

    protected function formatDecimal($value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }
}
