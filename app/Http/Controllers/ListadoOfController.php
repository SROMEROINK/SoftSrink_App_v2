<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListadoOfController extends Controller
{
    protected string $filterCollation = 'utf8mb4_spanish_ci';
    protected ?bool $hasListadoOfIndexView = null;

    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only([
            'index',
            'indexPlain',
            'getData',
            'resumen',
            'getUniqueFilters',
            'exportCsv',
            'exportExcel',
        ]);
    }

    protected function whereCollatedEquals($query, string $column, string $value)
    {
        return $query->whereRaw(
            "CONVERT({$column} USING utf8mb4) COLLATE {$this->filterCollation} = ?",
            [$value]
        );
    }

    protected function whereYearExpression($query, string $expression, $value)
    {
        return $query->whereRaw("YEAR({$expression}) = ?", [(int) $value]);
    }

    protected function whereMonthExpression($query, string $expression, $value)
    {
        return $query->whereRaw("MONTH({$expression}) = ?", [(int) $value]);
    }

    protected function hasActiveFilters(Request $request): bool
    {
        $filterFields = [
            'filtro_nro_of',
            'filtro_estado_planificacion',
            'filtro_producto',
            'filtro_descripcion',
            'filtro_categoria',
            'filtro_fecha_pedido',
            'filtro_nro_maquina',
            'filtro_familia_maquina',
            'filtro_nro_ingreso_mp',
            'filtro_codigo_mp',
            'filtro_certificado_mp',
            'filtro_proveedor',
            'filtro_piezas_fabricadas',
            'filtro_solo_restan_entregar',
            'filtro_solo_con_ultima_fabricacion',
            'filtro_anio_pedido',
            'filtro_mes_pedido',
            'filtro_anio_fabricacion',
            'filtro_mes_fabricacion',
            'filtro_anio_entrega',
            'filtro_mes_entrega',
            'buscar',
        ];

        foreach ($filterFields as $field) {
            if ($request->filled($field)) {
                return true;
            }
        }

        return false;
    }

    protected function forgetListadoOfCache(): void
    {
        Cache::forget('listado_of.total_records');
        Cache::forget('listado_of.resumen.base');
        Cache::forget('listado_of.filters.base');
    }

    protected function hasListadoOfIndexView(): bool
    {
        if ($this->hasListadoOfIndexView !== null) {
            return $this->hasListadoOfIndexView;
        }

        $this->hasListadoOfIndexView = DB::table('information_schema.VIEWS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'listado_of_index_db')
            ->exists();

        return $this->hasListadoOfIndexView;
    }

    protected function latestDeliveryPerOfSubquery()
    {
        return DB::table('listado_entregas_productos as lep')
            ->selectRaw(
                "lep.Id_OF AS Nro_OF,
                 MAX(
                    CONCAT(
                        DATE_FORMAT(lep.Fecha_Entrega_Calidad, '%Y%m%d'),
                        LPAD(COALESCE(lep.Nro_Remito_Entrega_Calidad, 0), 10, '0'),
                        '|',
                        DATE_FORMAT(lep.Fecha_Entrega_Calidad, '%Y-%m-%d'),
                        '|',
                        COALESCE(lep.Nro_Remito_Entrega_Calidad, 0)
                    )
                 ) AS Entrega_Ultima_Key"
            )
            ->whereNull('lep.deleted_at')
            ->groupBy('lep.Id_OF');
    }

    protected function latestDeliveryDateExpression(bool $useView): string
    {
        $parsed = "STR_TO_DATE(SUBSTRING_INDEX(SUBSTRING_INDEX(uent.Entrega_Ultima_Key, '|', 2), '|', -1), '%Y-%m-%d')";

        return $useView
            ? "COALESCE({$parsed}, lo.Ultima_Fecha_Entrega)"
            : $parsed;
    }

    protected function latestDeliveryRemitoExpression(): string
    {
        return "CAST(NULLIF(SUBSTRING_INDEX(uent.Entrega_Ultima_Key, '|', -1), '') AS UNSIGNED)";
    }

    protected function baseQuery(Request $request)
    {
        $useView = $this->hasListadoOfIndexView();
        $latestDeliveryDateExpr = $this->latestDeliveryDateExpression($useView);
        $latestDeliveryRemitoExpr = $this->latestDeliveryRemitoExpression();
        $latestDeliverySubquery = $this->latestDeliveryPerOfSubquery();

        $entregasPorOf = DB::table('listado_entregas_productos')
            ->selectRaw('Id_OF AS Nro_OF, SUM(Cant_Piezas_Entregadas) AS Piezas_Entregadas, MAX(Fecha_Entrega_Calidad) AS Ultima_Fecha_Entrega')
            ->whereNull('deleted_at')
            ->groupBy('Id_OF');

        if ($useView) {
            $query = DB::table('listado_of_index_db as lo')
                ->leftJoinSub($latestDeliverySubquery, 'uent', function ($join) {
                    $join->on('uent.Nro_OF', '=', 'lo.Nro_OF');
                })
                ->select([
                    'lo.Id_OF',
                    'lo.Nro_OF',
                    'lo.Estado_Planificacion',
                    'lo.Estado',
                    'lo.Prod_Codigo',
                    'lo.Prod_Descripcion',
                    'lo.Nombre_Categoria',
                    'lo.Revision_Plano_1',
                    'lo.Revision_Plano_2',
                    'lo.Fecha_del_Pedido',
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
                    'lo.Piezas_Entregadas',
                    'lo.Porcentaje_Fabricado',
                    'lo.Saldo_Entrega',
                    'lo.Control_Entrega',
                    'lo.Ultima_Fecha_Fabricacion',
                    'lo.created_at',
                    'lo.updated_at',
                    'lo.Id_Pedido_MP',
                ])
                ->selectRaw("{$latestDeliveryDateExpr} AS Ultima_Fecha_Entrega")
                ->selectRaw('CASE WHEN COALESCE(lo.Piezas_Fabricadas, 0) > 0 THEN ROUND((COALESCE(lo.Piezas_Entregadas, 0) * 100.0) / lo.Piezas_Fabricadas, 2) ELSE 0 END AS Porcentaje_Entregado')
                ->selectRaw("{$latestDeliveryRemitoExpr} AS Ultimo_Remito_Entrega");
        } else {
            $query = DB::table('listado_of_db as lo')
                ->leftJoin('pedido_cliente as pc', function ($join) {
                    $join->on('pc.Nro_OF', '=', 'lo.Nro_OF')
                        ->whereNull('pc.deleted_at');
                })
                ->leftJoin('pedido_cliente_mp as pm', function ($join) {
                    $join->on('pm.Id_OF', '=', 'pc.Id_OF')
                        ->whereNull('pm.deleted_at');
                })
                ->leftJoinSub($entregasPorOf, 'ent', function ($join) {
                    $join->on('ent.Nro_OF', '=', 'lo.Nro_OF');
                })
                ->leftJoinSub($latestDeliverySubquery, 'uent', function ($join) {
                    $join->on('uent.Nro_OF', '=', 'lo.Nro_OF');
                })
                ->select([
                    'pc.Id_OF',
                    'lo.Nro_OF',
                    'lo.Estado_Planificacion',
                    'lo.Estado',
                    'lo.Prod_Codigo',
                    'lo.Prod_Descripcion',
                    'lo.Nombre_Categoria',
                    'lo.Revision_Plano_1',
                    'lo.Revision_Plano_2',
                    'lo.Fecha_del_Pedido',
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
                    'ent.Piezas_Entregadas',
                    'pc.created_at',
                    'pc.updated_at',
                    'pm.Id_Pedido_MP',
                ])
                ->selectRaw('CASE WHEN COALESCE(lo.Cant_Fabricacion, 0) > 0 THEN ROUND((COALESCE(lo.Piezas_Fabricadas, 0) * 100.0) / lo.Cant_Fabricacion, 2) ELSE 0 END AS Porcentaje_Fabricado')
                ->selectRaw('CASE WHEN COALESCE(lo.Piezas_Fabricadas, 0) > 0 THEN ROUND((COALESCE(ent.Piezas_Entregadas, 0) * 100.0) / lo.Piezas_Fabricadas, 2) ELSE 0 END AS Porcentaje_Entregado')
                ->selectRaw('COALESCE(lo.Piezas_Fabricadas, 0) - COALESCE(ent.Piezas_Entregadas, 0) AS Saldo_Entrega')
                ->selectRaw("CASE WHEN COALESCE(ent.Piezas_Entregadas, 0) > COALESCE(lo.Piezas_Fabricadas, 0) THEN CONCAT('Entregado de mas: ', FORMAT(COALESCE(ent.Piezas_Entregadas, 0) - COALESCE(lo.Piezas_Fabricadas, 0), 0, 'de_DE')) WHEN COALESCE(lo.Piezas_Fabricadas, 0) > COALESCE(ent.Piezas_Entregadas, 0) THEN CONCAT('Restan entregar: ', FORMAT(COALESCE(lo.Piezas_Fabricadas, 0) - COALESCE(ent.Piezas_Entregadas, 0), 0, 'de_DE')) WHEN COALESCE(lo.Piezas_Fabricadas, 0) = 0 AND COALESCE(ent.Piezas_Entregadas, 0) = 0 THEN 'Sin fabricacion ni entregas' ELSE 'Entrega completa' END AS Control_Entrega")
                ->selectRaw('rf.Ultima_Fecha_Fabricacion AS Ultima_Fecha_Fabricacion')
                ->selectRaw("{$latestDeliveryDateExpr} AS Ultima_Fecha_Entrega")
                ->selectRaw("{$latestDeliveryRemitoExpr} AS Ultimo_Remito_Entrega");
        }

        if ($request->filled('filtro_nro_of')) {
            $query->where('lo.Nro_OF', $request->filtro_nro_of);
        }

        if ($request->filled('filtro_estado_planificacion')) {
            $this->whereCollatedEquals($query, 'lo.Estado_Planificacion', $request->filtro_estado_planificacion);
        }

        if ($request->filled('filtro_producto')) {
            $query->where('lo.Prod_Codigo', 'like', '%' . $request->filtro_producto . '%');
        }

        if ($request->filled('filtro_descripcion')) {
            $query->where('lo.Prod_Descripcion', 'like', '%' . $request->filtro_descripcion . '%');
        }

        if ($request->filled('filtro_categoria')) {
            $this->whereCollatedEquals($query, 'lo.Nombre_Categoria', $request->filtro_categoria);
        }

        if ($request->filled('filtro_fecha_pedido')) {
            $query->whereDate('lo.Fecha_del_Pedido', $request->filtro_fecha_pedido);
        }

        if ($request->filled('filtro_anio_pedido')) {
            $this->whereYearExpression($query, 'lo.Fecha_del_Pedido', $request->filtro_anio_pedido);
        }

        if ($request->filled('filtro_mes_pedido')) {
            $this->whereMonthExpression($query, 'lo.Fecha_del_Pedido', $request->filtro_mes_pedido);
        }

        if ($request->filled('filtro_nro_maquina')) {
            $this->whereCollatedEquals($query, 'lo.Nro_Maquina', $request->filtro_nro_maquina);
        }

        if ($request->filled('filtro_familia_maquina')) {
            $this->whereCollatedEquals($query, 'lo.Familia_Maquinas', $request->filtro_familia_maquina);
        }

        if ($request->filled('filtro_nro_ingreso_mp')) {
            $query->where('lo.Nro_Ingreso_MP', $request->filtro_nro_ingreso_mp);
        }

        if ($request->filled('filtro_codigo_mp')) {
            $query->where('lo.Codigo_MP', 'like', '%' . $request->filtro_codigo_mp . '%');
        }

        if ($request->filled('filtro_certificado_mp')) {
            $query->where('lo.Nro_Certificado_MP', 'like', '%' . $request->filtro_certificado_mp . '%');
        }

        if ($request->filled('filtro_proveedor')) {
            $this->whereCollatedEquals($query, 'lo.Prov_Nombre', $request->filtro_proveedor);
        }

        if ($request->filled('filtro_anio_fabricacion')) {
            $this->whereYearExpression(
                $query,
                $useView ? 'lo.Ultima_Fecha_Fabricacion' : 'rf.Ultima_Fecha_Fabricacion',
                $request->filtro_anio_fabricacion
            );
        }

        if ($request->filled('filtro_mes_fabricacion')) {
            $this->whereMonthExpression(
                $query,
                $useView ? 'lo.Ultima_Fecha_Fabricacion' : 'rf.Ultima_Fecha_Fabricacion',
                $request->filtro_mes_fabricacion
            );
        }

        if ($request->filled('filtro_anio_entrega')) {
            $this->whereYearExpression($query, $latestDeliveryDateExpr, $request->filtro_anio_entrega);
        }

        if ($request->filled('filtro_mes_entrega')) {
            $this->whereMonthExpression($query, $latestDeliveryDateExpr, $request->filtro_mes_entrega);
        }

        if ($request->filled('filtro_piezas_fabricadas')) {
            $query->where('lo.Piezas_Fabricadas', (int) $request->filtro_piezas_fabricadas);
        }

        if ($request->boolean('filtro_solo_restan_entregar')) {
            if ($useView) {
                $query->whereRaw('COALESCE(lo.Saldo_Entrega, 0) > 0');
            } else {
                $query->whereRaw('COALESCE(lo.Piezas_Fabricadas, 0) > COALESCE(ent.Piezas_Entregadas, 0)');
            }
        }

        if ($request->boolean('filtro_solo_con_ultima_fabricacion')) {
            $query->whereNotNull($useView ? 'lo.Ultima_Fecha_Fabricacion' : 'rf.Ultima_Fecha_Fabricacion');
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($subQuery) use ($buscar) {
                $subQuery->where('lo.Nro_OF', 'like', '%' . $buscar . '%')
                    ->orWhere('lo.Prod_Codigo', 'like', '%' . $buscar . '%')
                    ->orWhere('lo.Prod_Descripcion', 'like', '%' . $buscar . '%')
                    ->orWhere('lo.Codigo_MP', 'like', '%' . $buscar . '%')
                    ->orWhere('lo.Nro_Certificado_MP', 'like', '%' . $buscar . '%')
                    ->orWhere('lo.Prov_Nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('lo.Nro_Maquina', 'like', '%' . $buscar . '%')
                    ->orWhere('lo.Pedido_de_MP', 'like', '%' . $buscar . '%');
            });
        }

        return $query;
    }

    protected function baseYearFilters(): array
    {
        if ($this->hasListadoOfIndexView()) {
            return Cache::remember('listado_of.filters.base', now()->addMinutes(10), function () {
                return [
                    'estados_planificacion' => DB::table('listado_of_index_db')->whereNotNull('Estado_Planificacion')->distinct()->orderBy('Estado_Planificacion')->pluck('Estado_Planificacion')->values(),
                    'categorias' => DB::table('listado_of_index_db')->whereNotNull('Nombre_Categoria')->distinct()->orderBy('Nombre_Categoria')->pluck('Nombre_Categoria')->values(),
                    'maquinas' => DB::table('listado_of_index_db')->whereNotNull('Nro_Maquina')->distinct()->orderBy('Nro_Maquina')->pluck('Nro_Maquina')->values(),
                    'familias' => DB::table('listado_of_index_db')->whereNotNull('Familia_Maquinas')->distinct()->orderBy('Familia_Maquinas')->pluck('Familia_Maquinas')->values(),
                    'proveedores' => DB::table('listado_of_index_db')->whereNotNull('Prov_Nombre')->distinct()->orderBy('Prov_Nombre')->pluck('Prov_Nombre')->values(),
                    'years_pedido' => DB::table('listado_of_index_db')->whereNotNull('Fecha_del_Pedido')->selectRaw('YEAR(Fecha_del_Pedido) as anio')->distinct()->orderByDesc('anio')->pluck('anio')->filter()->values(),
                    'years_fabricacion' => DB::table('listado_of_index_db')->whereNotNull('Ultima_Fecha_Fabricacion')->selectRaw('YEAR(Ultima_Fecha_Fabricacion) as anio')->distinct()->orderByDesc('anio')->pluck('anio')->filter()->values(),
                    'years_entrega' => DB::table('listado_of_index_db')->whereNotNull('Ultima_Fecha_Entrega')->selectRaw('YEAR(Ultima_Fecha_Entrega) as anio')->distinct()->orderByDesc('anio')->pluck('anio')->filter()->values(),
                ];
            });
        }

        return [
            'estados_planificacion' => DB::table('pedido_cliente as pc')
                ->leftJoin('estado_planificacion as ep', 'ep.Estado_Plani_Id', '=', 'pc.Estado_Plani_Id')
                ->whereNull('pc.deleted_at')
                ->whereNotNull('ep.Nombre_Estado')
                ->distinct()->orderBy('ep.Nombre_Estado')->pluck('ep.Nombre_Estado')->values(),
            'categorias' => DB::table('listado_of_db')->whereNotNull('Nombre_Categoria')->distinct()->orderBy('Nombre_Categoria')->pluck('Nombre_Categoria')->values(),
            'maquinas' => DB::table('listado_of_db')->whereNotNull('Nro_Maquina')->distinct()->orderBy('Nro_Maquina')->pluck('Nro_Maquina')->values(),
            'familias' => DB::table('listado_of_db')->whereNotNull('Familia_Maquinas')->distinct()->orderBy('Familia_Maquinas')->pluck('Familia_Maquinas')->values(),
            'proveedores' => DB::table('listado_of_db')->whereNotNull('Prov_Nombre')->distinct()->orderBy('Prov_Nombre')->pluck('Prov_Nombre')->values(),
            'years_pedido' => DB::table('pedido_cliente')->whereNull('deleted_at')->whereNotNull('Fecha_del_Pedido')->selectRaw('YEAR(Fecha_del_Pedido) as anio')->distinct()->orderByDesc('anio')->pluck('anio')->filter()->values(),
            'years_fabricacion' => DB::table('registro_de_fabricacion')->whereNull('deleted_at')->whereNotNull('Fecha_Fabricacion')->selectRaw('YEAR(Fecha_Fabricacion) as anio')->distinct()->orderByDesc('anio')->pluck('anio')->filter()->values(),
            'years_entrega' => DB::table('listado_entregas_productos')->whereNull('deleted_at')->whereNotNull('Fecha_Entrega_Calidad')->selectRaw('YEAR(Fecha_Entrega_Calidad) as anio')->distinct()->orderByDesc('anio')->pluck('anio')->filter()->values(),
        ];
    }

    protected function buildIndexViewData(Request $request): array
    {
        $pageLengthInput = strtolower((string) $request->input('page_length', '25'));
        $allowedPageLengths = [10, 25, 50, 100];
        $showAll = $pageLengthInput === 'all';
        $pageLength = $showAll ? 'all' : (int) $pageLengthInput;

        if (!$showAll && !in_array($pageLength, $allowedPageLengths, true)) {
            $pageLength = 25;
        }

        $query = $this->baseQuery($request);
        $summaryQuery = clone $query;
        $orderedQuery = (clone $query)->orderByDesc('lo.Nro_OF');

        if ($showAll) {
            $items = $orderedQuery->get();
            $total = $items->count();
            $rows = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                max($total, 1),
                1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        } else {
            $rows = $orderedQuery
                ->paginate($pageLength)
                ->withQueryString();
        }

        $summary = [
            'total_of' => (clone $summaryQuery)->count(),
            'total_piezas_solicitadas' => (int) ((clone $summaryQuery)->sum('lo.Cant_Fabricacion') ?? 0),
            'total_piezas_fabricadas' => (int) ((clone $summaryQuery)->sum('lo.Piezas_Fabricadas') ?? 0),
        ];

        $filters = $this->baseYearFilters();

        return compact('rows', 'summary', 'filters', 'pageLength');
    }

    protected function exportRows(Request $request)
    {
        return (clone $this->baseQuery($request))
            ->orderByDesc('lo.Nro_OF')
            ->get();
    }

    public function formatDisplayPercent($value): string
    {
        $number = round((float) ($value ?? 0), 2);
        $display = $number > 100 ? round($number - 100, 2) : $number;
        $suffix = $number > 100 ? ' de mas' : '';

        return number_format($display, 2, ',', '.') . ' %' . $suffix;
    }

    public function formatLatestDelivery(?string $date, $remito): string
    {
        if (!$date && !$remito) {
            return '';
        }

        $parts = [];

        if ($date) {
            $parts[] = date('d/m/Y', strtotime($date));
        }

        if ($remito) {
            $parts[] = 'Remito ' . $remito;
        }

        return implode(' - ', $parts);
    }

    public function index(Request $request)
    {
        $data = $this->buildIndexViewData($request);

        if ($request->ajax()) {
            return response()->json([
                'summary_html' => view('listado_of.partials.summary', $data)->render(),
                'table_html' => view('listado_of.partials.table', $data)->render(),
            ]);
        }

        return view('listado_of.index', $data);
    }

    public function indexPlain(Request $request)
    {
        return view('listado_of.plain', $this->buildIndexViewData($request));
    }

    public function exportCsv(Request $request)
    {
        $rows = $this->exportRows($request);
        $filename = 'listado_of_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Nro OF', 'Planificacion', 'Producto', 'Descripcion', 'Categoria', 'Rev. Plano 1', 'Rev. Plano 2',
                'Fecha Pedido', 'Nro Maquina', 'Familia Maquina', 'Nro Ingreso MP', 'Codigo MP', 'Certificado MP',
                'Pedido MP', 'Proveedor', 'Cant. Fabricacion', 'Piezas Fabricadas', '% Fabricado OF',
                'Piezas Entregadas', 'Ult. Entrega', '% Entregado OF', 'Saldo Entrega', 'Control Entrega',
                'Saldo Fabricacion', 'Ult. Fabricacion'
            ], ';');

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->Nro_OF,
                    $row->Estado_Planificacion,
                    $row->Prod_Codigo,
                    $row->Prod_Descripcion,
                    $row->Nombre_Categoria,
                    $row->Revision_Plano_1,
                    $row->Revision_Plano_2,
                    $row->Fecha_del_Pedido ? date('d/m/Y', strtotime($row->Fecha_del_Pedido)) : '',
                    $row->Nro_Maquina,
                    $row->Familia_Maquinas,
                    $row->Nro_Ingreso_MP,
                    $row->Codigo_MP,
                    $row->Nro_Certificado_MP,
                    $row->Pedido_de_MP,
                    $row->Prov_Nombre,
                    number_format((float) ($row->Cant_Fabricacion ?? 0), 0, ',', '.'),
                    number_format((float) ($row->Piezas_Fabricadas ?? 0), 0, ',', '.'),
                    $this->formatDisplayPercent($row->Porcentaje_Fabricado ?? 0),
                    number_format((float) ($row->Piezas_Entregadas ?? 0), 0, ',', '.'),
                    $this->formatLatestDelivery($row->Ultima_Fecha_Entrega ?? null, $row->Ultimo_Remito_Entrega ?? null),
                    $this->formatDisplayPercent($row->Porcentaje_Entregado ?? 0),
                    number_format((float) ($row->Saldo_Entrega ?? 0), 0, ',', '.'),
                    $row->Control_Entrega,
                    number_format((float) ($row->Saldo_Fabricacion ?? 0), 0, ',', '.'),
                    $row->Ultima_Fecha_Fabricacion ? date('d/m/Y', strtotime($row->Ultima_Fecha_Fabricacion)) : '',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $rows = $this->exportRows($request);
        $filename = 'listado_of_' . now()->format('Ymd_His') . '.xls';

        return response()->view('listado_of.exports.excel', [
            'rows' => $rows,
            'controller' => $this,
        ], 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ]);
    }

    public function getData(Request $request)
    {
        try {
            $query = $this->baseQuery($request);
            $hasFilters = $this->hasActiveFilters($request);
            $totalRecords = $this->hasListadoOfIndexView()
                ? Cache::remember('listado_of.total_records', now()->addMinutes(10), function () {
                    return DB::table('listado_of_index_db')->count();
                })
                : DB::table('pedido_cliente')->whereNull('deleted_at')->count();
            $filteredRecords = $hasFilters
                ? (clone $query)->distinct('lo.Nro_OF')->count('lo.Nro_OF')
                : $totalRecords;

            return datatables()->query($query)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filteredRecords)
                ->editColumn('Fecha_del_Pedido', fn ($row) => $row->Fecha_del_Pedido ? date('d/m/Y', strtotime($row->Fecha_del_Pedido)) : '')
                ->editColumn('Ultima_Fecha_Fabricacion', fn ($row) => $row->Ultima_Fecha_Fabricacion ? date('d/m/Y', strtotime($row->Ultima_Fecha_Fabricacion)) : '')
                ->editColumn('Ultima_Fecha_Entrega', fn ($row) => $row->Ultima_Fecha_Entrega ? date('d/m/Y', strtotime($row->Ultima_Fecha_Entrega)) : '')
                ->addColumn('acciones', function ($row) {
                    $botones = '<div class="acciones-listado-of">';
                    $botones .= '<a href="' . route('pedido_cliente.show', $row->Id_OF) . '" class="btn btn-info btn-sm">Ver pedido</a>';

                    if (!empty($row->Id_Pedido_MP)) {
                        $botones .= '<a href="' . route('pedido_cliente_mp.editMassive', $row->Id_Pedido_MP) . '" class="btn btn-success btn-sm">MP</a>';
                    }

                    $botones .= '<a href="' . route('fabricacion.showByNroOF', $row->Id_OF) . '" class="btn btn-primary btn-sm">Fabricacion</a>';
                    $botones .= '</div>';

                    return $botones;
                })
                ->rawColumns(['acciones'])
                ->toJson();
        } catch (\Throwable $e) {
            Log::error('Error en Listado OF getData', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'No se pudo cargar el listado OF.'], 500);
        }
    }

    public function resumen(Request $request)
    {
        try {
            if ($this->hasListadoOfIndexView() && !$this->hasActiveFilters($request)) {
                $summary = Cache::remember('listado_of.resumen.base', now()->addMinutes(10), function () {
                    return [
                        'total_of' => DB::table('listado_of_index_db')->count(),
                        'total_piezas_solicitadas' => (int) (DB::table('listado_of_index_db')->sum('Cant_Fabricacion') ?? 0),
                        'total_piezas_fabricadas' => (int) (DB::table('listado_of_index_db')->sum('Piezas_Fabricadas') ?? 0),
                    ];
                });

                return response()->json($summary);
            }

            $query = $this->baseQuery($request);

            return response()->json([
                'total_of' => (clone $query)->count(),
                'total_piezas_solicitadas' => (int) ((clone $query)->sum('lo.Cant_Fabricacion') ?? 0),
                'total_piezas_fabricadas' => (int) ((clone $query)->sum('lo.Piezas_Fabricadas') ?? 0),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en Listado OF resumen', ['error' => $e->getMessage()]);

            return response()->json([
                'total_of' => 0,
                'total_piezas_solicitadas' => 0,
                'total_piezas_fabricadas' => 0,
            ], 500);
        }
    }

    public function getUniqueFilters(Request $request)
    {
        try {
            return response()->json($this->baseYearFilters());
        } catch (\Throwable $e) {
            Log::error('Error en Listado OF getUniqueFilters', ['error' => $e->getMessage()]);

            return response()->json([
                'estados_planificacion' => [],
                'categorias' => [],
                'maquinas' => [],
                'familias' => [],
                'proveedores' => [],
                'years_pedido' => [],
                'years_fabricacion' => [],
                'years_entrega' => [],
            ], 500);
        }
    }
}


