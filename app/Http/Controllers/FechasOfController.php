<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\FechasOf;
use App\Models\PedidoCliente;
use App\Services\HistoricalFechasOfImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Yajra\DataTables\Facades\DataTables;

class FechasOfController extends Controller
{
    use CheckForChanges;

    private const EMPTY_DATE = '9999-12-31';
    private const EMPTY_TIME = '00:00:00';

    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only(['index', 'getData', 'show']);
        $this->middleware('permission:editar produccion')->only(['create', 'store', 'edit', 'update', 'importHistoricCsv']);
        $this->middleware('permission:eliminar produccion')->only('destroy');
    }

    public function index()
    {
        $this->syncMissingRows();

        return view('fechas_of.index', [
            'totalOf' => PedidoCliente::count(),
            'ofConTiempo' => FechasOf::query()->where('Tiempo_Seg', '>', 0)->count(),
            'ofPendientes' => PedidoCliente::count() - FechasOf::query()->where('Tiempo_Seg', '>', 0)->count(),
            'categorias' => DB::table('producto_categoria')
                ->whereNotNull('Nombre_Categoria')
                ->orderBy('Nombre_Categoria')
                ->pluck('Nombre_Categoria'),
            'maquinas' => DB::table('pedido_cliente_mp')
                ->whereNotNull('Nro_Maquina')
                ->where('Nro_Maquina', '<>', '')
                ->distinct()
                ->orderBy('Nro_Maquina')
                ->pluck('Nro_Maquina'),
            'programasH1' => FechasOf::query()
                ->whereNotNull('Nro_Programa_H1')
                ->where('Nro_Programa_H1', '<>', '')
                ->distinct()
                ->orderBy('Nro_Programa_H1')
                ->pluck('Nro_Programa_H1'),
            'programasH2' => FechasOf::query()
                ->whereNotNull('Nro_Programa_H2')
                ->where('Nro_Programa_H2', '<>', '')
                ->distinct()
                ->orderBy('Nro_Programa_H2')
                ->pluck('Nro_Programa_H2'),
        ]);
    }

    public function getData(Request $request)
    {
        try {
            $this->syncMissingRows();

            $query = PedidoCliente::with([
                'producto.categoria',
                'definicionMp',
                'fechas',
            ])
                ->select([
                    'Id_OF',
                    'Nro_OF',
                    'Producto_Id',
                    'Fecha_del_Pedido',
                    'Cant_Fabricacion',
                ])
                ->orderByDesc('Nro_OF');

            if ($request->filled('filtro_nro_of')) {
                $query->where('Nro_OF', 'like', '%' . trim((string) $request->filtro_nro_of) . '%');
            }

            if ($request->filled('filtro_producto')) {
                $query->whereHas('producto', function ($subQuery) use ($request) {
                    $term = trim((string) $request->filtro_producto);

                    $subQuery->where(function ($nested) use ($term) {
                        $nested->where('Prod_Codigo', 'like', '%' . $term . '%')
                            ->orWhere('Prod_Descripcion', 'like', '%' . $term . '%');
                    });
                });
            }

            if ($request->filled('filtro_categoria')) {
                $query->whereHas('producto.categoria', function ($subQuery) use ($request) {
                    $subQuery->where('Nombre_Categoria', $request->filtro_categoria);
                });
            }

            if ($request->filled('filtro_maquina')) {
                $query->whereHas('definicionMp', function ($subQuery) use ($request) {
                    $subQuery->where('Nro_Maquina', $request->filtro_maquina);
                });
            }

            if ($request->filled('filtro_programa_h1')) {
                $query->whereHas('fechas', function ($subQuery) use ($request) {
                    $subQuery->where('Nro_Programa_H1', $request->filtro_programa_h1);
                });
            }

            if ($request->filled('filtro_programa_h2')) {
                $query->whereHas('fechas', function ($subQuery) use ($request) {
                    $subQuery->where('Nro_Programa_H2', $request->filtro_programa_h2);
                });
            }

            if ($request->filled('filtro_tiempo_seg')) {
                $query->whereHas('fechas', function ($subQuery) use ($request) {
                    $subQuery->where('Tiempo_Seg', (int) $request->filtro_tiempo_seg);
                });
            }

            if ($request->filled('filtro_estado_carga')) {
                if ($request->filtro_estado_carga === 'completo') {
                    $query->whereHas('fechas', function ($subQuery) {
                        $subQuery->where('Tiempo_Seg', '>', 0);
                    });
                }

                if ($request->filtro_estado_carga === 'pendiente') {
                    $query->where(function ($subQuery) {
                        $subQuery->whereDoesntHave('fechas')
                            ->orWhereHas('fechas', function ($nested) {
                                $nested->where('Tiempo_Seg', '<=', 0);
                            });
                    });
                }
            }

            return DataTables::eloquent($query)
                ->addColumn('Prod_Codigo', fn ($pedido) => $pedido->producto->Prod_Codigo ?? '')
                ->addColumn('Prod_Descripcion', fn ($pedido) => $pedido->producto->Prod_Descripcion ?? '')
                ->addColumn('Nombre_Categoria', fn ($pedido) => $pedido->producto->categoria->Nombre_Categoria ?? '')
                ->addColumn('Nro_Maquina', fn ($pedido) => $pedido->definicionMp->Nro_Maquina ?? '')
                ->addColumn('Nro_Programa_H1', fn ($pedido) => $pedido->fechas->Nro_Programa_H1 ?? '')
                ->addColumn('Nro_Programa_H2', fn ($pedido) => $pedido->fechas->Nro_Programa_H2 ?? '')
                ->addColumn('Inicio_PAP', fn ($pedido) => $this->formatDateOutput(optional($pedido->fechas)->Inicio_PAP))
                ->addColumn('Hora_Inicio_PAP', fn ($pedido) => $this->formatTimeOutput(optional($pedido->fechas)->Hora_Inicio_PAP))
                ->addColumn('Fin_PAP', fn ($pedido) => $this->formatDateOutput(optional($pedido->fechas)->Fin_PAP))
                ->addColumn('Hora_Fin_PAP', fn ($pedido) => $this->formatTimeOutput(optional($pedido->fechas)->Hora_Fin_PAP))
                ->addColumn('Inicio_OF', fn ($pedido) => $this->formatDateOutput(optional($pedido->fechas)->Inicio_OF))
                ->addColumn('Finalizacion_OF', fn ($pedido) => $this->formatDateOutput(optional($pedido->fechas)->Finalizacion_OF))
                ->addColumn('Tiempo_Pieza', fn ($pedido) => $this->formatDecimalOutput(optional($pedido->fechas)->Tiempo_Pieza))
                ->addColumn('Tiempo_Seg', fn ($pedido) => (int) (optional($pedido->fechas)->Tiempo_Seg ?? 0))
                ->addColumn('Estado_Carga', function ($pedido) {
                    return (int) (optional($pedido->fechas)->Tiempo_Seg ?? 0) > 0 ? 'Completo' : 'Pendiente';
                })
                ->addColumn('acciones', function ($pedido) {
                    $id = optional($pedido->fechas)->Id_Fechas;

                    if (!$id) {
                        return '<span class="text-muted">Sin ficha</span>';
                    }

                    return '
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="' . route('fechas_of.edit', $id) . '" class="btn btn-primary">Editar</a>
                            <button type="button" class="btn btn-outline-danger js-limpiar-registro" data-id="' . $id . '">Limpiar</button>
                        </div>
                    ';
                })
                ->rawColumns(['acciones'])
                ->toJson();
        } catch (\Exception $e) {
            Log::error('Error al obtener fechas_of', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Error al obtener los datos.'], 500);
        }
    }

    public function create()
    {
        $this->syncMissingRows();

        $pedidos = PedidoCliente::with(['producto.categoria', 'definicionMp', 'fechas'])
            ->orderBy('Nro_OF')
            ->get();

        $machineMap = $this->getMachineMapByOfNumbers(
            $pedidos->pluck('Nro_OF')
                ->filter()
                ->unique()
                ->values()
                ->all()
        );

        $rows = $pedidos
            ->map(function (PedidoCliente $pedido) use ($machineMap) {
                $fechas = $pedido->fechas;
                $machineNumber = $this->resolveMachineNumber($pedido, $machineMap);

                return [
                    'Id_OF' => $pedido->Id_OF,
                    'Id_Fechas' => optional($fechas)->Id_Fechas,
                    'Nro_OF' => $pedido->Nro_OF,
                    'Prod_Codigo' => $pedido->producto->Prod_Codigo ?? '',
                    'Prod_Descripcion' => $pedido->producto->Prod_Descripcion ?? '',
                    'Nombre_Categoria' => $pedido->producto->categoria->Nombre_Categoria ?? '',
                    'Nro_Maquina' => $machineNumber,
                    'Nro_Programa_H1' => $fechas->Nro_Programa_H1 ?? '',
                    'Nro_Programa_H2' => $fechas->Nro_Programa_H2 ?? '',
                    'Inicio_PAP' => $this->dateForForm(optional($fechas)->Inicio_PAP),
                    'Hora_Inicio_PAP' => $this->timeForForm(optional($fechas)->Hora_Inicio_PAP),
                    'Fin_PAP' => $this->dateForForm(optional($fechas)->Fin_PAP),
                    'Hora_Fin_PAP' => $this->timeForForm(optional($fechas)->Hora_Fin_PAP),
                    'Inicio_OF' => $this->dateForForm(optional($fechas)->Inicio_OF),
                    'Finalizacion_OF' => $this->dateForForm(optional($fechas)->Finalizacion_OF),
                    'Tiempo_Pieza' => $this->decimalForForm(optional($fechas)->Tiempo_Pieza),
                    'Tiempo_Seg' => (int) (optional($fechas)->Tiempo_Seg ?? 0),
                ];
            })
            ->values();

        return view('fechas_of.create', [
            'rows' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'Id_OF' => 'required|array|min:1',
                'Id_OF.*' => 'required|integer|exists:pedido_cliente,Id_OF',
                'Nro_OF_fechas' => 'required|array|min:1',
                'Nro_OF_fechas.*' => 'required|integer',
                'Nro_Programa_H1' => 'nullable|array',
                'Nro_Programa_H2' => 'nullable|array',
                'Inicio_PAP' => 'nullable|array',
                'Hora_Inicio_PAP' => 'nullable|array',
                'Fin_PAP' => 'nullable|array',
                'Hora_Fin_PAP' => 'nullable|array',
                'Inicio_OF' => 'nullable|array',
                'Finalizacion_OF' => 'nullable|array',
                'Tiempo_Pieza' => 'nullable|array',
            ]);

            $invalidRows = [];

            DB::beginTransaction();

            foreach ($validated['Id_OF'] as $index => $idOf) {
                $payload = $this->buildPayloadFromRow($request, $index, (int) $idOf, (int) $validated['Nro_OF_fechas'][$index]);

                if ($payload === null) {
                    $invalidRows[] = $index + 1;
                    continue;
                }

                $registro = FechasOf::query()->firstOrNew(['Id_OF' => (int) $idOf]);

                if (!$registro->exists) {
                    $payload['created_by'] = Auth::id();
                }

                $registro->fill($payload);
                $registro->save();
            }

            if (!empty($invalidRows)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Hay filas con Tiempo de Pieza invalido. Usa el formato mm.ss y segundos entre 00 y 59.',
                    'invalidRows' => $invalidRows,
                ], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tiempos de produccion guardados correctamente.',
                'redirect' => route('fechas_of.index'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al guardar fechas_of', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error al guardar la planificacion de OF.',
            ], 400);
        }
    }

    public function show(string $id)
    {
        return redirect()->route('fechas_of.edit', $id);
    }

    public function importHistoricCsv(HistoricalFechasOfImportService $historicalImportService)
    {
        try {
            $summary = $historicalImportService->importFromDefaultPath(Auth::id());

            $message = sprintf(
                'Importacion completada. Filas procesadas: %d. Actualizadas: %d. Creadas: %d. Sin pedido asociado: %d.',
                $summary['processed'],
                $summary['updated'],
                $summary['created'],
                $summary['skipped_missing_pedido']
            );

            if (($summary['invalid_rows'] ?? 0) > 0) {
                $message .= ' Filas invalidas omitidas: ' . $summary['invalid_rows'] . '.';
            }

            return redirect()->route('fechas_of.index')->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Error al importar CSV historico de fechas_of', [
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return redirect()->route('fechas_of.index')
                ->with('error', 'No se pudo importar el CSV de fechas OF. Revisar log.');
        }
    }

    public function edit($id)
    {
        $fechasOf = FechasOf::with(['pedido.producto.categoria', 'pedido.definicionMp'])->findOrFail($id);

        return view('fechas_of.edit', [
            'fechasOf' => $fechasOf,
            'tiempoPiezaForm' => $this->decimalForForm($fechasOf->Tiempo_Pieza),
            'inicioPapForm' => $this->dateForForm($fechasOf->Inicio_PAP),
            'horaInicioPapForm' => $this->timeForForm($fechasOf->Hora_Inicio_PAP),
            'finPapForm' => $this->dateForForm($fechasOf->Fin_PAP),
            'horaFinPapForm' => $this->timeForForm($fechasOf->Hora_Fin_PAP),
            'inicioOfForm' => $this->dateForForm($fechasOf->Inicio_OF),
            'finalizacionOfForm' => $this->dateForForm($fechasOf->Finalizacion_OF),
        ]);
    }

    public function update(Request $request, $id)
    {
        $fechasOf = FechasOf::findOrFail($id);

        $validated = $request->validate([
            'Nro_Programa_H1' => 'nullable|string|max:255',
            'Nro_Programa_H2' => 'nullable|string|max:255',
            'Inicio_PAP' => 'nullable|date',
            'Hora_Inicio_PAP' => 'nullable|date_format:H:i',
            'Fin_PAP' => 'nullable|date',
            'Hora_Fin_PAP' => 'nullable|date_format:H:i',
            'Inicio_OF' => 'nullable|date',
            'Finalizacion_OF' => 'nullable|date',
            'Tiempo_Pieza' => 'nullable|string|max:10',
        ]);

        $tiempoSeg = $this->calculateTiempoSegFromInput($request->input('Tiempo_Pieza'));

        if ($tiempoSeg === null && filled($request->input('Tiempo_Pieza'))) {
            return response()->json([
                'success' => false,
                'message' => 'El Tiempo de Pieza debe tener formato mm.ss y segundos entre 00 y 59.',
            ], 422);
        }

        $payload = [
            'Nro_Programa_H1' => $this->normalizeNullableString($validated['Nro_Programa_H1'] ?? null),
            'Nro_Programa_H2' => $this->normalizeNullableString($validated['Nro_Programa_H2'] ?? null),
            'Inicio_PAP' => $this->normalizeDate($validated['Inicio_PAP'] ?? null),
            'Hora_Inicio_PAP' => $this->normalizeTime($validated['Hora_Inicio_PAP'] ?? null),
            'Fin_PAP' => $this->normalizeDate($validated['Fin_PAP'] ?? null),
            'Hora_Fin_PAP' => $this->normalizeTime($validated['Hora_Fin_PAP'] ?? null),
            'Inicio_OF' => $this->normalizeDate($validated['Inicio_OF'] ?? null),
            'Finalizacion_OF' => $this->normalizeDate($validated['Finalizacion_OF'] ?? null),
            'Tiempo_Pieza' => $this->normalizeDecimalForStorage($validated['Tiempo_Pieza'] ?? null),
            'Tiempo_Seg' => $tiempoSeg ?? 0,
            'updated_by' => Auth::id(),
        ];

        return $this->updateIfChanged($fechasOf, $payload, [
            'success_redirect' => route('fechas_of.index'),
            'success_message' => 'Registro actualizado correctamente.',
            'no_changes_message' => 'No se detectaron cambios.',
            'set_updated_by' => false,
            'use_transaction' => true,
            'normalize_data' => false,
        ]);
    }

    public function destroy($id)
    {
        try {
            $fechasOf = FechasOf::findOrFail($id);
            $fechasOf->update([
                'Nro_Programa_H1' => null,
                'Nro_Programa_H2' => null,
                'Inicio_PAP' => self::EMPTY_DATE,
                'Hora_Inicio_PAP' => self::EMPTY_TIME,
                'Fin_PAP' => self::EMPTY_DATE,
                'Hora_Fin_PAP' => self::EMPTY_TIME,
                'Inicio_OF' => self::EMPTY_DATE,
                'Finalizacion_OF' => self::EMPTY_DATE,
                'Tiempo_Pieza' => 0,
                'Tiempo_Seg' => 0,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Datos de la OF limpiados correctamente.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al limpiar fechas_of', [
                'id' => $id,
                'error' => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron limpiar los datos de la OF.',
            ], 400);
        }
    }

    private function buildPayloadFromRow(Request $request, int $index, int $idOf, int $nroOf): ?array
    {
        $tiempoInput = $request->input("Tiempo_Pieza.$index");
        $tiempoSeg = $this->calculateTiempoSegFromInput($tiempoInput);

        if ($tiempoSeg === null && filled($tiempoInput)) {
            return null;
        }

        return [
            'Id_OF' => $idOf,
            'Nro_OF_fechas' => $nroOf,
            'Nro_Programa_H1' => $this->normalizeNullableString($request->input("Nro_Programa_H1.$index")),
            'Nro_Programa_H2' => $this->normalizeNullableString($request->input("Nro_Programa_H2.$index")),
            'Inicio_PAP' => $this->normalizeDate($request->input("Inicio_PAP.$index")),
            'Hora_Inicio_PAP' => $this->normalizeTime($request->input("Hora_Inicio_PAP.$index")),
            'Fin_PAP' => $this->normalizeDate($request->input("Fin_PAP.$index")),
            'Hora_Fin_PAP' => $this->normalizeTime($request->input("Hora_Fin_PAP.$index")),
            'Inicio_OF' => $this->normalizeDate($request->input("Inicio_OF.$index")),
            'Finalizacion_OF' => $this->normalizeDate($request->input("Finalizacion_OF.$index")),
            'Tiempo_Pieza' => $this->normalizeDecimalForStorage($tiempoInput),
            'Tiempo_Seg' => $tiempoSeg ?? 0,
            'reg_Status' => 1,
            'updated_by' => Auth::id(),
        ];
    }

    private function normalizeNullableString($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function normalizeDate($value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? self::EMPTY_DATE : $value;
    }

    private function normalizeTime($value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return self::EMPTY_TIME;
        }

        return strlen($value) === 5 ? $value . ':00' : $value;
    }

    private function normalizeDecimalForStorage($value): float
    {
        $value = trim(str_replace(',', '.', (string) ($value ?? '')));

        if ($value === '') {
            return 0.00;
        }

        return (float) number_format((float) $value, 2, '.', '');
    }

    private function calculateTiempoSegFromInput($value): ?int
    {
        $raw = trim(str_replace(',', '.', (string) ($value ?? '')));

        if ($raw === '') {
            return 0;
        }

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $raw)) {
            return null;
        }

        $parts = explode('.', $raw);
        $minutes = (int) $parts[0];
        $seconds = isset($parts[1]) ? (int) str_pad(substr($parts[1], 0, 2), 2, '0') : 0;

        if ($seconds > 59) {
            return null;
        }

        return ($minutes * 60) + $seconds;
    }

    private function dateForForm($value): string
    {
        if (!$value) {
            return '';
        }

        $formatted = is_string($value) ? substr($value, 0, 10) : $value->format('Y-m-d');

        return $formatted === self::EMPTY_DATE ? '' : $formatted;
    }

    private function timeForForm($value): string
    {
        if (!$value) {
            return '';
        }

        $formatted = substr((string) $value, 0, 5);

        return $formatted === '00:00' ? '' : $formatted;
    }

    private function decimalForForm($value): string
    {
        if ($value === null) {
            return '';
        }

        $formatted = number_format((float) $value, 2, '.', '');

        return $formatted === '0.00' ? '' : $formatted;
    }

    private function formatDateOutput($value): string
    {
        $date = $this->dateForForm($value);

        return $date === '' ? '' : $date;
    }

    private function formatTimeOutput($value): string
    {
        return $this->timeForForm($value);
    }

    private function formatDecimalOutput($value): string
    {
        return number_format((float) ($value ?? 0), 2, ',', '.');
    }

    private function getMachineMapByOfNumbers(array $nrosOf): Collection
    {
        if (empty($nrosOf)) {
            return collect();
        }

        return DB::table('listado_of_db')
            ->selectRaw('Nro_OF')
            ->selectRaw("MAX(CONVERT(Nro_Maquina USING utf8mb4) COLLATE utf8mb4_spanish_ci) AS Nro_Maquina")
            ->whereIn('Nro_OF', $nrosOf)
            ->groupBy('Nro_OF')
            ->pluck('Nro_Maquina', 'Nro_OF');
    }

    private function resolveMachineNumber(PedidoCliente $pedido, Collection $machineMap): string
    {
        $machineFromListadoOf = trim((string) $machineMap->get($pedido->Nro_OF, ''));

        if ($machineFromListadoOf !== '') {
            return $machineFromListadoOf;
        }

        return trim((string) ($pedido->definicionMp->Nro_Maquina ?? ''));
    }

    private function syncMissingRows(): void
    {
        $pedidosSinFila = PedidoCliente::query()
            ->doesntHave('fechas')
            ->get(['Id_OF', 'Nro_OF', 'created_by', 'updated_by']);

        foreach ($pedidosSinFila as $pedido) {
            FechasOf::query()->create([
                'Id_OF' => $pedido->Id_OF,
                'Nro_OF_fechas' => $pedido->Nro_OF,
                'Inicio_PAP' => self::EMPTY_DATE,
                'Hora_Inicio_PAP' => self::EMPTY_TIME,
                'Fin_PAP' => self::EMPTY_DATE,
                'Hora_Fin_PAP' => self::EMPTY_TIME,
                'Inicio_OF' => self::EMPTY_DATE,
                'Finalizacion_OF' => self::EMPTY_DATE,
                'Tiempo_Pieza' => 0,
                'Tiempo_Seg' => 0,
                'reg_Status' => 1,
                'created_by' => $pedido->created_by,
                'updated_by' => $pedido->updated_by ?? $pedido->created_by,
            ]);
        }
    }
}
