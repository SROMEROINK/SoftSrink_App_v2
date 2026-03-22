<?php

namespace App\Http\Controllers;

use App\Models\MpIngreso;
use App\Models\MpSalidaInicial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpSalidaInicialController extends Controller
{
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

    public function getData(Request $request)
    {
        $query = DB::table('mp_ingreso as i')
            ->leftJoin('mp_salidas_iniciales as si', function ($join) {
                $join->on('si.Id_Ingreso_MP', '=', 'i.Id_MP')
                    ->whereNull('si.deleted_at');
            })
            ->leftJoin('mp_materia_prima as m', 'm.Id_Materia_Prima', '=', 'i.Id_Materia_Prima')
            ->leftJoin('mp_diametro as d', 'd.Id_Diametro', '=', 'i.Id_Diametro_MP')
            ->whereNull('i.deleted_at')
            ->select([
                'i.Id_MP',
                'i.Nro_Ingreso_MP',
                'i.Codigo_MP',
                'i.Nro_Certificado_MP',
                'i.Unidades_MP as Unidades_Ingresadas',
                'i.Longitud_Unidad_MP as Longitud_Ingreso',
                'i.Mts_Totales as Metros_Ingreso',
                'm.Nombre_Materia as Materia_Prima',
                'd.Valor_Diametro as Diametro_MP',
                'si.Id_Ingreso_MP as Ajuste_Id',
                'si.Cantidad_Unidades_MP',
                'si.Cantidad_Unidades_MP_Preparadas',
                'si.Cantidad_MP_Adicionales',
                'si.Total_Salidas_MP',
                'si.Devoluciones_Unidades_MP',
                'si.Total_Unidades',
                'si.Longitud_Unidad_MP',
                'si.Total_mm_Utilizados',
                'si.reg_Status',
                DB::raw("CASE WHEN si.Id_Ingreso_MP IS NULL THEN 'PENDIENTE AJUSTE' ELSE 'AJUSTE CARGADO' END AS Estado_Ajuste"),
            ])
            ->orderBy('i.Nro_Ingreso_MP', 'asc');

        if ($request->filled('filtro_ingreso')) {
            $query->where('i.Nro_Ingreso_MP', 'like', '%' . $request->filtro_ingreso . '%');
        }

        if ($request->filled('filtro_codigo')) {
            $query->where('i.Codigo_MP', 'like', '%' . $request->filtro_codigo . '%');
        }

        if ($request->filled('filtro_materia')) {
            $query->whereRaw('LOWER(m.Nombre_Materia) = ?', [mb_strtolower($request->filtro_materia)]);
        }

        if ($request->filled('filtro_estado')) {
            if ($request->filtro_estado === 'PENDIENTE AJUSTE') {
                $query->whereNull('si.Id_Ingreso_MP');
            } elseif ($request->filtro_estado === 'AJUSTE CARGADO') {
                $query->whereNotNull('si.Id_Ingreso_MP');
            }
        }

        return datatables()->of($query)
            ->editColumn('Unidades_Ingresadas', fn ($row) => number_format((int) $row->Unidades_Ingresadas, 0, ',', '.'))
            ->editColumn('Cantidad_Unidades_MP_Preparadas', fn ($row) => $row->Cantidad_Unidades_MP_Preparadas !== null ? number_format((int) $row->Cantidad_Unidades_MP_Preparadas, 0, ',', '.') : '')
            ->editColumn('Cantidad_MP_Adicionales', fn ($row) => $row->Cantidad_MP_Adicionales !== null ? number_format((int) $row->Cantidad_MP_Adicionales, 0, ',', '.') : '')
            ->editColumn('Devoluciones_Unidades_MP', fn ($row) => $row->Devoluciones_Unidades_MP !== null ? number_format((int) $row->Devoluciones_Unidades_MP, 0, ',', '.') : '')
            ->editColumn('Total_Salidas_MP', fn ($row) => $row->Total_Salidas_MP !== null ? number_format((int) $row->Total_Salidas_MP, 0, ',', '.') : '')
            ->editColumn('Total_Unidades', fn ($row) => $row->Total_Unidades !== null ? number_format((int) $row->Total_Unidades, 0, ',', '.') : '')
            ->editColumn('Total_mm_Utilizados', fn ($row) => $row->Total_mm_Utilizados !== null ? number_format((float) $row->Total_mm_Utilizados, 2, ',', '.') : '')
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

        return view('materia_prima.salidas_iniciales.show', compact('salidaInicial'));
    }

    public function edit($id)
    {
        $salidaInicial = MpSalidaInicial::with(['ingresoMp.proveedor', 'ingresoMp.materiaPrima', 'ingresoMp.diametro'])->findOrFail($id);
        $ingresos = MpIngreso::with(['proveedor', 'materiaPrima', 'diametro'])
            ->where('Id_MP', $salidaInicial->Id_Ingreso_MP)
            ->get();

        return view('materia_prima.salidas_iniciales.edit', compact('salidaInicial', 'ingresos'));
    }

    public function update(Request $request, $id)
    {
        $salidaInicial = MpSalidaInicial::findOrFail($id);
        $validated = $this->validateData($request);
        $ingreso = $this->findIngresoOrFail($validated['Id_Ingreso_MP']);

        $data = $this->buildPayload($validated, $ingreso);
        $data['updated_by'] = Auth::id();

        $salidaInicial->fill($data);

        if (!$salidaInicial->isDirty()) {
            return redirect()->back()->with('warning', 'No se realizaron cambios.');
        }

        $salidaInicial->save();

        return redirect()->route('mp_salidas_iniciales.index')->with('success', 'Salida inicial actualizada correctamente.');
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

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'Id_Ingreso_MP' => 'required|integer|exists:mp_ingreso,Id_MP',
            'Cantidad_Unidades_MP_Preparadas' => 'required|integer|min:0',
            'Cantidad_MP_Adicionales' => 'nullable|integer|min:0',
            'Devoluciones_Unidades_MP' => 'nullable|integer|min:0',
            'reg_Status' => 'required|in:0,1',
        ]);
    }

    protected function buildPayload(array $validated, MpIngreso $ingreso): array
    {
        $unidadesIngreso = (int) ($ingreso->Unidades_MP ?? 0);
        $preparadas = (int) ($validated['Cantidad_Unidades_MP_Preparadas'] ?? 0);
        $adicionales = (int) ($validated['Cantidad_MP_Adicionales'] ?? 0);
        $devoluciones = (int) ($validated['Devoluciones_Unidades_MP'] ?? 0);
        $longitud = (float) ($ingreso->Longitud_Unidad_MP ?? 0);

        $totalSalidas = $preparadas + $adicionales - $devoluciones;
        $totalUnidades = $unidadesIngreso - $totalSalidas;
        $totalUtilizado = round($totalSalidas * $longitud, 2);

        return [
            'Id_Ingreso_MP' => $ingreso->Id_MP,
            'Cantidad_Unidades_MP' => $unidadesIngreso,
            'Cantidad_Unidades_MP_Preparadas' => $preparadas,
            'Cantidad_MP_Adicionales' => $adicionales,
            'Total_Salidas_MP' => $totalSalidas,
            'Devoluciones_Unidades_MP' => $devoluciones,
            'Total_Unidades' => $totalUnidades,
            'Longitud_Unidad_MP' => $longitud,
            'Total_mm_Utilizados' => $totalUtilizado,
            'reg_Status' => (int) $validated['reg_Status'],
        ];
    }

    protected function findIngresoOrFail(int $id): MpIngreso
    {
        return MpIngreso::where('Id_MP', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    protected function getPendientesQuery()
    {
        return MpIngreso::query()
            ->with(['proveedor', 'materiaPrima', 'diametro'])
            ->whereNull('deleted_at')
            ->whereDoesntHave('salidaInicial')
            ->orderBy('Nro_Ingreso_MP');
    }
}
