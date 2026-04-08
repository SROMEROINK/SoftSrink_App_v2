<?php

namespace App\Http\Controllers;

use App\Models\CalidadInspector;
use App\Models\ListadoEntregaProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ListadoEntregaProductoController extends Controller
{
    protected string $filterCollation = 'utf8mb4_spanish_ci';
    protected ?bool $hasEntregaDbView = null;

    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only(['index', 'show', 'getData', 'resumen', 'getOfData', 'getUniqueFilters']);
        $this->middleware('permission:editar produccion')->only(['create', 'store', 'edit', 'update']);
        $this->middleware('permission:eliminar registros')->only(['destroy']);
    }

    protected function whereCollatedEquals($query, string $column, string $value)
    {
        return $query->whereRaw(
            "CONVERT({$column} USING utf8mb4) COLLATE {$this->filterCollation} = ?",
            [$value]
        );
    }

    protected function hasActiveFilters(Request $request): bool
    {
        $filterFields = [
            'buscar_global',
            'filtro_nro_of',
            'filtro_producto',
            'filtro_descripcion',
            'filtro_categoria',
            'filtro_nro_maquina',
            'filtro_familia_maquina',
            'filtro_nro_ingreso_mp',
            'filtro_codigo_mp',
            'filtro_certificado_mp',
            'filtro_proveedor',
            'filtro_nro_parcial',
            'filtro_cant_piezas',
            'filtro_nro_remito',
            'filtro_fecha_entrega',
            'filtro_anio_entrega_desde',
            'filtro_mes_entrega_desde',
            'filtro_anio_entrega_hasta',
            'filtro_mes_entrega_hasta',
            'filtro_inspector',
        ];

        foreach ($filterFields as $field) {
            if ($request->filled($field)) {
                return true;
            }
        }

        return false;
    }

    protected function forgetEntregaIndexCache(): void
    {
        Cache::forget('entregas_productos.total_records');
        Cache::forget('entregas_productos.resumen.base');
        Cache::forget('entregas_productos.filters.base');
    }

    protected function hasEntregaDbView(): bool
    {
        if ($this->hasEntregaDbView !== null) {
            return $this->hasEntregaDbView;
        }

        $this->hasEntregaDbView = DB::table('information_schema.VIEWS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'listado_entregas_productos_db')
            ->exists();

        return $this->hasEntregaDbView;
    }

    protected function resolveEntregaDesde(Request $request): ?string
    {
        if (!$request->filled('filtro_anio_entrega_desde')) {
            return null;
        }

        $year = (int) $request->filtro_anio_entrega_desde;
        $month = $request->filled('filtro_mes_entrega_desde')
            ? max(1, min(12, (int) $request->filtro_mes_entrega_desde))
            : 1;

        return Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
    }

    protected function resolveEntregaHasta(Request $request): ?string
    {
        if (!$request->filled('filtro_anio_entrega_hasta')) {
            return null;
        }

        $year = (int) $request->filtro_anio_entrega_hasta;
        $month = $request->filled('filtro_mes_entrega_hasta')
            ? max(1, min(12, (int) $request->filtro_mes_entrega_hasta))
            : 12;

        return Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
    }

    protected function baseQuery(Request $request)
    {
        if ($this->hasEntregaDbView()) {
            $query = DB::table('listado_entregas_productos_db as lep')
                ->select([
                    'lep.Id_List_Entreg_Prod',
                    'lep.Nro_OF',
                    'lep.Nro_Parcial_Calidad',
                    'lep.Cant_Piezas_Entregadas',
                    'lep.Nro_Remito_Entrega_Calidad',
                    'lep.Fecha_Entrega_Calidad',
                    'lep.Inspector_Calidad',
                    'lep.created_at',
                    'lep.updated_at',
                    'lep.created_by',
                    'lep.updated_by',
                    'lep.Prod_Codigo',
                    'lep.Prod_Descripcion',
                    'lep.Nombre_Categoria',
                    'lep.Nro_Maquina',
                    'lep.Familia_Maquinas',
                    'lep.Nro_Ingreso_MP',
                    'lep.Codigo_MP',
                    'lep.Nro_Certificado_MP',
                    'lep.Pedido_de_MP',
                    'lep.Prov_Nombre',
                    'lep.Piezas_Fabricadas',
                    'lep.Saldo_Fabricacion',
                    'lep.Pedido_Id',
                    'lep.Id_Pedido_MP',
                ]);
        } else {
            $query = DB::table('listado_entregas_productos as lep')
                ->join('listado_of_db as lo', 'lo.Nro_OF', '=', 'lep.Id_OF')
                ->leftJoin('pedido_cliente as pc', function ($join) {
                    $join->on('pc.Nro_OF', '=', 'lep.Id_OF')
                        ->whereNull('pc.deleted_at');
                })
                ->leftJoin('pedido_cliente_mp as pm', function ($join) {
                    $join->on('pm.Id_OF', '=', 'pc.Id_OF')
                        ->whereNull('pm.deleted_at');
                })
                ->select([
                    'lep.Id_List_Entreg_Prod',
                    'lep.Id_OF as Nro_OF',
                    'lep.Nro_Parcial_Calidad',
                    'lep.Cant_Piezas_Entregadas',
                    'lep.Nro_Remito_Entrega_Calidad',
                    'lep.Fecha_Entrega_Calidad',
                    'lep.Inspector_Calidad',
                    'lep.created_at',
                    'lep.updated_at',
                    'lep.created_by',
                    'lep.updated_by',
                    'lo.Prod_Codigo',
                    'lo.Prod_Descripcion',
                    'lo.Nombre_Categoria',
                    'lo.Nro_Maquina',
                    'lo.Familia_Maquinas',
                    'lo.Nro_Ingreso_MP',
                    'lo.Codigo_MP',
                    'lo.Nro_Certificado_MP',
                    'lo.Pedido_de_MP',
                    'lo.Prov_Nombre',
                    'lo.Piezas_Fabricadas',
                    'lo.Saldo_Fabricacion',
                    'pc.Id_OF as Pedido_Id',
                    'pm.Id_Pedido_MP',
                ])
                ->whereNull('lep.deleted_at');
        }

        if ($request->filled('buscar_global')) {
            $search = trim((string) $request->buscar_global);

            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('lep.Nro_OF', 'like', '%' . $search . '%')
                    ->orWhere('lep.Prod_Codigo', 'like', '%' . $search . '%')
                    ->orWhere('lep.Prod_Descripcion', 'like', '%' . $search . '%')
                    ->orWhere('lep.Nombre_Categoria', 'like', '%' . $search . '%')
                    ->orWhere('lep.Nro_Maquina', 'like', '%' . $search . '%')
                    ->orWhere('lep.Familia_Maquinas', 'like', '%' . $search . '%')
                    ->orWhere('lep.Nro_Ingreso_MP', 'like', '%' . $search . '%')
                    ->orWhere('lep.Codigo_MP', 'like', '%' . $search . '%')
                    ->orWhere('lep.Nro_Certificado_MP', 'like', '%' . $search . '%')
                    ->orWhere('lep.Prov_Nombre', 'like', '%' . $search . '%')
                    ->orWhere('lep.Nro_Parcial_Calidad', 'like', '%' . $search . '%')
                    ->orWhere('lep.Nro_Remito_Entrega_Calidad', 'like', '%' . $search . '%')
                    ->orWhere('lep.Inspector_Calidad', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('filtro_nro_of')) {
            $query->where('lep.Nro_OF', $request->filtro_nro_of);
        }

        if ($request->filled('filtro_producto')) {
            $query->where('lep.Prod_Codigo', 'like', '%' . $request->filtro_producto . '%');
        }

        if ($request->filled('filtro_descripcion')) {
            $query->where('lep.Prod_Descripcion', 'like', '%' . $request->filtro_descripcion . '%');
        }

        if ($request->filled('filtro_categoria')) {
            $this->whereCollatedEquals($query, 'lep.Nombre_Categoria', $request->filtro_categoria);
        }

        if ($request->filled('filtro_nro_maquina')) {
            $this->whereCollatedEquals($query, 'lep.Nro_Maquina', $request->filtro_nro_maquina);
        }

        if ($request->filled('filtro_familia_maquina')) {
            $this->whereCollatedEquals($query, 'lep.Familia_Maquinas', $request->filtro_familia_maquina);
        }

        if ($request->filled('filtro_nro_ingreso_mp')) {
            $query->where('lep.Nro_Ingreso_MP', $request->filtro_nro_ingreso_mp);
        }

        if ($request->filled('filtro_codigo_mp')) {
            $query->where('lep.Codigo_MP', 'like', '%' . $request->filtro_codigo_mp . '%');
        }

        if ($request->filled('filtro_certificado_mp')) {
            $query->where('lep.Nro_Certificado_MP', 'like', '%' . $request->filtro_certificado_mp . '%');
        }

        if ($request->filled('filtro_proveedor')) {
            $this->whereCollatedEquals($query, 'lep.Prov_Nombre', $request->filtro_proveedor);
        }

        if ($request->filled('filtro_nro_parcial')) {
            $query->where('lep.Nro_Parcial_Calidad', 'like', '%' . $request->filtro_nro_parcial . '%');
        }

        if ($request->filled('filtro_cant_piezas')) {
            $query->where('lep.Cant_Piezas_Entregadas', (int) $request->filtro_cant_piezas);
        }

        if ($request->filled('filtro_nro_remito')) {
            $query->where('lep.Nro_Remito_Entrega_Calidad', (int) $request->filtro_nro_remito);
        }

        if ($request->filled('filtro_fecha_entrega')) {
            $query->whereDate('lep.Fecha_Entrega_Calidad', $request->filtro_fecha_entrega);
        }

        $tieneDesde = $request->filled('filtro_anio_entrega_desde');
        $tieneHasta = $request->filled('filtro_anio_entrega_hasta');

        if ($tieneDesde && !$tieneHasta) {
            $query->whereYear('lep.Fecha_Entrega_Calidad', (int) $request->filtro_anio_entrega_desde);

            if ($request->filled('filtro_mes_entrega_desde')) {
                $query->whereMonth('lep.Fecha_Entrega_Calidad', (int) $request->filtro_mes_entrega_desde);
            }
        } else {
            $fechaEntregaDesde = $this->resolveEntregaDesde($request);
            if ($fechaEntregaDesde) {
                $query->whereDate('lep.Fecha_Entrega_Calidad', '>=', $fechaEntregaDesde);
            }

            $fechaEntregaHasta = $this->resolveEntregaHasta($request);
            if ($fechaEntregaHasta) {
                $query->whereDate('lep.Fecha_Entrega_Calidad', '<=', $fechaEntregaHasta);
            }
        }

        if ($request->filled('filtro_inspector')) {
            $query->where('lep.Inspector_Calidad', 'like', '%' . $request->filtro_inspector . '%');
        }

        return $query;
    }

    protected function detailQuery()
    {
        return DB::table('listado_entregas_productos as lep')
            ->join('listado_of_db as lo', 'lo.Nro_OF', '=', 'lep.Id_OF')
            ->leftJoin('pedido_cliente as pc', function ($join) {
                $join->on('pc.Nro_OF', '=', 'lep.Id_OF')
                    ->whereNull('pc.deleted_at');
            })
            ->leftJoin('pedido_cliente_mp as pm', function ($join) {
                $join->on('pm.Id_OF', '=', 'pc.Id_OF')
                    ->whereNull('pm.deleted_at');
            })
            ->leftJoin('users as uc', 'uc.id', '=', 'lep.created_by')
            ->leftJoin('users as uu', 'uu.id', '=', 'lep.updated_by')
            ->leftJoin('users as ul', 'ul.id', '=', 'lep.Id_Usuario_Libera')
            ->select([
                'lep.*',
                'pc.Id_OF as Pedido_Id',
                'pm.Id_Pedido_MP',
                'lo.Prod_Codigo',
                'lo.Prod_Descripcion',
                'lo.Nombre_Categoria',
                'lo.Estado_Planificacion',
                'lo.Estado',
                'lo.Revision_Plano_1',
                'lo.Revision_Plano_2',
                'lo.Cant_Fabricacion',
                'lo.Nro_Maquina',
                'lo.Familia_Maquinas',
                'lo.Nro_Ingreso_MP',
                'lo.Codigo_MP',
                'lo.Nro_Certificado_MP',
                'lo.Pedido_de_MP',
                'lo.Prov_Nombre',
                'lo.Piezas_Fabricadas',
                'lo.Saldo_Fabricacion',
                'lo.Ultima_Fecha_Fabricacion',
                'uc.name as creator_name',
                'uu.name as updater_name',
                'ul.name as liberador_name',
            ])
            ->whereNull('lep.deleted_at');
    }

    protected function usesLiberadorFk(): bool
    {
        return Schema::hasColumn('listado_entregas_productos', 'Id_Usuario_Libera');
    }

    protected function getEntregaAggregateQuery($query)
    {
        $aggregate = clone $query;

        if (method_exists($aggregate, 'cloneWithout')) {
            $aggregate = $aggregate->cloneWithout(['columns', 'orders', 'groups', 'havings']);
        }

        if (method_exists($aggregate, 'cloneWithoutBindings')) {
            $aggregate = $aggregate->cloneWithoutBindings(['select', 'order']);
        }

        return $aggregate;
    }

    protected function getEntregaFamilyLabels(): array
    {
        return [
            'Implantes',
            'Instrumental',
            'Protésicos',
            'ins/p/imp.',
            'Dispositivos',
        ];
    }

    protected function buildEntregaFamilySummary($query, string $categoriaColumn, string $cantidadColumn): array
    {
        $rows = $this->getEntregaAggregateQuery($query)
            ->selectRaw("{$categoriaColumn} as categoria, SUM({$cantidadColumn}) as total")
            ->whereNotNull($categoriaColumn)
            ->groupBy($categoriaColumn)
            ->pluck('total', 'categoria');

        $familias = [];

        foreach ($this->getEntregaFamilyLabels() as $label) {
            $familias[$label] = (int) ($rows[$label] ?? 0);
        }

        return $familias;
    }

    protected function getEntregaSummaryData(Request $request): array
    {
        if ($this->hasEntregaDbView() && !$this->hasActiveFilters($request)) {
            $summary = Cache::remember('entregas_productos.resumen.base', now()->addMinutes(10), function () {
                return [
                    'total_entregas' => DB::table('listado_entregas_productos_db')->count(),
                    'total_piezas' => (int) (DB::table('listado_entregas_productos_db')->sum('Cant_Piezas_Entregadas') ?? 0),
                    'total_remitos' => DB::table('listado_entregas_productos_db')->distinct()->count('Nro_Remito_Entrega_Calidad'),
                ];
            });

            return array_merge($summary, [
                'familias_entregadas' => $this->buildEntregaFamilySummary(
                    DB::table('listado_entregas_productos_db'),
                    'Nombre_Categoria',
                    'Cant_Piezas_Entregadas'
                ),
            ]);
        }

        $query = $this->baseQuery($request);

        return [
            'total_entregas' => (clone $query)->distinct('lep.Id_List_Entreg_Prod')->count('lep.Id_List_Entreg_Prod'),
            'total_piezas' => (int) ((clone $query)->sum('lep.Cant_Piezas_Entregadas') ?? 0),
            'total_remitos' => (clone $query)->distinct()->count('lep.Nro_Remito_Entrega_Calidad'),
            'familias_entregadas' => $this->buildEntregaFamilySummary(
                $query,
                'lep.Nombre_Categoria',
                'lep.Cant_Piezas_Entregadas'
            ),
        ];
    }

    protected function getOfDataPayload(int $nroOf, ?int $excludeEntregaId = null): ?array
    {
        $of = DB::table('listado_of_db')
            ->where('Nro_OF', $nroOf)
            ->first([
                'Nro_OF',
                'Estado_Planificacion',
                'Estado',
                'Prod_Codigo',
                'Prod_Descripcion',
                'Nombre_Categoria',
                'Revision_Plano_1',
                'Revision_Plano_2',
                'Fecha_del_Pedido',
                'Cant_Fabricacion',
                'Nro_Maquina',
                'Familia_Maquinas',
                'Nro_Ingreso_MP',
                'Codigo_MP',
                'Nro_Certificado_MP',
                'Pedido_de_MP',
                'Prov_Nombre',
                'Piezas_Fabricadas',
                'Saldo_Fabricacion',
                'Ultima_Fecha_Fabricacion',
            ]);

        if (!$of) {
            return null;
        }

        $entregadoQuery = DB::table('listado_entregas_productos')
            ->where('Id_OF', $nroOf)
            ->whereNull('deleted_at');

        if ($excludeEntregaId) {
            $entregadoQuery->where('Id_List_Entreg_Prod', '!=', $excludeEntregaId);
        }

        $totalEntregado = (int) ($entregadoQuery->sum('Cant_Piezas_Entregadas') ?? 0);
        $piezasFabricadas = (int) ($of->Piezas_Fabricadas ?? 0);
        $saldoEntrega = $piezasFabricadas - $totalEntregado;

        return [
            'Nro_OF' => (int) $of->Nro_OF,
            'Estado_Planificacion' => $of->Estado_Planificacion,
            'Estado' => $of->Estado,
            'Prod_Codigo' => $of->Prod_Codigo,
            'Prod_Descripcion' => $of->Prod_Descripcion,
            'Nombre_Categoria' => $of->Nombre_Categoria,
            'Revision_Plano_1' => $of->Revision_Plano_1,
            'Revision_Plano_2' => $of->Revision_Plano_2,
            'Fecha_del_Pedido' => $of->Fecha_del_Pedido,
            'Cant_Fabricacion' => (int) ($of->Cant_Fabricacion ?? 0),
            'Nro_Maquina' => $of->Nro_Maquina,
            'Familia_Maquinas' => $of->Familia_Maquinas,
            'Nro_Ingreso_MP' => $of->Nro_Ingreso_MP,
            'Codigo_MP' => $of->Codigo_MP,
            'Nro_Certificado_MP' => $of->Nro_Certificado_MP,
            'Pedido_de_MP' => $of->Pedido_de_MP,
            'Prov_Nombre' => $of->Prov_Nombre,
            'Piezas_Fabricadas' => $piezasFabricadas,
            'Saldo_Fabricacion' => (int) ($of->Saldo_Fabricacion ?? 0),
            'Ultima_Fecha_Fabricacion' => $of->Ultima_Fecha_Fabricacion,
            'Total_Entregado' => $totalEntregado,
            'Saldo_Entrega' => $saldoEntrega,
        ];
    }

    protected function usesQualityInspectorFk(): bool
    {
        return Schema::hasTable('calidad_inspectores')
            && Schema::hasColumn('listado_entregas_productos', 'Id_Inspector_Calidad');
    }

    protected function getActiveQualityInspectors()
    {
        if (Schema::hasTable('calidad_inspectores')) {
            return CalidadInspector::query()
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
        }

        return DB::table('listado_entregas_productos')
            ->selectRaw('TRIM(Inspector_Calidad) as nombre')
            ->whereNotNull('Inspector_Calidad')
            ->where('Inspector_Calidad', '<>', '')
            ->where('Inspector_Calidad', '<>', 'Sin registrar')
            ->groupBy(DB::raw('TRIM(Inspector_Calidad)'))
            ->orderBy('nombre')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => $item->nombre,
                    'nombre' => $item->nombre,
                ];
            });
    }

    protected function formatParcialCalidadCode(int $nroOf, int $nroParcial): string
    {
        return str_pad((string) $nroOf, 6, '0', STR_PAD_LEFT) . '/' . $nroParcial;
    }

    protected function getNextParcialCalidadNumber(int $nroOf, ?int $excludeEntregaId = null): int
    {
        $query = DB::table('listado_entregas_productos')
            ->where('Id_OF', $nroOf)
            ->whereNull('deleted_at');

        if ($excludeEntregaId) {
            $query->where('Id_List_Entreg_Prod', '!=', $excludeEntregaId);
        }

        $maxParcial = (int) ($query
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(Nro_Parcial_Calidad, '/', -1) AS UNSIGNED)) as max_parcial")
            ->value('max_parcial') ?? 0);

        return $maxParcial + 1;
    }

    protected function buildEntregaRowsFromRequest(Request $request): array
    {
        $rows = $request->input('rows', []);
        if (!is_array($rows)) {
            return [];
        }

        $normalized = [];
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $nroOf = trim((string) ($row['Id_OF'] ?? ''));
            $cantidad = trim((string) ($row['Cant_Piezas_Entregadas'] ?? ''));
            $inspectorId = trim((string) ($row['Id_Inspector_Calidad'] ?? ''));
            $parcial = trim((string) ($row['Nro_Parcial_Calidad'] ?? ''));

            if ($nroOf === '' && $cantidad === '' && $inspectorId === '' && $parcial === '') {
                continue;
            }

            $normalized[] = [
                'row_number' => $index + 1,
                'Id_OF' => $nroOf,
                'Cant_Piezas_Entregadas' => $cantidad,
                'Id_Inspector_Calidad' => $inspectorId,
                'Nro_Parcial_Calidad' => $parcial,
            ];
        }

        return $normalized;
    }

    protected function validateEntregaBatch(Request $request): array
    {
        $request->merge([
            'rows' => $this->buildEntregaRowsFromRequest($request),
        ]);

        $rules = [
            'Nro_Remito_Entrega_Calidad' => ['required', 'integer', 'min:1'],
            'Fecha_Entrega_Calidad' => ['required', 'date'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.Id_OF' => ['required', 'integer', 'exists:pedido_cliente,Nro_OF'],
            'rows.*.Cant_Piezas_Entregadas' => ['required', 'integer', 'min:1'],
            'rows.*.Nro_Parcial_Calidad' => ['required', 'string', 'max:255'],
        ];

        if ($this->usesQualityInspectorFk()) {
            $rules['rows.*.Id_Inspector_Calidad'] = [
                'required',
                'integer',
                Rule::exists('calidad_inspectores', 'id')->where(fn ($query) => $query->where('activo', true)),
            ];
        } else {
            $rules['rows.*.Id_Inspector_Calidad'] = ['required'];
        }

        $validator = validator($request->all(), $rules, [
            'Nro_Remito_Entrega_Calidad.required' => 'El numero de remito es obligatorio.',
            'Fecha_Entrega_Calidad.required' => 'La fecha de entrega es obligatoria.',
            'rows.required' => 'Debe cargar al menos una fila de entrega.',
            'rows.*.Id_OF.required' => 'Cada fila debe indicar un Nro OF.',
            'rows.*.Id_OF.exists' => 'Una de las OF ingresadas no existe.',
            'rows.*.Cant_Piezas_Entregadas.required' => 'Cada fila debe indicar una cantidad entregada.',
            'rows.*.Cant_Piezas_Entregadas.min' => 'La cantidad entregada debe ser mayor a cero.',
            'rows.*.Id_Inspector_Calidad.required' => 'Cada fila debe indicar quien controlo el lote.',
            'rows.*.Nro_Parcial_Calidad.required' => 'Cada fila debe tener un numero de parcial de calidad.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $rows = $request->input('rows', []);
            if (!is_array($rows) || empty($rows)) {
                return;
            }

            $seenParciales = [];
            $localNextParcialByOf = [];
            $sumByOf = [];

            foreach ($rows as $row) {
                $rowNumber = (int) ($row['row_number'] ?? 0);
                $nroOf = (int) ($row['Id_OF'] ?? 0);
                $cantidad = (int) ($row['Cant_Piezas_Entregadas'] ?? 0);
                $parcial = trim((string) ($row['Nro_Parcial_Calidad'] ?? ''));

                if (!$nroOf || !$cantidad || $parcial === '') {
                    continue;
                }

                if (isset($seenParciales[$parcial])) {
                    $validator->errors()->add("rows.$rowNumber.Nro_Parcial_Calidad", "El parcial {$parcial} esta duplicado dentro de la carga.");
                }
                $seenParciales[$parcial] = true;

                $exists = DB::table('listado_entregas_productos')
                    ->where('Nro_Parcial_Calidad', $parcial)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($exists) {
                    $validator->errors()->add("rows.$rowNumber.Nro_Parcial_Calidad", "El parcial {$parcial} ya existe en la base de datos.");
                }

                if (!array_key_exists($nroOf, $localNextParcialByOf)) {
                    $localNextParcialByOf[$nroOf] = $this->getNextParcialCalidadNumber($nroOf);
                }

                $expectedCode = $this->formatParcialCalidadCode($nroOf, $localNextParcialByOf[$nroOf]);
                if ($parcial !== $expectedCode) {
                    $validator->errors()->add("rows.$rowNumber.Nro_Parcial_Calidad", "Para la OF {$nroOf} el parcial esperado es {$expectedCode}.");
                }
                $localNextParcialByOf[$nroOf]++;

                $sumByOf[$nroOf] = ($sumByOf[$nroOf] ?? 0) + $cantidad;
            }

            foreach ($sumByOf as $nroOf => $totalCantidad) {
                $ofData = $this->getOfDataPayload((int) $nroOf);
                if (!$ofData) {
                    continue;
                }

                $saldoEntrega = max(0, (int) ($ofData['Saldo_Entrega'] ?? 0));
                if ($totalCantidad > $saldoEntrega) {
                    $validator->errors()->add(
                        'rows',
                        'La suma de entregas para la OF ' . $nroOf . ' supera el saldo entregable. Disponible actual: ' . number_format($saldoEntrega, 0, ',', '.') . '.'
                    );
                }
            }
        });

        return $validator->validate();
    }

    protected function validateEntrega(Request $request, ?ListadoEntregaProducto $entrega = null): array
    {
        $entregaId = $entrega?->Id_List_Entreg_Prod;
        $validator = validator($request->all(), [
            'Id_OF' => ['required', 'integer', 'exists:pedido_cliente,Nro_OF'],
            'Nro_Parcial_Calidad' => [
                'required',
                'string',
                'max:255',
                Rule::unique('listado_entregas_productos', 'Nro_Parcial_Calidad')
                    ->ignore($entregaId, 'Id_List_Entreg_Prod')
                    ->where(function ($query) use ($request) {
                        return $query->where('Id_OF', $request->input('Id_OF'))
                            ->whereNull('deleted_at');
                    }),
            ],
            'Cant_Piezas_Entregadas' => ['required', 'integer', 'min:1'],
            'Nro_Remito_Entrega_Calidad' => ['required', 'integer', 'min:1'],
            'Fecha_Entrega_Calidad' => ['required', 'date'],
            'Inspector_Calidad' => ['required', 'string', 'max:255'],
            'Id_Inspector_Calidad' => $this->usesQualityInspectorFk()
                ? ['nullable', 'integer', Rule::exists('calidad_inspectores', 'id')->where(fn ($query) => $query->where('activo', true))]
                : ['nullable'],
            'reg_Status' => ['nullable', 'in:0,1'],
        ], [
            'Id_OF.required' => 'Debe indicar una OF.',
            'Id_OF.exists' => 'La OF seleccionada no existe.',
            'Nro_Parcial_Calidad.required' => 'El parcial de calidad es obligatorio.',
            'Nro_Parcial_Calidad.unique' => 'Ese parcial ya existe para la OF seleccionada.',
            'Cant_Piezas_Entregadas.required' => 'La cantidad de piezas entregadas es obligatoria.',
            'Nro_Remito_Entrega_Calidad.required' => 'El numero de remito es obligatorio.',
            'Fecha_Entrega_Calidad.required' => 'La fecha de entrega es obligatoria.',
            'Inspector_Calidad.required' => 'El inspector de calidad es obligatorio.',
        ]);

        $validator->after(function ($validator) use ($request, $entregaId) {
            $nroOf = (int) $request->input('Id_OF');
            $cantidad = (int) $request->input('Cant_Piezas_Entregadas');

            if (!$nroOf || !$cantidad) {
                return;
            }

            $ofData = $this->getOfDataPayload($nroOf, $entregaId);
            if (!$ofData) {
                return;
            }

            $saldoEntrega = (int) ($ofData['Saldo_Entrega'] ?? 0);
            if ($saldoEntrega < 0) {
                $saldoEntrega = 0;
            }

            if ($cantidad > $saldoEntrega) {
                $validator->errors()->add(
                    'Cant_Piezas_Entregadas',
                    'La cantidad entregada supera el disponible entregable para la OF. Disponible actual: ' . number_format($saldoEntrega, 0, ',', '.') . '.'
                );
            }
        });

        return $validator->validate();
    }

    public function index()
    {
        return view('entregas_productos.index');
    }

    public function getData(Request $request)
    {
        try {
            $query = $this->baseQuery($request);
            $summary = $this->getEntregaSummaryData($request);
            $hasFilters = $this->hasActiveFilters($request);
            $totalRecords = $this->hasEntregaDbView()
                ? Cache::remember('entregas_productos.total_records', now()->addMinutes(10), function () {
                    return DB::table('listado_entregas_productos_db')->count();
                })
                : ListadoEntregaProducto::query()->whereNull('deleted_at')->count();
            $filteredRecords = $hasFilters
                ? (clone $query)->distinct('lep.Id_List_Entreg_Prod')->count('lep.Id_List_Entreg_Prod')
                : $totalRecords;

            return datatables()->query($query)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filteredRecords)
                ->editColumn('Fecha_Entrega_Calidad', function ($row) {
                    return $row->Fecha_Entrega_Calidad ? date('d/m/Y', strtotime($row->Fecha_Entrega_Calidad)) : '';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? date('d/m/Y H:i:s', strtotime($row->created_at)) : '';
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? date('d/m/Y H:i:s', strtotime($row->updated_at)) : '';
                })
                ->addColumn('acciones', function ($row) {
                    $botones = '';

                    if (!empty($row->Pedido_Id)) {
                        $botones .= '<a href="' . route('pedido_cliente.show', $row->Pedido_Id) . '" class="btn btn-info btn-sm">Pedido</a> ';
                    }

                    if (!empty($row->Id_Pedido_MP)) {
                        $botones .= '<a href="' . route('pedido_cliente_mp.editMassive', $row->Id_Pedido_MP) . '" class="btn btn-success btn-sm">MP</a> ';
                    }

                    $botones .= '<a href="' . route('fabricacion.showByNroOF', ['nroOF' => $row->Nro_OF]) . '" class="btn btn-primary btn-sm">Fabricacion</a> ';
                    $botones .= '<a href="' . route('entregas_productos.show', $row->Id_List_Entreg_Prod) . '" class="btn btn-secondary btn-sm">Ver</a> ';

                    if (auth()->user()?->can('editar produccion')) {
                        $botones .= '<a href="' . route('entregas_productos.edit', $row->Id_List_Entreg_Prod) . '" class="btn btn-primary btn-sm">Editar</a> ';
                    }

                    if (auth()->user()?->can('eliminar registros')) {
                        $botones .= '<button type="button" class="btn btn-danger btn-sm trigger-delete" data-id="' . $row->Id_List_Entreg_Prod . '">Eliminar</button>';
                    }

                    return trim($botones);
                })
                ->rawColumns(['acciones'])
                ->with(['summary' => $summary])
                ->toJson();
        } catch (\Throwable $e) {
            Log::error('Error en entregas_productos getData', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'No se pudo cargar el listado de entregas.'], 500);
        }
    }

    public function resumen(Request $request)
    {
        try {
            return response()->json($this->getEntregaSummaryData($request));

            $familyLabels = [
                'Implantes',
                'Instrumental',
                'Protésicos',
                'ins/p/imp.',
                'Dispositivos',
            ];

            $buildFamilySummary = function ($query, string $categoriaColumn, string $cantidadColumn) use ($familyLabels) {
                $rows = (clone $query)
                    ->selectRaw("{$categoriaColumn} as categoria, SUM({$cantidadColumn}) as total")
                    ->whereNotNull($categoriaColumn)
                    ->groupBy($categoriaColumn)
                    ->pluck('total', 'categoria');

                $familias = [];

                foreach ($familyLabels as $label) {
                    $familias[$label] = (int) ($rows[$label] ?? 0);
                }

                return $familias;
            };

            if ($this->hasEntregaDbView() && !$this->hasActiveFilters($request)) {
                $summary = Cache::remember('entregas_productos.resumen.base', now()->addMinutes(10), function () {
                    return [
                        'total_entregas' => DB::table('listado_entregas_productos_db')->count(),
                        'total_piezas' => (int) (DB::table('listado_entregas_productos_db')->sum('Cant_Piezas_Entregadas') ?? 0),
                        'total_remitos' => DB::table('listado_entregas_productos_db')->distinct()->count('Nro_Remito_Entrega_Calidad'),
                    ];
                });

                $familias = $buildFamilySummary(
                    DB::table('listado_entregas_productos_db'),
                    'Nombre_Categoria',
                    'Cant_Piezas_Entregadas'
                );

                return response()->json(array_merge($summary, [
                    'familias_entregadas' => $familias,
                ]));
            }

            $query = $this->baseQuery($request);

            return response()->json([
                'total_entregas' => (clone $query)->count(),
                'total_piezas' => (int) ((clone $query)->sum('lep.Cant_Piezas_Entregadas') ?? 0),
                'total_remitos' => (clone $query)->distinct()->count('lep.Nro_Remito_Entrega_Calidad'),
                'familias_entregadas' => $buildFamilySummary(
                    $query,
                    'lep.Nombre_Categoria',
                    'lep.Cant_Piezas_Entregadas'
                ),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en entregas_productos resumen', ['error' => $e->getMessage()]);

            return response()->json([
                'total_entregas' => 0,
                'total_piezas' => 0,
                'total_remitos' => 0,
                'familias_entregadas' => [
                    'Implantes' => 0,
                    'Instrumental' => 0,
                    'Protésicos' => 0,
                    'ins/p/imp.' => 0,
                    'Dispositivos' => 0,
                ],
            ], 500);
        }
    }

    public function getUniqueFilters(Request $request)
    {
        try {
            if ($this->hasEntregaDbView() && !$this->hasActiveFilters($request)) {
                $filters = Cache::remember('entregas_productos.filters.base', now()->addMinutes(10), function () {
                    return [
                        'categorias' => DB::table('listado_entregas_productos_db')
                            ->whereNotNull('Nombre_Categoria')
                            ->distinct()
                            ->orderBy('Nombre_Categoria')
                            ->pluck('Nombre_Categoria')
                            ->values(),
                        'maquinas' => DB::table('listado_entregas_productos_db')
                            ->whereNotNull('Nro_Maquina')
                            ->distinct()
                            ->orderBy('Nro_Maquina')
                            ->pluck('Nro_Maquina')
                            ->values(),
                        'familias' => DB::table('listado_entregas_productos_db')
                            ->whereNotNull('Familia_Maquinas')
                            ->distinct()
                            ->orderBy('Familia_Maquinas')
                            ->pluck('Familia_Maquinas')
                            ->values(),
                        'proveedores' => DB::table('listado_entregas_productos_db')
                            ->whereNotNull('Prov_Nombre')
                            ->distinct()
                            ->orderBy('Prov_Nombre')
                            ->pluck('Prov_Nombre')
                            ->values(),
                        'years_entrega' => DB::table('listado_entregas_productos_db')
                            ->whereNotNull('Fecha_Entrega_Calidad')
                            ->selectRaw('DISTINCT YEAR(Fecha_Entrega_Calidad) as anio')
                            ->orderByDesc('anio')
                            ->pluck('anio')
                            ->values(),
                    ];
                });

                return response()->json($filters);
            }

            $base = $this->baseQuery($request);

            $categorias = $this->getEntregaAggregateQuery($base)
                ->select('lep.Nombre_Categoria')
                ->whereNotNull('lep.Nombre_Categoria')
                ->distinct()
                ->orderBy('lep.Nombre_Categoria')
                ->pluck('lep.Nombre_Categoria')
                ->values();

            $maquinas = $this->getEntregaAggregateQuery($base)
                ->select('lep.Nro_Maquina')
                ->whereNotNull('lep.Nro_Maquina')
                ->distinct()
                ->orderBy('lep.Nro_Maquina')
                ->pluck('lep.Nro_Maquina')
                ->values();

            $familias = $this->getEntregaAggregateQuery($base)
                ->select('lep.Familia_Maquinas')
                ->whereNotNull('lep.Familia_Maquinas')
                ->distinct()
                ->orderBy('lep.Familia_Maquinas')
                ->pluck('lep.Familia_Maquinas')
                ->values();

            $proveedores = $this->getEntregaAggregateQuery($base)
                ->select('lep.Prov_Nombre')
                ->whereNotNull('lep.Prov_Nombre')
                ->distinct()
                ->orderBy('lep.Prov_Nombre')
                ->pluck('lep.Prov_Nombre')
                ->values();

            $yearsEntrega = $this->getEntregaAggregateQuery($base)
                ->whereNotNull('lep.Fecha_Entrega_Calidad')
                ->selectRaw('YEAR(lep.Fecha_Entrega_Calidad) as anio')
                ->distinct()
                ->orderByDesc('anio')
                ->pluck('anio')
                ->values();

            return response()->json([
                'categorias' => $categorias,
                'maquinas' => $maquinas,
                'familias' => $familias,
                'proveedores' => $proveedores,
                'years_entrega' => $yearsEntrega,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en entregas_productos getUniqueFilters', ['error' => $e->getMessage()]);

            return response()->json([
                'categorias' => [],
                'maquinas' => [],
                'familias' => [],
                'proveedores' => [],
                'years_entrega' => [],
            ], 500);
        }
    }

    public function getOfData(Request $request, int $nroOf)
    {
        $excludeEntregaId = $request->filled('exclude_entrega_id') ? (int) $request->input('exclude_entrega_id') : null;
        $data = $this->getOfDataPayload($nroOf, $excludeEntregaId);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos consolidados para esa OF.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge($data, [
                'Next_Parcial_Numero' => $this->getNextParcialCalidadNumber($nroOf, $excludeEntregaId),
                'Next_Parcial_Calidad' => $this->formatParcialCalidadCode(
                    $nroOf,
                    $this->getNextParcialCalidadNumber($nroOf, $excludeEntregaId)
                ),
            ]),
        ]);
    }

    public function create()
    {
        $ultimoRemito = (int) (ListadoEntregaProducto::query()->max('Nro_Remito_Entrega_Calidad') ?? 0);

        return view('entregas_productos.create', [
            'ultimoRemito' => $ultimoRemito,
            'proximoRemito' => $ultimoRemito + 1,
            'fechaEntregaSugerida' => now()->format('Y-m-d'),
            'inspectoresCalidad' => $this->getActiveQualityInspectors(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $isBatch = is_array($request->input('rows'));

            if (!$isBatch) {
                $validated = $this->validateEntrega($request);

                $payload = [
                    'Id_OF' => (int) $validated['Id_OF'],
                    'Nro_Parcial_Calidad' => trim($validated['Nro_Parcial_Calidad']),
                    'Cant_Piezas_Entregadas' => (int) $validated['Cant_Piezas_Entregadas'],
                    'Nro_Remito_Entrega_Calidad' => (int) $validated['Nro_Remito_Entrega_Calidad'],
                    'Fecha_Entrega_Calidad' => $validated['Fecha_Entrega_Calidad'],
                    'Inspector_Calidad' => trim($validated['Inspector_Calidad']),
                    'reg_Status' => (int) ($validated['reg_Status'] ?? 1),
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ];

                if ($this->usesQualityInspectorFk() && !empty($validated['Id_Inspector_Calidad'])) {
                    $payload['Id_Inspector_Calidad'] = (int) $validated['Id_Inspector_Calidad'];
                }

                if ($this->usesLiberadorFk()) {
                    $payload['Id_Usuario_Libera'] = Auth::id();
                }

                $entrega = ListadoEntregaProducto::create($payload);

                $this->forgetEntregaIndexCache();

                return response()->json([
                    'success' => true,
                    'message' => 'Entrega registrada correctamente.',
                    'redirect' => route('entregas_productos.show', $entrega->Id_List_Entreg_Prod),
                ]);
            }

            $validated = $this->validateEntregaBatch($request);
            $rows = $validated['rows'] ?? [];
            $inspectores = $this->usesQualityInspectorFk()
                ? CalidadInspector::query()->pluck('nombre', 'id')
                : collect();
            $createdCount = 0;

            DB::transaction(function () use ($validated, $rows, $inspectores, &$createdCount) {
                foreach ($rows as $row) {
                    $payload = [
                        'Id_OF' => (int) $row['Id_OF'],
                        'Nro_Parcial_Calidad' => trim($row['Nro_Parcial_Calidad']),
                        'Cant_Piezas_Entregadas' => (int) $row['Cant_Piezas_Entregadas'],
                        'Nro_Remito_Entrega_Calidad' => (int) $validated['Nro_Remito_Entrega_Calidad'],
                        'Fecha_Entrega_Calidad' => $validated['Fecha_Entrega_Calidad'],
                        'Inspector_Calidad' => $this->usesQualityInspectorFk()
                            ? (string) ($inspectores[(int) $row['Id_Inspector_Calidad']] ?? '')
                            : trim((string) $row['Id_Inspector_Calidad']),
                        'reg_Status' => 1,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ];

                    if ($this->usesQualityInspectorFk()) {
                        $payload['Id_Inspector_Calidad'] = (int) $row['Id_Inspector_Calidad'];
                    }

                    if ($this->usesLiberadorFk()) {
                        $payload['Id_Usuario_Libera'] = Auth::id();
                    }

                    ListadoEntregaProducto::create($payload);
                    $createdCount++;
                }
            });

            $this->forgetEntregaIndexCache();

            return response()->json([
                'success' => true,
                'message' => 'Entrega registrada correctamente. Filas creadas: ' . $createdCount . '.',
                'redirect' => route('entregas_productos.index'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error al crear entrega de productos', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo registrar la entrega.',
            ], 500);
        }
    }

    public function show(string $id)
    {
        $entrega = $this->detailQuery()
            ->where('lep.Id_List_Entreg_Prod', $id)
            ->first();

        abort_if(!$entrega, 404);

        $detalleOf = $this->getOfDataPayload((int) $entrega->Id_OF, (int) $entrega->Id_List_Entreg_Prod);

        return view('entregas_productos.show', compact('entrega', 'detalleOf'));
    }

    public function edit(string $id)
    {
        $entrega = ListadoEntregaProducto::findOrFail($id);
        $detalle = $this->detailQuery()
            ->where('lep.Id_List_Entreg_Prod', $id)
            ->first();

        abort_if(!$detalle, 404);

        $detalleOf = $this->getOfDataPayload((int) $entrega->Id_OF, (int) $entrega->Id_List_Entreg_Prod);

        return view('entregas_productos.edit', [
            'entrega' => $entrega,
            'detalle' => $detalle,
            'detalleOf' => $detalleOf,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $entrega = ListadoEntregaProducto::findOrFail($id);

        try {
            $validated = $this->validateEntrega($request, $entrega);

            $payload = [
                'Id_OF' => (int) $validated['Id_OF'],
                'Nro_Parcial_Calidad' => trim($validated['Nro_Parcial_Calidad']),
                'Cant_Piezas_Entregadas' => (int) $validated['Cant_Piezas_Entregadas'],
                'Nro_Remito_Entrega_Calidad' => (int) $validated['Nro_Remito_Entrega_Calidad'],
                'Fecha_Entrega_Calidad' => $validated['Fecha_Entrega_Calidad'],
                'Inspector_Calidad' => trim($validated['Inspector_Calidad']),
                'reg_Status' => (int) ($validated['reg_Status'] ?? $entrega->reg_Status ?? 1),
            ];

            if ($this->usesQualityInspectorFk() && !empty($validated['Id_Inspector_Calidad'])) {
                $payload['Id_Inspector_Calidad'] = (int) $validated['Id_Inspector_Calidad'];
            }

            $entrega->fill($payload);

            if (!$entrega->isDirty()) {
                return response()->json([
                    'success' => false,
                    'type' => 'no_changes',
                    'message' => 'No se detectaron cambios en la entrega.',
                ]);
            }

            $entrega->updated_by = Auth::id();
            $entrega->save();
            $this->forgetEntregaIndexCache();

            return response()->json([
                'success' => true,
                'message' => 'Entrega actualizada correctamente.',
                'redirect' => route('entregas_productos.show', $entrega->Id_List_Entreg_Prod),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error al actualizar entrega de productos', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar la entrega.',
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $entrega = ListadoEntregaProducto::findOrFail($id);
            $entrega->deleted_by = Auth::id();
            $entrega->save();
            $entrega->delete();
            $this->forgetEntregaIndexCache();

            return response()->json([
                'success' => true,
                'message' => 'Entrega eliminada correctamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar entrega de productos', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la entrega.',
            ], 500);
        }
    }
}
