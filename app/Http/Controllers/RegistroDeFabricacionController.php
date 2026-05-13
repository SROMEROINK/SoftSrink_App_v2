<?php
//app\Http\Controllers\RegistroDeFabricacionController.php
namespace App\Http\Controllers;

use App\Models\RegistroDeFabricacion;
use App\Services\HistoricalRegistroFabricacionImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class RegistroDeFabricacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only('index', 'showByNroOF', 'show', 'getData', 'resumen', 'resumenMensualFamilias');
        $this->middleware('permission:editar produccion')->only(['create', 'store', 'edit', 'update', 'importHistoricCsv']);
        $this->middleware('permission:eliminar registros')->only('destroy');
    }

    protected function getFabricacionMonthLabels(): array
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    }

    protected function buildMonthlyFamilySummaryQuery(Request $request)
    {
        $query = DB::table('registro_de_fabricacion as rf')
            ->leftJoin('pedido_cliente as pc', 'pc.Id_OF', '=', 'rf.Nro_OF')
            ->leftJoin('pedido_cliente_mp as pm', function ($join) {
                $join->on('pm.Id_OF', '=', 'pc.Id_OF')
                    ->whereNull('pm.deleted_at');
            })
            ->whereNotNull('rf.Fecha_Fabricacion');

        if ($request->filled('filtro_anio')) {
            $query->whereYear('rf.Fecha_Fabricacion', (int) $request->input('filtro_anio'));
        }

        if ($request->filled('filtro_mes')) {
            $query->whereMonth('rf.Fecha_Fabricacion', (int) $request->input('filtro_mes'));
        }

        if ($request->filled('filtro_familia')) {
            $query->where('pm.Familia_Maquina', $request->input('filtro_familia'));
        }

        return $query;
    }

    protected function buildFilteredQuery(Request $request)
    {
        $registrosFabricacion = RegistroDeFabricacion::with([
            'producto.categoria',
            'pedido.definicionMp',
            'creator',
            'updater',
        ])->select(
            'Id_OF',
            'Nro_OF',
            'Id_Producto',
            'Nro_Parcial',
            'Cant_Piezas',
            'Fecha_Fabricacion',
            'Horario',
            'Nombre_Operario',
            'Turno',
            'Cant_Horas_Extras',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by'
        );

        if ($request->filled('filtro_id_of')) {
            $registrosFabricacion->where('Id_OF', (int) $request->filtro_id_of);
        }

        if ($request->filled('filtro_nro_of')) {
            $registrosFabricacion->whereHas('pedido', function ($query) use ($request) {
                $query->where('Nro_OF', $request->filtro_nro_of);
            });
        }

        if ($request->filled('filtro_prod_codigo')) {
            $registrosFabricacion->whereHas('producto', function ($query) use ($request) {
                $query->where('Prod_Codigo', 'like', '%' . $request->filtro_prod_codigo . '%');
            });
        }

        if ($request->filled('filtro_prod_descripcion')) {
            $registrosFabricacion->whereHas('producto', function ($query) use ($request) {
                $query->where('Prod_Descripcion', 'like', '%' . $request->filtro_prod_descripcion . '%');
            });
        }

        if ($request->filled('filtro_categoria')) {
            $registrosFabricacion->whereHas('producto.categoria', function ($query) use ($request) {
                $query->where('Nombre_Categoria', $request->filtro_categoria);
            });
        }

        if ($request->filled('filtro_maquina')) {
            $registrosFabricacion->whereHas('pedido.definicionMp', function ($query) use ($request) {
                $query->where('Nro_Maquina', $request->filtro_maquina);
            });
        }

        if ($request->filled('filtro_familia')) {
            $registrosFabricacion->whereHas('pedido.definicionMp', function ($query) use ($request) {
                $query->where('Familia_Maquina', $request->filtro_familia);
            });
        }

        if ($request->filled('filtro_fecha_fabricacion')) {
            $registrosFabricacion->whereDate('Fecha_Fabricacion', $request->filtro_fecha_fabricacion);
        }

        if ($request->filled('filtro_nro_parcial')) {
            $registrosFabricacion->where('Nro_Parcial', $request->filtro_nro_parcial);
        }

        if ($request->filled('filtro_cant_piezas')) {
            $registrosFabricacion->where('Cant_Piezas', $request->filtro_cant_piezas);
        }

        if ($request->filled('filtro_horario')) {
            $registrosFabricacion->where('Horario', $request->filtro_horario);
        }

        if ($request->filled('filtro_nombre_operario')) {
            $registrosFabricacion->where('Nombre_Operario', $request->filtro_nombre_operario);
        }

        if ($request->filled('filtro_turno')) {
            $registrosFabricacion->where('Turno', $request->filtro_turno);
        }

        if ($request->filled('filtro_cant_horas_extras')) {
            $registrosFabricacion->where('Cant_Horas_Extras', $request->filtro_cant_horas_extras);
        }

        if ($request->filled('filtro_nro_of') && !$request->filled('filtro_id_of')) {
            $registrosFabricacion
                ->orderBy('Nro_Parcial', 'asc')
                ->orderBy('Fecha_Fabricacion', 'asc');
        } else {
            $registrosFabricacion->orderByDesc('Id_OF');
        }

        return $registrosFabricacion;
    }

    public function getData(Request $request)
    {
        try {
            if ($request->ajax()) {
                $registrosFabricacion = $this->buildFilteredQuery($request);

                return DataTables::eloquent($registrosFabricacion)
                    ->addColumn('Nro_OF_Visual', function ($registro) {
                        return $registro->pedido->Nro_OF ?? $registro->Nro_OF;
                    })
                    ->addColumn('Prod_Codigo', function ($registro) {
                        return $registro->producto->Prod_Codigo ?? '';
                    })
                    ->addColumn('Prod_Descripcion', function ($registro) {
                        return $registro->producto->Prod_Descripcion ?? '';
                    })
                    ->addColumn('Nombre_Categoria', function ($registro) {
                        return $registro->producto->categoria->Nombre_Categoria ?? '';
                    })
                    ->addColumn('Nro_Maquina', function ($registro) {
                        return $registro->pedido->definicionMp->Nro_Maquina ?? '';
                    })
                    ->addColumn('Familia_Maquinas', function ($registro) {
                        return $registro->pedido->definicionMp->Familia_Maquina ?? '';
                    })
                    ->addColumn('creator', function ($registro) {
                        return $registro->creator->name ?? '';
                    })
                    ->addColumn('updater', function ($registro) {
                        return $registro->updater->name ?? '';
                    })
                    ->editColumn('created_at', function ($registro) {
                        return $registro->created_at ? $registro->created_at->format('Y-m-d H:i:s') : '';
                    })
                    ->editColumn('updated_at', function ($registro) {
                        return $registro->updated_at ? $registro->updated_at->format('Y-m-d H:i:s') : '';
                    })
                    ->editColumn('Fecha_Fabricacion', function ($registro) {
                        return $registro->Fecha_Fabricacion ? $registro->Fecha_Fabricacion->format('d/m/Y') : '';
                    })
                    ->make(true);
            }
        } catch (\Exception $e) {
            Log::error('Error in getData: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching data'], 500);
        }
    }

    public function resumen(Request $request)
    {
        try {
            $query = $this->buildFilteredQuery($request);

            return response()->json([
                'total_registros' => (clone $query)->count(),
                'total_piezas' => (int) ((clone $query)->sum('Cant_Piezas') ?? 0),
                'total_of' => (clone $query)->select('Nro_OF')->distinct()->count('Nro_OF'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in fabricacion resumen: ' . $e->getMessage());

            return response()->json([
                'total_registros' => 0,
                'total_piezas' => 0,
                'total_of' => 0,
            ], 500);
        }
    }

    public function index(HistoricalRegistroFabricacionImportService $historicalImportService)
    {
        $csvHistoricoDisponible = $historicalImportService->csvExists();
        $pendingHistoricalRows = $csvHistoricoDisponible ? $historicalImportService->pendingImportCount() : 0;
        $historicoImportado = true;
        $puedeImportarHistorico = false;

        return view('fabricacion.index', compact(
            'csvHistoricoDisponible',
            'pendingHistoricalRows',
            'historicoImportado',
            'puedeImportarHistorico'
        ));
    }

    public function resumenMensualFamilias(Request $request)
    {
        $fabRes = DB::table('registro_de_fabricacion as rf')
            ->selectRaw('rf.Nro_OF')
            ->selectRaw('MIN(rf.Fecha_Fabricacion) as Fecha_Inicio_Fabricacion')
            ->selectRaw('MAX(rf.Fecha_Fabricacion) as Fecha_Fin_Fabricacion')
            ->selectRaw('SUM(rf.Cant_Piezas) as Piezas_Fabricadas')
            ->selectRaw('COUNT(*) as Parciales_Registrados')
            ->whereNotNull('rf.Fecha_Fabricacion');

        if ($request->filled('filtro_anio')) {
            $fabRes->whereYear('rf.Fecha_Fabricacion', (int) $request->input('filtro_anio'));
        }

        if ($request->filled('filtro_mes')) {
            $fabRes->whereMonth('rf.Fecha_Fabricacion', (int) $request->input('filtro_mes'));
        }

        $fabRes->groupBy('rf.Nro_OF');

        $entRes = DB::table('listado_entregas_productos as lep')
            ->selectRaw('lep.Id_OF')
            ->selectRaw('SUM(lep.Cant_Piezas_Entregadas) as Piezas_Entregadas_Mes')
            ->whereNotNull('lep.Fecha_Entrega_Calidad');

        if ($request->filled('filtro_anio')) {
            $entRes->whereYear('lep.Fecha_Entrega_Calidad', (int) $request->input('filtro_anio'));
        }

        if ($request->filled('filtro_mes')) {
            $entRes->whereMonth('lep.Fecha_Entrega_Calidad', (int) $request->input('filtro_mes'));
        }

        $entRes->groupBy('lep.Id_OF');

        $rowsQuery = DB::table('pedido_cliente as pc')
            ->joinSub($fabRes, 'fab_res', function ($join) {
                $join->on('pc.Id_OF', '=', 'fab_res.Nro_OF');
            })
            ->leftJoinSub($entRes, 'ent_res', function ($join) {
                $join->on('pc.Id_OF', '=', 'ent_res.Id_OF');
            })
            ->join('productos as p', 'pc.Producto_Id', '=', 'p.Id_Producto')
            ->leftJoin('producto_categoria as cat', 'p.Id_Prod_Categoria', '=', 'cat.Id_Categoria')
            ->leftJoin('pedido_cliente_mp as pm', function ($join) {
                $join->on('pm.Id_OF', '=', 'pc.Id_OF')
                    ->whereNull('pm.deleted_at');
            })
            ->select([
                'pc.Id_OF',
                'pc.Nro_OF',
                'p.Prod_Codigo',
                'p.Prod_Descripcion',
                'cat.Nombre_Categoria',
                'pm.Nro_Maquina',
                'pm.Familia_Maquina',
                'pc.Fecha_del_Pedido',
                DB::raw('pc.Cant_Fabricacion as Cant_Pedido'),
                'fab_res.Fecha_Inicio_Fabricacion',
                  'fab_res.Fecha_Fin_Fabricacion',
                  'fab_res.Piezas_Fabricadas',
                  'fab_res.Parciales_Registrados',
                  DB::raw('COALESCE(ent_res.Piezas_Entregadas_Mes, 0) as Piezas_Entregadas_Mes'),
              ]);

        if ($request->filled('filtro_familia')) {
            $rowsQuery->where('pm.Familia_Maquina', $request->input('filtro_familia'));
        }

        if ($request->filled('filtro_nro_of')) {
            $rowsQuery->where('pc.Nro_OF', (int) $request->input('filtro_nro_of'));
        }

        if ($request->filled('filtro_categoria')) {
            $rowsQuery->where('cat.Nombre_Categoria', $request->input('filtro_categoria'));
        }

        $rows = (clone $rowsQuery)
            ->orderByDesc('fab_res.Fecha_Fin_Fabricacion')
            ->orderByDesc('pc.Nro_OF')
            ->paginate((int) $request->input('per_page', 25))
            ->withQueryString();

        $summary = [
            'total_piezas' => (int) ((clone $rowsQuery)->sum('fab_res.Piezas_Fabricadas') ?? 0),
            'total_parciales' => (int) ((clone $rowsQuery)->sum('fab_res.Parciales_Registrados') ?? 0),
            'total_of' => (int) ((clone $rowsQuery)->count() ?? 0),
        ];

        $years = Cache::remember('fabricacion.resumen_mensual.years', now()->addMinutes(30), function () {
            return DB::table('registro_de_fabricacion')
                ->whereNotNull('Fecha_Fabricacion')
                ->selectRaw('YEAR(Fecha_Fabricacion) as anio')
                ->distinct()
                ->orderByDesc('anio')
                ->pluck('anio')
                ->filter()
                ->values();
        });

        $familias = Cache::remember('fabricacion.resumen_mensual.familias', now()->addMinutes(30), function () {
            return DB::table('registro_de_fabricacion as rf')
                ->leftJoin('pedido_cliente as pc', 'pc.Id_OF', '=', 'rf.Nro_OF')
                ->leftJoin('pedido_cliente_mp as pm', function ($join) {
                    $join->on('pm.Id_OF', '=', 'pc.Id_OF')
                        ->whereNull('pm.deleted_at');
                })
                ->selectRaw("COALESCE(NULLIF(pm.Familia_Maquina, ''), 'Sin familia') as familia")
                ->distinct()
                ->orderBy('familia')
                ->pluck('familia')
                ->filter()
                ->values();
        });

        $categorias = Cache::remember('fabricacion.resumen_mensual.categorias', now()->addMinutes(30), function () {
            return DB::table('productos as p')
                ->leftJoin('producto_categoria as cat', 'p.Id_Prod_Categoria', '=', 'cat.Id_Categoria')
                ->whereNotNull('cat.Nombre_Categoria')
                ->select('cat.Nombre_Categoria')
                ->distinct()
                ->orderBy('cat.Nombre_Categoria')
                ->pluck('cat.Nombre_Categoria')
                ->filter()
                ->values();
        });

        return view('fabricacion.resumen_mensual', [
            'rows' => $rows,
            'summary' => $summary,
            'years' => $years,
            'familias' => $familias,
            'categorias' => $categorias,
            'months' => $this->getFabricacionMonthLabels(),
        ]);
    }

    public function importHistoricCsv(HistoricalRegistroFabricacionImportService $historicalImportService)
    {
        return redirect()->route('fabricacion.index')->with('warning', 'La importacion historica ya quedo cerrada para evitar duplicados.');
    }

    public function create()
    {
        return view('fabricacion.create');
    }

    public function checkNroOFParcial(Request $request)
    {
        $nroOfParcial = $request->input('Nro_OF_Parcial');
        $registroExistente = RegistroDeFabricacion::where('Nro_OF_Parcial', $nroOfParcial)->exists();

        return response()->json(['exists' => $registroExistente]);
    }

    public function store(Request $request)
    {
        $messages = [
            'nro_of.*.required' => 'El numero de OF es obligatorio.',
            'nro_of.*.integer' => 'El numero de OF debe ser un numero entero.',
            'nro_of.*.min' => 'El numero de OF no puede ser negativo.',
            'nro_parcial.*.required' => 'El numero de parcial es obligatorio.',
            'Nro_OF_Parcial.*.unique' => 'El numero de parcial ya ha sido registrado.',
            'cant_piezas.*.required' => 'La cantidad de piezas es obligatoria.',
            'cant_piezas.*.numeric' => 'La cantidad de piezas debe ser un numero.',
            'fecha_fabricacion.*.required' => 'La fecha de fabricacion es obligatoria.',
            'fecha_fabricacion.*.date' => 'La fecha de fabricacion no tiene un formato valido.',
            'horario.*.required' => 'El horario es obligatorio.',
            'operario.*.nullable' => 'El nombre del operario es obligatorio.',
            'turno.*.required' => 'El turno es obligatorio.',
            'cant_horas.*.required' => 'La cantidad de horas es obligatoria.',
            'cant_horas.*.numeric' => 'La cantidad de horas debe ser un numero.',
        ];

        $request->validate([
            'nro_of.*' => 'required|integer|min:0',
            'Id_Producto.*' => 'required',
            'nro_parcial.*' => 'required',
            'Nro_OF_Parcial.*' => 'required|unique:registro_de_fabricacion,Nro_OF_Parcial',
            'cant_piezas.*' => 'required|numeric',
            'fecha_fabricacion.*' => 'required|date',
            'horario.*' => 'required',
            'operario.*' => 'nullable|string|max:255',
            'turno.*' => 'required',
            'cant_horas.*' => 'required|numeric',
        ], $messages);

        if (empty($request->nro_of)) {
            return response()->json(['success' => false, 'message' => 'El numero de OF es obligatorio.'], 400);
        }

        $duplicatedRows = [];
        $incompleteRows = [];
        $savedRows = 0;

        DB::beginTransaction();

        try {
            foreach ($request->nro_of as $index => $nroOf) {
                if (!isset(
                    $request->Id_Producto[$index],
                    $request->nro_parcial[$index],
                    $request->cant_piezas[$index],
                    $request->fecha_fabricacion[$index],
                    $request->horario[$index],
                    $request->turno[$index],
                    $request->cant_horas[$index]
                )) {
                    $incompleteRows[] = $index + 1;
                    continue;
                }

                $nroOfParcial = $nroOf . '/' . $request->nro_parcial[$index];

                if (RegistroDeFabricacion::where('Nro_OF_Parcial', $nroOfParcial)->exists()) {
                    $duplicatedRows[] = $index + 1;
                    continue;
                }

                $registro = new RegistroDeFabricacion();
                $registro->Nro_OF = $nroOf;
                $registro->Id_Producto = $request->Id_Producto[$index];
                $registro->Nro_Parcial = $request->nro_parcial[$index];
                $registro->Nro_OF_Parcial = $nroOfParcial;
                $registro->Cant_Piezas = $request->cant_piezas[$index];
                $registro->Fecha_Fabricacion = $request->fecha_fabricacion[$index];
                $registro->Horario = $request->horario[$index];
                $registro->Turno = $request->turno[$index];
                $registro->Cant_Horas_Extras = $request->cant_horas[$index];
                $registro->created_by = Auth::id();
                $registro->updated_by = Auth::id();
                $registro->Nombre_Operario = $request->horario[$index] === 'H.Normales'
                    ? null
                    : ($request->operario[$index] ?? null);

                $registro->save();
                $savedRows++;
            }

            if (!empty($duplicatedRows) || !empty($incompleteRows)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Algunas filas no pudieron guardarse.',
                    'duplicatedRows' => $duplicatedRows,
                    'incompleteRows' => $incompleteRows,
                ], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Datos guardados correctamente.',
                'savedRows' => $savedRows,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error al guardar fabricacion', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error al guardar la fabricacion. Revisar log.',
            ], 500);
        }
    }

    public function showByNroOF($nroOF)
    {
        $registros = RegistroDeFabricacion::with('pedido')
            ->where('Nro_OF', $nroOF)
            ->orderBy('Nro_Parcial', 'asc')
            ->get();
        $totalCantPiezas = $registros->sum('Cant_Piezas');

        if ($registros->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron registros con ese numero de OF.');
        }

        return view('fabricacion.show', compact('registros', 'totalCantPiezas'));
    }

    public function edit($id)
    {
        $registro_fabricacion = RegistroDeFabricacion::findOrFail($id);
        return view('fabricacion.edit', compact('registro_fabricacion'));
    }

    public function update(Request $request, string $Id_OF)
    {
        Log::info('Datos recibidos para la actualizacion:', $request->all());

        $registro_fabricacion = RegistroDeFabricacion::find($Id_OF);
        if (!$registro_fabricacion) {
            Log::error('No se encontro el registro especificado.', ['Id_OF' => $Id_OF]);
            return response()->json(['status' => 'error', 'message' => 'No se encontro el registro especificado.'], 404);
        }

        $nuevoNroOFParcial = $request->Nro_OF . '/' . $request->Nro_Parcial;

        $existe = RegistroDeFabricacion::where('Nro_OF_Parcial', $nuevoNroOFParcial)
            ->where('Id_OF', '!=', $Id_OF)
            ->exists();

        if ($existe) {
            Log::error('El Nro OF Parcial ya existe.', ['Nro_OF_Parcial' => $nuevoNroOFParcial]);
            return response()->json(['status' => 'error', 'message' => 'El Nro OF Parcial ya existe.'], 400);
        }

        $cambios = [
            'Nro_OF' => $request->Nro_OF,
            'Nro_Parcial' => $request->Nro_Parcial,
            'Nro_OF_Parcial' => $nuevoNroOFParcial,
            'Cant_Piezas' => $request->Cant_Piezas,
            'Fecha_Fabricacion' => $request->Fecha_Fabricacion,
            'Horario' => $request->Horario,
            'Nombre_Operario' => $request->Nombre_Operario,
            'Turno' => $request->Turno,
            'Cant_Horas_Extras' => $request->Cant_Horas_Extras,
        ];

        $hayCambios = false;
        foreach ($cambios as $campo => $valor) {
            if ($registro_fabricacion->$campo != $valor) {
                $hayCambios = true;
                break;
            }
        }

        if (!$hayCambios) {
            return response()->json(['status' => 'warning', 'message' => 'No se realizaron cambios en el registro.'], 200);
        }

        $registro_fabricacion->Nro_OF = $request->Nro_OF;
        $registro_fabricacion->Nro_Parcial = $request->Nro_Parcial;
        $registro_fabricacion->Nro_OF_Parcial = $nuevoNroOFParcial;
        $registro_fabricacion->Cant_Piezas = $request->Cant_Piezas;
        $registro_fabricacion->Fecha_Fabricacion = $request->Fecha_Fabricacion;
        $registro_fabricacion->Horario = $request->Horario;
        $registro_fabricacion->Nombre_Operario = $request->Nombre_Operario;
        $registro_fabricacion->Turno = $request->Turno;
        $registro_fabricacion->Cant_Horas_Extras = $request->Cant_Horas_Extras;
        $registro_fabricacion->updated_by = Auth::id();
        $registro_fabricacion->save();

        return response()->json(['status' => 'success', 'message' => 'Registro actualizado correctamente.']);
    }

    public function destroy(string $Id_OF)
    {
        try {
            $registro_fabricacion = RegistroDeFabricacion::findOrFail($Id_OF);
            $nroOF = $registro_fabricacion->Nro_OF;
            $registro_fabricacion->delete();

            $remaining = RegistroDeFabricacion::where('Nro_OF', $nroOF)->count();

            if ($remaining > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Registro eliminado correctamente.',
                    'redirect' => route('fabricacion.showByNroOF', ['nroOF' => $nroOF]),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Todos los registros eliminados. Creando nuevo.',
                'redirect' => route('fabricacion.create'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro.']);
        }
    }
}
