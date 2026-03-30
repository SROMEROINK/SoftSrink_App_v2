<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\EstadoPlanificacion;
use App\Models\MpDiametro;
use App\Models\MpIngreso;
use App\Models\MpMateriaPrima;
use App\Models\PedidoCliente;
use App\Models\PedidoClienteMp;
use App\Services\PedidoClienteMpPlannerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Yajra\DataTables\Facades\DataTables;

class PedidoClienteMpController extends Controller
{
    use CheckForChanges;
    protected bool $pedidoMaterialNormalizado = false;


    public function __construct(protected PedidoClienteMpPlannerService $plannerService)
    {
        $this->middleware('permission:ver produccion')->only(['index', 'show', 'getData', 'resumen', 'showDeleted', 'planner']);
        $this->middleware('permission:editar produccion')->only(['create', 'store', 'createMassive', 'storeMassive', 'editGroup', 'updateGroup', 'editMassive', 'updateMassive', 'edit', 'update', 'destroy', 'restore']);
    }

    public function getData(Request $request)
    {
        $this->normalizeInternalPedidoMaterialNumbers();

        $query = DB::table('pedido_cliente as p')
            ->leftJoin('pedido_cliente_mp as pm', function ($join) {
                $join->on('pm.Id_OF', '=', 'p.Id_OF')
                    ->whereNull('pm.deleted_at');
            })
            ->leftJoin('productos as prod', 'prod.Id_Producto', '=', 'p.Producto_Id')
            ->leftJoin('producto_categoria as cat', 'cat.Id_Categoria', '=', 'prod.Id_Prod_Categoria')
            ->whereNull('p.deleted_at')
            ->where(function ($sub) {
                $sub->whereNotNull('pm.Id_Pedido_MP')
                    ->orWhereNotExists(function ($legacy) {
                        $legacy->select(DB::raw(1))
                            ->from('listado_of as legacy_of')
                            ->whereColumn('legacy_of.Nro_OF', 'p.Nro_OF');
                    });
            })
            ->select([
                'p.Id_OF',
                'p.Nro_OF',
                'p.Fecha_del_Pedido',
                'p.Cant_Fabricacion',
                'prod.Prod_Codigo',
                'cat.Nombre_Categoria',
                'pm.Id_Pedido_MP',
                'pm.Nro_Ingreso_MP',
                'pm.Pedido_Material_Nro',
                'pm.Codigo_MP',
                'pm.Cant_Barras_MP',
                DB::raw('COALESCE(pm.Estado_Plani_Id, p.Estado_Plani_Id) as Estado_Plani_Id'),
                DB::raw("(SELECT ep.Nombre_Estado FROM estado_planificacion ep WHERE ep.Estado_Plani_Id = COALESCE(pm.Estado_Plani_Id, p.Estado_Plani_Id) LIMIT 1) as Estado_MP"),
            ]);

        if ($request->filled('filtro_of')) {
            $query->where('p.Nro_OF', $request->filtro_of);
        }

        if ($request->filled('filtro_producto')) {
            $query->where('prod.Prod_Codigo', 'like', '%' . $request->filtro_producto . '%');
        }

        if ($request->filled('filtro_categoria')) {
            $query->where('cat.Nombre_Categoria', $request->filtro_categoria);
        }

        if ($request->filled('filtro_estado')) {
            $query->whereRaw('COALESCE(pm.Estado_Plani_Id, p.Estado_Plani_Id) = ?', [$request->filtro_estado]);
        }

        if ($request->filled('filtro_codigo_mp')) {
            $query->where('pm.Codigo_MP', $request->filtro_codigo_mp);
        }

        if ($request->filled('filtro_pedido_material')) {
            $pedidoMaterialFiltro = trim((string) $request->filtro_pedido_material);

            if (is_numeric($pedidoMaterialFiltro)) {
                $query->whereRaw('CAST(pm.Pedido_Material_Nro AS UNSIGNED) = ?', [(int) $pedidoMaterialFiltro]);
            } else {
                $query->where('pm.Pedido_Material_Nro', $pedidoMaterialFiltro);
            }
        }

        return DataTables::of($query)
            ->orderColumn('pm.Pedido_Material_Nro', function ($query, $order) {
                $query->orderByRaw("CAST(pm.Pedido_Material_Nro AS UNSIGNED) {$order}");
            })
            ->orderColumn('Pedido_Material_Nro', function ($query, $order) {
                $query->orderByRaw("CAST(pm.Pedido_Material_Nro AS UNSIGNED) {$order}");
            })
            ->editColumn('Fecha_del_Pedido', fn ($row) => $row->Fecha_del_Pedido ? Carbon::parse($row->Fecha_del_Pedido)->format('d/m/Y') : '')
            ->editColumn('Cant_Fabricacion', fn ($row) => $row->Cant_Fabricacion !== null ? number_format((int) $row->Cant_Fabricacion, 0, ',', '.') : '')
            ->editColumn('Nro_Ingreso_MP', fn ($row) => $row->Nro_Ingreso_MP !== null ? number_format((int) $row->Nro_Ingreso_MP, 0, ',', '.') : '')
            ->editColumn('Cant_Barras_MP', fn ($row) => $row->Cant_Barras_MP !== null ? number_format((int) $row->Cant_Barras_MP, 0, ',', '.') : '')
            ->toJson();
    }

    public function index()
    {
        $this->normalizeInternalPedidoMaterialNumbers();

        return view('pedido_cliente_mp.index', $this->legacyPendingSummary());
    }

    public function resumen()
    {
        $this->normalizeInternalPedidoMaterialNumbers();

        $pendingSummary = $this->legacyPendingSummary();

        return response()->json([
            'total' => PedidoClienteMp::withTrashed()->count(),
            'definidas' => PedidoClienteMp::whereNotNull('Codigo_MP')->where('Codigo_MP', '!=', '')->count(),
            'eliminados' => PedidoClienteMp::onlyTrashed()->count(),
            'legacy_max_nro_of' => $pendingSummary['legacyMaxNroOf'],
            'pending_of_count' => $pendingSummary['pendingOfCount'],
            'pending_min_nro_of' => $pendingSummary['pendingMinNroOf'],
            'pending_max_nro_of' => $pendingSummary['pendingMaxNroOf'],
        ]);
    }

    public function create(Request $request)
    {
        $this->normalizeInternalPedidoMaterialNumbers();
        $selectedOf = $request->query('of');
        $compactSelectorMode = $request->boolean('from_massive');
        $selectedMachine = $request->query('machine');
        $massiveRowIndex = $request->query('row');
        $massiveReturnUrl = $request->query('return_url', route('pedido_cliente_mp.createMassive'));
        $massiveSelectionStorageKey = $request->query('storage_key', 'pedidoClienteMpMassiveSelection');
        $selectedIngreso = $request->query('selected_ingreso');
        $selectedCertificado = $request->query('selected_certificado');
        $selectedPedidoMaterial = $request->query('selected_pedido_material');
        $selectedLongitudUnMp = $request->query('selected_longitud_un_mp');
        $selectedMateriaPrima = $request->query('selected_materia_prima');
        $selectedDiametroMp = $request->query('selected_diametro_mp');
        $selectedCodigoMp = $request->query('selected_codigo_mp');

        if (!$compactSelectorMode) {
            $redirectParams = [];
            if ($selectedOf !== null && $selectedOf !== '') {
                $redirectParams['of'] = $selectedOf;
            }

            return redirect()
                ->route('pedido_cliente_mp.createMassive', $redirectParams)
                ->with('info', 'La definicion de MP ahora se realiza desde la hoja masiva.');
        }

        return view('pedido_cliente_mp.create', array_merge(
            $this->formData($selectedOf),
            [
                'selectedOf' => $selectedOf,
                'compactSelectorMode' => $compactSelectorMode,
                'selectedMachine' => $selectedMachine,
                'massiveRowIndex' => $massiveRowIndex,
                'massiveReturnUrl' => $massiveReturnUrl,
                'massiveSelectionStorageKey' => $massiveSelectionStorageKey,
                'selectedIngreso' => $selectedIngreso,
                'selectedCertificado' => $selectedCertificado,
                'selectedPedidoMaterial' => $selectedPedidoMaterial,
                'selectedLongitudUnMp' => $selectedLongitudUnMp,
                'selectedMateriaPrima' => $selectedMateriaPrima,
                'selectedDiametroMp' => $selectedDiametroMp,
                'selectedCodigoMp' => $selectedCodigoMp,
            ]
        ));
    }

    public function createMassive(Request $request)
    {
        $this->normalizeInternalPedidoMaterialNumbers();
        return view('pedido_cliente_mp.mass-create', array_merge(
            $this->legacyPendingSummary(),
            $this->massiveCreateData(),
            [
                'preselectedOf' => $request->query('of'),
            ]
        ));
    }

    public function editGroup(Request $request)
    {
        $this->normalizeInternalPedidoMaterialNumbers();

        $pedidoMaterial = trim((string) $request->query('pedido_material', ''));

        if ($pedidoMaterial === '') {
            return redirect()->route('pedido_cliente_mp.index')
                ->with('warning', 'Debes indicar un Pedido MP Interno para editar el grupo.');
        }

        $registros = PedidoClienteMp::with(['pedido.producto.categoria'])
            ->whereNull('deleted_at')
            ->when(is_numeric($pedidoMaterial), function ($query) use ($pedidoMaterial) {
                $query->whereRaw('CAST(Pedido_Material_Nro AS UNSIGNED) = ?', [(int) $pedidoMaterial]);
            }, function ($query) use ($pedidoMaterial) {
                $query->where('Pedido_Material_Nro', $pedidoMaterial);
            })
            ->orderBy('Id_OF')
            ->get();

        if ($registros->isEmpty()) {
            return redirect()->route('pedido_cliente_mp.index')
                ->with('warning', 'No se encontraron registros para el Pedido MP Interno seleccionado.');
        }

        return view('pedido_cliente_mp.edit-group', [
            'pedidoMaterial' => $pedidoMaterial,
            'registros' => $registros,
        ]);
    }

    public function updateGroup(Request $request)
    {
        $ids = $request->input('ids', []);
        $pedidoMaterialNros = $request->input('pedido_material_nro', []);

        if (empty($ids) || empty($pedidoMaterialNros)) {
            return redirect()->route('pedido_cliente_mp.index')
                ->with('warning', 'No se recibieron filas para actualizar.');
        }

        DB::beginTransaction();

        try {
            $registros = PedidoClienteMp::whereIn('Id_Pedido_MP', $ids)
                ->whereNull('deleted_at')
                ->get()
                ->keyBy('Id_Pedido_MP');

            $actualizados = 0;

            foreach ($ids as $index => $id) {
                $registro = $registros->get((int) $id);
                if (!$registro) {
                    continue;
                }

                $nuevoPedido = trim((string) ($pedidoMaterialNros[$index] ?? ''));
                if ($nuevoPedido === '') {
                    continue;
                }

                if ((string) $registro->Pedido_Material_Nro === $nuevoPedido) {
                    continue;
                }

                $registro->Pedido_Material_Nro = $nuevoPedido;
                $registro->updated_by = Auth::id();
                $registro->save();
                $actualizados++;
            }

            DB::commit();

            return redirect()->route('pedido_cliente_mp.index')
                ->with('success', $actualizados > 0
                    ? 'Pedidos MP Internos actualizados correctamente.'
                    : 'No habia cambios para guardar en el grupo.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar grupo de pedido_cliente_mp', ['error' => $e->getMessage()]);

            return redirect()->route('pedido_cliente_mp.index')
                ->with('error', 'No se pudo actualizar el grupo de Pedido MP Interno.');
        }
    }
    public function planner(Request $request)
    {
        $pedido = PedidoCliente::with(['producto.categoria', 'producto.subCategoria'])
            ->whereNull('deleted_at')
            ->findOrFail($request->integer('id_of'));

        return response()->json(
            $this->plannerService->buildForPedido($pedido, $request->all())
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['Codigo_MP'] = $this->buildCodigoMp($validated['Codigo_MP'] ?? null, $validated['Materia_Prima'] ?? null, $validated['Diametro_MP'] ?? null);

        DB::beginTransaction();

        try {
            $validated['created_by'] = Auth::id();
            $validated['updated_by'] = Auth::id();
            PedidoClienteMp::create($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Definicion de materia prima creada correctamente.',
                'redirect' => route('pedido_cliente_mp.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear pedido_cliente_mp', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar la definicion de materia prima.',
            ], 400);
        }
    }

    public function storeMassive(Request $request)
    {
        $rows = $this->buildMassiveRows($request);

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'Debes completar al menos una fila valida para guardar la definicion masiva de MP.',
            ], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                PedidoClienteMp::create($row);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Definiciones masivas de materia prima creadas correctamente.',
                'redirect' => route('pedido_cliente_mp.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear pedido_cliente_mp masivo', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar la carga masiva de materia prima.',
            ], 400);
        }
    }

    public function show($id)
    {
        $this->normalizeInternalPedidoMaterialNumbers();
        $pedidoMp = PedidoClienteMp::with([
            'pedido.producto.categoria',
            'pedido.producto.subCategoria',
            'estadoPlanificacion',
        ])->withTrashed()->findOrFail($id);

        return view('pedido_cliente_mp.show', compact('pedidoMp'));
    }

    public function edit($id)
    {
        return redirect()
            ->route('pedido_cliente_mp.editMassive', $id)
            ->with('info', 'La edicion de MP ahora se realiza desde la hoja de edicion masiva.');
    }

    public function editMassive($id)
    {
        $this->normalizeInternalPedidoMaterialNumbers();

        $pedidoMp = PedidoClienteMp::with(['pedido.producto.categoria', 'pedido.producto.subCategoria'])
            ->whereNull('deleted_at')
            ->findOrFail($id);

        return view('pedido_cliente_mp.edit-massive', array_merge(
            $this->legacyPendingSummary(),
            $this->massiveCreateData(),
            [
                'pedidoMp' => $pedidoMp,
                'editMassiveSelectionStorageKey' => "pedidoClienteMpEditMassiveSelection_{$pedidoMp->Id_Pedido_MP}",
                'editMassiveDraftStorageKey' => "pedidoClienteMpEditMassiveDraft_{$pedidoMp->Id_Pedido_MP}",
            ]
        ));
    }

    public function updateMassive(Request $request, $id)
    {
        $pedidoMp = PedidoClienteMp::whereNull('deleted_at')->findOrFail($id);
        $pedido = PedidoCliente::with('producto')->whereNull('deleted_at')->findOrFail($pedidoMp->Id_OF);

        $validated = $request->validate([
            'Id_Maquina' => 'required|integer',
            'Nro_Ingreso_MP' => 'required|integer|min:1',
            'Estado_Plani_Id' => 'required|exists:estado_planificacion,Estado_Plani_Id',
            'Observaciones' => 'nullable|string',
        ], [
            'Id_Maquina.required' => 'Debes seleccionar una maquina.',
            'Nro_Ingreso_MP.required' => 'Debes seleccionar un ingreso MP.',
            'Estado_Plani_Id.required' => 'Debes indicar el estado de MP.',
        ]);

        $maquina = DB::table('maquinas_produc')
            ->select('id_maquina', 'Nro_maquina', 'familia_maquina', 'scrap_maquina')
            ->where('id_maquina', $validated['Id_Maquina'])
            ->where('Status', 1)
            ->first();

        $ingreso = DB::table('mp_ingreso')
            ->leftJoin('mp_materia_prima', 'mp_ingreso.Id_Materia_Prima', '=', 'mp_materia_prima.Id_Materia_Prima')
            ->leftJoin('mp_diametro', 'mp_ingreso.Id_Diametro_MP', '=', 'mp_diametro.Id_Diametro')
            ->whereNull('mp_ingreso.deleted_at')
            ->where('mp_ingreso.reg_Status', 1)
            ->where('mp_ingreso.Nro_Ingreso_MP', $validated['Nro_Ingreso_MP'])
            ->select([
                'mp_ingreso.Nro_Ingreso_MP',
                'mp_ingreso.Nro_Pedido',
                'mp_ingreso.Codigo_MP',
                'mp_ingreso.Nro_Certificado_MP',
                'mp_ingreso.Longitud_Unidad_MP',
                'mp_materia_prima.Nombre_Materia as Materia_Prima',
                'mp_diametro.Valor_Diametro as Diametro_MP',
            ])
            ->first();

        if (!$maquina || !$ingreso) {
            return redirect()
                ->route('pedido_cliente_mp.editMassive', $pedidoMp->Id_Pedido_MP)
                ->with('error', 'La maquina o el ingreso MP seleccionado no son validos.');
        }

        [$productoMateria, $productoDiametro] = $this->resolveMateriaDiametroForComparison(
            $pedido->producto->Prod_Codigo_MP ?? null,
            $pedido->producto->Prod_Material_MP ?? null,
            $pedido->producto->Prod_Diametro_de_MP ?? null
        );
        [$ingresoMateria, $ingresoDiametro] = $this->resolveMateriaDiametroForComparison(
            $ingreso->Codigo_MP ?? null,
            $ingreso->Materia_Prima ?? null,
            $ingreso->Diametro_MP ?? null
        );

        if (!$this->isCompatibleMpSelection($productoMateria, $productoDiametro, $ingresoMateria, $ingresoDiametro)) {
            return redirect()
                ->route('pedido_cliente_mp.editMassive', $pedidoMp->Id_Pedido_MP)
                ->with('error', 'El ingreso seleccionado no es compatible con la materia prima requerida por la OF.');
        }

        $plannerData = $this->plannerService->buildForPedido($pedido, [
            'Id_Maquina' => $validated['Id_Maquina'],
            'Codigo_MP' => $ingreso->Codigo_MP,
            'Longitud_Un_MP' => $ingreso->Longitud_Unidad_MP,
        ]);

        $payload = [
            'Estado_Plani_Id' => $validated['Estado_Plani_Id'],
            'Id_Maquina' => (int) $maquina->id_maquina,
            'Nro_Maquina' => $maquina->Nro_maquina,
            'Familia_Maquina' => $maquina->familia_maquina,
            'Scrap_Maquina' => $maquina->scrap_maquina,
            'Codigo_MP' => $plannerData['seleccion']['codigo_mp'],
            'Materia_Prima' => $plannerData['seleccion']['materia_prima'],
            'Diametro_MP' => $plannerData['seleccion']['diametro_mp'],
            'Nro_Ingreso_MP' => (int) $ingreso->Nro_Ingreso_MP,
            'Nro_Certificado_MP' => $ingreso->Nro_Certificado_MP,
            'Longitud_Un_MP' => $plannerData['seleccion']['longitud_un_mp'],
            'Largo_Pieza' => $plannerData['seleccion']['largo_pieza'],
            'Frenteado' => $plannerData['seleccion']['frenteado'],
            'Ancho_Cut_Off' => $plannerData['seleccion']['ancho_cut_off'],
            'Sobrematerial_Promedio' => $plannerData['seleccion']['sobrematerial_promedio'],
            'Largo_Total_Pieza' => $plannerData['seleccion']['largo_total_pieza'],
            'MM_Totales' => $plannerData['seleccion']['mm_totales'],
            'Longitud_Barra_Sin_Scrap' => $plannerData['seleccion']['longitud_barra_sin_scrap'],
            'Cant_Barras_MP' => $plannerData['seleccion']['cant_barras_requeridas'],
            'Cant_Piezas_Por_Barra' => $plannerData['seleccion']['cant_piezas_por_barra'],
            'Observaciones' => blank($validated['Observaciones'] ?? null) ? null : $validated['Observaciones'],
        ];

        return $this->updateIfChanged($pedidoMp, $payload, [
            'success_redirect' => route('pedido_cliente_mp.index'),
            'success_message' => 'Definicion de materia prima actualizada correctamente.',
            'no_changes_message' => 'No se detectaron cambios en la hoja de edicion.',
            'set_updated_by' => true,
            'use_transaction' => true,
            'normalize_data' => false,
        ]);
    }

    public function update(Request $request, $id)
    {
        $pedidoMp = PedidoClienteMp::findOrFail($id);
        $validated = $this->validateData($request, $id);
        $validated['Codigo_MP'] = $this->buildCodigoMp($validated['Codigo_MP'] ?? null, $validated['Materia_Prima'] ?? null, $validated['Diametro_MP'] ?? null);

        return $this->updateIfChanged($pedidoMp, $validated, [
            'success_redirect' => route('pedido_cliente_mp.index'),
            'success_message' => 'Definicion de materia prima actualizada correctamente.',
            'no_changes_message' => 'No se detectaron cambios.',
            'set_updated_by' => true,
            'use_transaction' => true,
            'normalize_data' => false,
        ]);
    }

    public function destroy($id)
    {
        try {
            $pedidoMp = PedidoClienteMp::findOrFail($id);
            $pedidoMp->deleted_by = Auth::id();
            $pedidoMp->save();
            $pedidoMp->delete();

            return response()->json([
                'success' => true,
                'message' => 'Definicion de materia prima enviada a eliminados.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar pedido_cliente_mp', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la definicion de materia prima.',
            ], 400);
        }
    }

    public function showDeleted()
    {
        $eliminados = PedidoClienteMp::onlyTrashed()->with([
            'pedido.producto',
            'estadoPlanificacion',
        ])->orderByDesc('Id_Pedido_MP')->get();

        return view('pedido_cliente_mp.deleted', compact('eliminados'));
    }

    public function restore($id)
    {
        try {
            $pedidoMp = PedidoClienteMp::onlyTrashed()->findOrFail($id);
            $pedidoMp->restore();

            return response()->json([
                'success' => true,
                'message' => 'Definicion de materia prima restaurada correctamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al restaurar pedido_cliente_mp', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo restaurar la definicion de materia prima.',
            ], 400);
        }
    }

    protected function formData($selectedOf = null)
    {
        $legacySummary = $this->legacyPendingSummary();
        $legacyMaxNroOf = $legacySummary['legacyMaxNroOf'];

        $pedidos = PedidoCliente::with(['producto.categoria', 'producto.subCategoria'])
            ->whereNull('deleted_at')
            ->where(function ($query) use ($selectedOf) {
                if ($selectedOf) {
                    $query->where('Id_OF', $selectedOf)
                        ->orWhere(function ($sub) {
                            $sub->whereDoesntHave('definicionMp')
                                ->whereNotExists(function ($legacy) {
                                    $legacy->select(DB::raw(1))
                                        ->from('listado_of as legacy_of')
                                        ->whereColumn('legacy_of.Nro_OF', 'pedido_cliente.Nro_OF');
                                });
                        });

                    return;
                }

                $query->whereDoesntHave('definicionMp')
                    ->whereNotExists(function ($legacy) {
                        $legacy->select(DB::raw(1))
                            ->from('listado_of as legacy_of')
                            ->whereColumn('legacy_of.Nro_OF', 'pedido_cliente.Nro_OF');
                    });
            })
            ->orderByDesc('Nro_OF')
            ->get();

        $estadosPlanificacion = EstadoPlanificacion::where('Status', 1)
            ->orderBy('Estado_Plani_Id')
            ->get(['Estado_Plani_Id', 'Nombre_Estado']);

        $stockRows = collect($this->plannerService->buildStockDashboard()['rows'] ?? []);

        $materiasPrimas = $stockRows
            ->pluck('Materia_Prima')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $diametros = $stockRows
            ->pluck('Diametro_MP')
            ->filter()
            ->unique(function ($value) {
                return trim((string) $value);
            })
            ->sortBy(function ($value) {
                return $this->extractDiameter($value) ?? PHP_INT_MAX;
            })
            ->values();

        $maquinas = DB::table('maquinas_produc')
            ->select('id_maquina', 'Nro_maquina', 'familia_maquina', 'scrap_maquina')
            ->where('Status', 1)
            ->orderBy('Nro_maquina')
            ->get();

        $nextPedidoMaterialNro = $this->nextPedidoMaterialNro();

        return compact('pedidos', 'estadosPlanificacion', 'materiasPrimas', 'diametros', 'maquinas', 'legacyMaxNroOf', 'nextPedidoMaterialNro');
    }

    protected function massiveCreateData(): array
    {
        $pedidos = PedidoCliente::with(['producto.categoria', 'producto.subCategoria'])
            ->whereNull('deleted_at')
            ->whereDoesntHave('definicionMp')
            ->whereNotExists(function ($legacy) {
                $legacy->select(DB::raw(1))
                    ->from('listado_of as legacy_of')
                    ->whereColumn('legacy_of.Nro_OF', 'pedido_cliente.Nro_OF');
            })
            ->orderBy('Nro_OF')
            ->get();

        $pedidosCatalogo = $pedidos->map(function ($pedido) {
            $producto = $pedido->producto;
            $codigoMpEsperado = $this->buildCodigoMp(
                $producto->Prod_Codigo_MP ?? null,
                $producto->Prod_Material_MP ?? null,
                $producto->Prod_Diametro_de_MP ?? null
            );

            return [
                'Id_OF' => $pedido->Id_OF,
                'Nro_OF' => $pedido->Nro_OF,
                'Prod_Codigo' => $producto->Prod_Codigo ?? '',
                'Prod_Descripcion' => $producto->Prod_Descripcion ?? '',
                'Cant_Fabricacion' => (int) ($pedido->Cant_Fabricacion ?? 0),
                'Largo_Pieza' => $producto->Prod_Longitud_de_Pieza ?? null,
                'Codigo_MP' => $codigoMpEsperado,
                'Materia_Prima' => $producto->Prod_Material_MP ?? '',
                'Diametro_MP' => $producto->Prod_Diametro_de_MP ?? '',
            ];
        })->values();

        $maquinasCatalogo = DB::table('maquinas_produc')
            ->select('id_maquina', 'Nro_maquina', 'familia_maquina', 'scrap_maquina')
            ->where('Status', 1)
            ->orderBy('Nro_maquina')
            ->get()
            ->map(fn ($maquina) => [
                'id_maquina' => (int) $maquina->id_maquina,
                'Nro_maquina' => $maquina->Nro_maquina,
                'familia_maquina' => $maquina->familia_maquina,
                'scrap_maquina' => (float) $maquina->scrap_maquina,
            ])->values();

        $ingresosCatalogo = collect($this->plannerService->buildStockDashboard()['rows'] ?? [])
            ->sortBy('Nro_Ingreso_MP')
            ->values()
            ->map(fn ($ingreso) => [
                'Nro_Ingreso_MP' => (int) ($ingreso['Nro_Ingreso_MP'] ?? 0),
                'Nro_Pedido' => $ingreso['Nro_Pedido_Proveedor'] ?? null,
                'Codigo_MP' => $ingreso['Codigo_MP'] ?? null,
                'Nro_Certificado_MP' => $ingreso['Nro_Certificado_MP'] ?? null,
                'Longitud_Unidad_MP' => isset($ingreso['Longitud_Unidad_MP']) ? (float) $ingreso['Longitud_Unidad_MP'] : null,
                'Unidades_MP' => (int) ($ingreso['Unidades_MP'] ?? 0),
                'Mts_Totales' => isset($ingreso['Mts_Totales']) ? (float) $ingreso['Mts_Totales'] : null,
                'Materia_Prima' => $ingreso['Materia_Prima'] ?? null,
                'Diametro_MP' => $ingreso['Diametro_MP'] ?? null,
            ]);

        $estadosPlanificacion = EstadoPlanificacion::where('Status', 1)
            ->orderBy('Estado_Plani_Id')
            ->get(['Estado_Plani_Id', 'Nombre_Estado']);

        $nextPedidoMaterialNro = $this->nextPedidoMaterialNro();

        return compact('pedidosCatalogo', 'maquinasCatalogo', 'ingresosCatalogo', 'estadosPlanificacion', 'nextPedidoMaterialNro');
    }

    protected function legacyPendingSummary(): array
    {
        $baseQuery = PedidoCliente::query()
            ->whereNull('deleted_at')
            ->whereDoesntHave('definicionMp')
            ->whereNotExists(function ($legacy) {
                $legacy->select(DB::raw(1))
                    ->from('listado_of as legacy_of')
                    ->whereColumn('legacy_of.Nro_OF', 'pedido_cliente.Nro_OF');
            });

        return [
            'legacyMaxNroOf' => (int) (DB::table('listado_of')->max('Nro_OF') ?? 0),
            'pendingOfCount' => (clone $baseQuery)->count(),
            'pendingMinNroOf' => (clone $baseQuery)->min('Nro_OF'),
            'pendingMaxNroOf' => (clone $baseQuery)->max('Nro_OF'),
        ];
    }

    protected function nextPedidoMaterialNro(): int
    {
        $this->normalizeInternalPedidoMaterialNumbers();

        $umbralPedidoProveedor = 9999;

        $maxPedidoDefinido = (int) (PedidoClienteMp::withTrashed()
            ->whereRaw('Pedido_Material_Nro REGEXP "^[0-9]+$"')
            ->whereRaw('CAST(Pedido_Material_Nro AS UNSIGNED) <= ?', [$umbralPedidoProveedor])
            ->selectRaw('MAX(CAST(Pedido_Material_Nro AS UNSIGNED)) as max_pedido')
            ->value('max_pedido') ?? 0);

        $maxPedidoEgreso = (int) (DB::table('mp_salidas')
            ->where('Nro_Pedido_MP', '<=', $umbralPedidoProveedor)
            ->max('Nro_Pedido_MP') ?? 0);

        return max($maxPedidoDefinido, $maxPedidoEgreso, 0) + 1;
    }

    protected function normalizeInternalPedidoMaterialNumbers(): void
    {
        if ($this->pedidoMaterialNormalizado) {
            return;
        }

        $this->pedidoMaterialNormalizado = true;
        $umbralPedidoProveedor = 9999;

        $gruposContaminados = DB::table('pedido_cliente_mp')
            ->whereRaw('Pedido_Material_Nro REGEXP "^[0-9]+$"')
            ->whereRaw('CAST(Pedido_Material_Nro AS UNSIGNED) > ?', [$umbralPedidoProveedor])
            ->selectRaw('Pedido_Material_Nro, MIN(Id_Pedido_MP) as first_id')
            ->groupBy('Pedido_Material_Nro')
            ->orderBy('first_id')
            ->get();

        if ($gruposContaminados->isEmpty()) {
            return;
        }

        $maxPedidoDefinido = (int) (PedidoClienteMp::withTrashed()
            ->whereRaw('Pedido_Material_Nro REGEXP "^[0-9]+$"')
            ->whereRaw('CAST(Pedido_Material_Nro AS UNSIGNED) <= ?', [$umbralPedidoProveedor])
            ->selectRaw('MAX(CAST(Pedido_Material_Nro AS UNSIGNED)) as max_pedido')
            ->value('max_pedido') ?? 0);

        $maxPedidoEgreso = (int) (DB::table('mp_salidas')
            ->where('Nro_Pedido_MP', '<=', $umbralPedidoProveedor)
            ->max('Nro_Pedido_MP') ?? 0);

        $ultimoCorrelativo = max($maxPedidoDefinido, $maxPedidoEgreso, 0);
        $ahora = now();
        $userId = Auth::id();

        foreach ($gruposContaminados as $grupo) {
            $ultimoCorrelativo++;

            DB::table('pedido_cliente_mp')
                ->where('Pedido_Material_Nro', $grupo->Pedido_Material_Nro)
                ->update([
                    'Pedido_Material_Nro' => (string) $ultimoCorrelativo,
                    'updated_at' => $ahora,
                    'updated_by' => $userId,
                ]);
        }
    }

    protected function validateData(Request $request, $id = null): array
    {
        $validated = $request->validate([
            'Id_OF' => [
                'required',
                'exists:pedido_cliente,Id_OF',
                Rule::unique('pedido_cliente_mp', 'Id_OF')->ignore($id, 'Id_Pedido_MP')->whereNull('deleted_at'),
            ],
            'Estado_Plani_Id' => 'required|exists:estado_planificacion,Estado_Plani_Id',
            'Id_Maquina' => 'nullable|integer',
            'Nro_Maquina' => 'nullable|string|max:50',
            'Familia_Maquina' => 'nullable|string|max:255',
            'Scrap_Maquina' => 'nullable|numeric|min:0',
            'Codigo_MP' => 'nullable|string|max:255',
            'Materia_Prima' => 'nullable|string|max:255',
            'Diametro_MP' => 'nullable|string|max:100',
            'Nro_Ingreso_MP' => 'nullable|integer|min:0',
            'Pedido_Material_Nro' => 'nullable|string|max:255',
            'Nro_Certificado_MP' => 'nullable|string|max:255',
            'Longitud_Un_MP' => 'nullable|numeric|min:0',
            'Largo_Pieza' => 'nullable|numeric|min:0',
            'Frenteado' => 'nullable|numeric|min:0',
            'Ancho_Cut_Off' => 'nullable|numeric|min:0',
            'Sobrematerial_Promedio' => 'nullable|numeric|min:0',
            'Largo_Total_Pieza' => 'nullable|numeric|min:0',
            'MM_Totales' => 'nullable|numeric|min:0',
            'Longitud_Barra_Sin_Scrap' => 'nullable|numeric|min:0',
            'Cant_Barras_MP' => 'nullable|integer|min:0',
            'Cant_Piezas_Por_Barra' => 'nullable|numeric|min:0',
            'Observaciones' => 'nullable|string',
            'reg_Status' => 'required|in:0,1',
        ], [
            'Id_OF.required' => 'Debes seleccionar una OF.',
            'Id_OF.unique' => 'Esta OF ya tiene una definicion de materia prima cargada.',
            'Estado_Plani_Id.required' => 'Debes indicar el estado de MP.',
        ]);

        $pedido = PedidoCliente::with('producto')->findOrFail($validated['Id_OF']);
        $producto = $pedido->producto;

        $productoCodigoMp = trim((string) ($producto->Prod_Codigo_MP ?? ''));
        $productoMateria = trim((string) ($producto->Prod_Material_MP ?? ''));
        $productoDiametro = trim((string) ($producto->Prod_Diametro_de_MP ?? ''));

        $materia = trim((string) ($validated['Materia_Prima'] ?? ''));
        $diametro = trim((string) ($validated['Diametro_MP'] ?? ''));
        $codigo = trim((string) ($validated['Codigo_MP'] ?? ''));
        $codigoIngresado = $codigo !== '' ? $codigo : ($materia !== '' && $diametro !== '' ? "{$materia}_{$diametro}" : '');

        [$productoMateriaComparada, $productoDiametroComparado] = $this->resolveMateriaDiametroForComparison($productoCodigoMp, $productoMateria, $productoDiametro);
        [$ingresoMateriaComparada, $ingresoDiametroComparado] = $this->resolveMateriaDiametroForComparison($codigoIngresado, $materia, $diametro);

        if (!$this->isCompatibleMpSelection($productoMateriaComparada, $productoDiametroComparado, $ingresoMateriaComparada, $ingresoDiametroComparado)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'Materia_Prima' => 'La materia prima debe coincidir con la compatibilidad definida en el producto.',
                'Diametro_MP' => 'El diametro del ingreso debe ser igual o mayor al requerido por el producto.',
            ]);
        }

        if (!empty($validated['Id_Maquina'])) {
            $maquina = DB::table('maquinas_produc')
                ->select('id_maquina', 'Nro_maquina', 'familia_maquina', 'scrap_maquina')
                ->where('id_maquina', $validated['Id_Maquina'])
                ->where('Status', 1)
                ->first();

            if (!$maquina) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'Id_Maquina' => 'La maquina seleccionada no existe en la base de datos.',
                ]);
            }

            $validated['Nro_Maquina'] = $maquina->Nro_maquina;
            $validated['Familia_Maquina'] = $maquina->familia_maquina;
            $validated['Scrap_Maquina'] = $maquina->scrap_maquina;
        } else {
            $validated['Nro_Maquina'] = null;
            $validated['Familia_Maquina'] = null;
            $validated['Scrap_Maquina'] = null;
        }

        $pedidoMaterialNro = trim((string) ($validated['Pedido_Material_Nro'] ?? ''));
        $validated['Pedido_Material_Nro'] = $pedidoMaterialNro !== ''
            ? $pedidoMaterialNro
            : (string) $this->nextPedidoMaterialNro();

        return $validated;
    }

    protected function buildMassiveRows(Request $request): array
    {
        $ofIds = $request->input('Id_OF', []);
        $machineIds = $request->input('Id_Maquina', []);
        $ingresoNumbers = $request->input('Nro_Ingreso_MP', []);
        $estadoIds = $request->input('Estado_Plani_Id', []);
        $observaciones = $request->input('Observaciones', []);
        $regStatuses = $request->input('reg_Status', []);

        $maxRows = max(
            count($ofIds),
            count($machineIds),
            count($ingresoNumbers),
            count($estadoIds)
        );

        $duplicatedRows = [];
        $invalidRows = [];
        $preparedRows = [];
        $seenOf = [];
        $pedidoMaterialNros = $request->input('Pedido_Material_Nro', []);
        $pedidoMaterialLote = null;

        for ($index = 0; $index < $maxRows; $index++) {
            $rowNumber = $index + 1;
            $idOf = $ofIds[$index] ?? null;
            $idMaquina = $machineIds[$index] ?? null;
            $nroIngreso = $ingresoNumbers[$index] ?? null;
            $estadoId = $estadoIds[$index] ?? 11;
            $observacion = $observaciones[$index] ?? null;
            $regStatus = $regStatuses[$index] ?? 1;
            $pedidoMaterialFila = trim((string) ($pedidoMaterialNros[$index] ?? ''));

            $isEmptyRow = blank($idOf) && blank($idMaquina) && blank($nroIngreso);
            if ($isEmptyRow) {
                continue;
            }

            if ($pedidoMaterialLote === null) {
                $pedidoMaterialLote = $pedidoMaterialFila !== ''
                    ? $pedidoMaterialFila
                    : (string) $this->nextPedidoMaterialNro();
            }

            if (blank($idOf) || blank($idMaquina) || blank($nroIngreso) || blank($estadoId)) {
                $invalidRows[] = $rowNumber;
                continue;
            }

            if (isset($seenOf[$idOf])) {
                $duplicatedRows[] = $rowNumber;
                $duplicatedRows[] = $seenOf[$idOf];
                continue;
            }
            $seenOf[$idOf] = $rowNumber;

            $pedido = PedidoCliente::with('producto')->whereNull('deleted_at')->find($idOf);
            $maquina = DB::table('maquinas_produc')
                ->select('id_maquina', 'Nro_maquina', 'familia_maquina', 'scrap_maquina')
                ->where('id_maquina', $idMaquina)
                ->where('Status', 1)
                ->first();
            $ingreso = DB::table('mp_ingreso')
                ->leftJoin('mp_materia_prima', 'mp_ingreso.Id_Materia_Prima', '=', 'mp_materia_prima.Id_Materia_Prima')
                ->leftJoin('mp_diametro', 'mp_ingreso.Id_Diametro_MP', '=', 'mp_diametro.Id_Diametro')
                ->whereNull('mp_ingreso.deleted_at')
                ->where('mp_ingreso.reg_Status', 1)
                ->where('mp_ingreso.Nro_Ingreso_MP', $nroIngreso)
                ->select([
                    'mp_ingreso.Nro_Ingreso_MP',
                    'mp_ingreso.Nro_Pedido',
                    'mp_ingreso.Codigo_MP',
                    'mp_ingreso.Nro_Certificado_MP',
                    'mp_ingreso.Longitud_Unidad_MP',
                    'mp_materia_prima.Nombre_Materia as Materia_Prima',
                    'mp_diametro.Valor_Diametro as Diametro_MP',
                ])
                ->first();

            if (!$pedido || !$pedido->producto || !$maquina || !$ingreso) {
                $invalidRows[] = $rowNumber;
                continue;
            }

            [$productoMateria, $productoDiametro] = $this->resolveMateriaDiametroForComparison(
                $pedido->producto->Prod_Codigo_MP ?? null,
                $pedido->producto->Prod_Material_MP ?? null,
                $pedido->producto->Prod_Diametro_de_MP ?? null
            );
            [$ingresoMateria, $ingresoDiametro] = $this->resolveMateriaDiametroForComparison(
                $ingreso->Codigo_MP ?? null,
                $ingreso->Materia_Prima ?? null,
                $ingreso->Diametro_MP ?? null
            );

            if (!$this->isCompatibleMpSelection($productoMateria, $productoDiametro, $ingresoMateria, $ingresoDiametro)) {
                $invalidRows[] = $rowNumber;
                continue;
            }

            if (PedidoClienteMp::where('Id_OF', $idOf)->whereNull('deleted_at')->exists()) {
                $duplicatedRows[] = $rowNumber;
                continue;
            }

            $plannerData = $this->plannerService->buildForPedido($pedido, [
                'Id_Maquina' => $idMaquina,
                'Codigo_MP' => $ingreso->Codigo_MP,
                'Longitud_Un_MP' => $ingreso->Longitud_Unidad_MP,
            ]);

            $preparedRows[] = [
                'Id_OF' => $pedido->Id_OF,
                'Estado_Plani_Id' => $estadoId,
                'Id_Maquina' => (int) $maquina->id_maquina,
                'Nro_Maquina' => $maquina->Nro_maquina,
                'Familia_Maquina' => $maquina->familia_maquina,
                'Scrap_Maquina' => $maquina->scrap_maquina,
                'Codigo_MP' => $plannerData['seleccion']['codigo_mp'],
                'Materia_Prima' => $plannerData['seleccion']['materia_prima'],
                'Diametro_MP' => $plannerData['seleccion']['diametro_mp'],
                'Nro_Ingreso_MP' => (int) $ingreso->Nro_Ingreso_MP,
                'Pedido_Material_Nro' => $pedidoMaterialLote,
                'Nro_Certificado_MP' => $ingreso->Nro_Certificado_MP,
                'Longitud_Un_MP' => $plannerData['seleccion']['longitud_un_mp'],
                'Largo_Pieza' => $plannerData['seleccion']['largo_pieza'],
                'Frenteado' => $plannerData['seleccion']['frenteado'],
                'Ancho_Cut_Off' => $plannerData['seleccion']['ancho_cut_off'],
                'Sobrematerial_Promedio' => $plannerData['seleccion']['sobrematerial_promedio'],
                'Largo_Total_Pieza' => $plannerData['seleccion']['largo_total_pieza'],
                'MM_Totales' => $plannerData['seleccion']['mm_totales'],
                'Longitud_Barra_Sin_Scrap' => $plannerData['seleccion']['longitud_barra_sin_scrap'],
                'Cant_Barras_MP' => $plannerData['seleccion']['cant_barras_requeridas'],
                'Cant_Piezas_Por_Barra' => $plannerData['seleccion']['cant_piezas_por_barra'],
                'Observaciones' => blank($observacion) ? null : $observacion,
                'reg_Status' => (int) $regStatus,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
        }

        $duplicatedRows = array_values(array_unique($duplicatedRows));
        $invalidRows = array_values(array_unique($invalidRows));

        if (!empty($duplicatedRows)) {
            abort(response()->json([
                'success' => false,
                'message' => 'Hay OF duplicadas en la carga masiva o ya definidas en la base.',
                'duplicatedRows' => $duplicatedRows,
            ], 400));
        }

        if (!empty($invalidRows)) {
            abort(response()->json([
                'success' => false,
                'message' => 'Hay filas invalidas o incompatibles. Revisa OF, maquina e ingreso MP.',
                'invalidRows' => $invalidRows,
            ], 400));
        }

        return $preparedRows;
    }

    protected function buildCodigoMp(?string $codigo, ?string $materia, ?string $diametro): ?string
    {
        if ($codigo) {
            return trim($codigo);
        }

        if ($materia && $diametro) {
            return trim($materia) . '_' . trim($diametro);
        }

        return null;
    }

    protected function normalizeCodigoMp(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/', '', trim(mb_strtoupper($value)));
        return $normalized === '' ? null : $normalized;
    }

    protected function splitCodigoMp(?string $codigoMp): array
    {
        $codigoMp = trim((string) $codigoMp);
        if ($codigoMp === '' || !str_contains($codigoMp, '_')) {
            return [null, null];
        }

        $parts = explode('_', $codigoMp, 2);
        return [trim($parts[0]) ?: null, trim($parts[1]) ?: null];
    }

    protected function resolveMateriaDiametroForComparison(?string $codigoMp, ?string $materiaPrima, ?string $diametroMp): array
    {
        [$materiaFromCode, $diametroFromCode] = $this->splitCodigoMp($codigoMp);

        return [
            $materiaFromCode ?: trim((string) $materiaPrima),
            $diametroFromCode ?: trim((string) $diametroMp),
        ];
    }

    protected function extractDiameter(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (preg_match('/[ÃƒËœÃƒÂ¸]?\s*([0-9]+(?:[\.,][0-9]+)?)/u', $value, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }

        return null;
    }

    protected function isCompatibleMpSelection(?string $productoMateria, ?string $productoDiametro, ?string $ingresoMateria, ?string $ingresoDiametro): bool
    {
        $productoMateria = trim((string) $productoMateria);
        $productoDiametro = trim((string) $productoDiametro);
        $ingresoMateria = trim((string) $ingresoMateria);
        $ingresoDiametro = trim((string) $ingresoDiametro);

        if ($productoMateria !== '' && $ingresoMateria !== '' && $productoMateria !== $ingresoMateria) {
            return false;
        }

        $diametroProducto = $this->extractDiameter($productoDiametro);
        $diametroIngreso = $this->extractDiameter($ingresoDiametro);

        if ($diametroProducto === null || $diametroIngreso === null) {
            return true;
        }

        return $diametroIngreso >= $diametroProducto;
    }
}






