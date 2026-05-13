@extends('adminlte::page')

@section('title', 'Materia Prima - Stock MP')

@section('content_header')
<x-header-card
    title="Stock Neto de Materia Prima"
    quantityTitle="Ingresos MP activos:"
    quantity="{{ $summary['ingresos_con_stock'] ?? 0 }}"
    buttonRoute="{{ route('mp_ingresos.index') }}"
    buttonText="Ver Ingresos MP"
/>
@stop

@section('content')
@include('components.swal-session')
<div class="container-fluid">
    <div class="alert alert-info mb-4">
        <strong>Stock neto MP:</strong> esta vista consolida ingreso proveedor - devoluciones al proveedor - barras ingresos iniciales - total OF + ajuste de stock. Las reservas se muestran solo como referencia.
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($summary['ingresos_con_stock'] ?? 0, 0, ',', '.') }}</h3>
                    <p>Ingresos MP activos</p>
                </div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($summary['total_barras_disponibles'] ?? 0, 0, ',', '.') }}</h3>
                    <p>Barras disponibles</p>
                </div>
                <div class="icon"><i class="fas fa-cubes"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format((float) ($summary['total_metros_disponibles'] ?? 0), 2, ',', '.') }}</h3>
                    <p>Metros disponibles</p>
                </div>
                <div class="icon"><i class="fas fa-ruler-horizontal"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($summary['total_barras_reservadas'] ?? 0, 0, ',', '.') }}</h3>
                    <p>Barras reservadas</p>
                </div>
                <div class="icon"><i class="fas fa-layer-group"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-3">
            <table id="stock-mp-table" class="table table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Nro Ingreso MP</th>
                        <th>Fecha Ingreso</th>
                        <th>Proveedor</th>
                        <th>Codigo MP</th>
                        <th>Barras Ingresos Iniciales</th>
                        <th>Barras Ingreso</th>
                        <th>Longitud x Un.</th>
                        <th>Devol. Proveedor</th>
                        <th>Cant. de barras OF iniciales</th>
                        <th>Devoluciones Stock</th>
                        <th>Adicionales Stock</th>
                        <th>Cant. total OF</th>
                        <th>Diferencia de Stock</th>
                        <th>Barras Disponibles</th>
                        <th>Metros Disponibles</th>
                        <th>Motivo de alerta</th>
                        <th>Acciones</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar ingreso"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar fecha"></th>
                        <th><select class="form-control form-control-sm column-filter select-filter"><option value="">Todos</option></select></th>
                        <th><select class="form-control form-control-sm column-filter select-filter"><option value="">Todos</option></select></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Ing. iniciales"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Ingreso"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Longitud"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Dev. prov."></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="OF iniciales"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Dev. stock"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Adic. stock"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Total OF"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Diferencia"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Disponibles"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Metros"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Motivo"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr
                            data-ingreso-id="{{ $row['Id_MP'] }}"
                            @class([
                                'stock-alert-row' => ($row['Tiene_Alerta_Stock'] ?? false),
                                'stock-selected-row' => (int) request('selected', 0) === (int) ($row['Id_MP'] ?? 0),
                            ])
                            title="{{ $row['Motivo_Alerta'] ?: '' }}"
                        >
                            <td>{{ $row['Nro_Ingreso_MP'] }}</td>
                            <td>{{ $row['Fecha_Ingreso'] }}</td>
                            <td>{{ $row['Proveedor'] ?? '-' }}</td>
                            <td>{{ $row['Codigo_MP'] ?? '-' }}</td>
                            <td>{{ number_format((float) $row['Barras_Ingresos_Iniciales'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Barras_Ingreso'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Longitud_Unidad_MP'], 2, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Devoluciones_Proveedor'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Cant_Barras_Of_Iniciales'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Cantidad_Devoluciones_Stock'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Cantidad_Adicionales_Stock'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Cantidad_Total_OF'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Ajuste_Stock'], 0, ',', '.') }}</td>
                            <td><strong>{{ number_format((float) $row['Barras_Disponibles'], 0, ',', '.') }}</strong></td>
                            <td>
                                <strong class="{{ ((float) ($row['Mts_Disponibles'] ?? 0)) < 0 ? 'text-danger' : '' }}">{{ number_format((float) $row['Mts_Disponibles'], 2, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if (!empty($row['Boton_Alerta_Texto']))
                                    <span class="btn btn-sm {{ $row['Boton_Alerta_Clase'] }} disabled" title="{{ number_format((float) ($row['Cantidad_Alerta_Barras'] ?? 0), 0, ',', '.') }} barras | {{ number_format((float) ($row['Cantidad_Alerta_Metros'] ?? 0), 2, ',', '.') }} m">
                                        {{ $row['Boton_Alerta_Texto'] }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column" style="gap:6px;">
                                    @if ((int) ($row['Barras_Reservadas'] ?? 0) > 0)
                                        <a href="{{ route('pedido_cliente_mp.index', ['filtro_ingreso_mp' => $row['Nro_Ingreso_MP']]) }}" class="btn btn-sm btn-info">
                                            Ver reservas
                                        </a>
                                    @endif
                                    <a href="{{ $row['Url_Salida_Inicial'] }}" class="btn btn-sm {{ ($row['Tiene_Salida_Inicial'] ?? false) ? 'btn-primary' : 'btn-success' }}">
                                        {{ $row['Texto_Salida_Inicial'] }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="stock-mp-totals-row">
                        <th>Totales filtrados</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/datatables.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/summary-boxes.css') }}">
<style>
    #stock-mp-table th,
    #stock-mp-table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    #stock-mp-table tbody tr {
        cursor: pointer;
    }

    #stock-mp-table .filter-row th {
        padding: 8px;
    }

    #stock-mp-table tfoot th {
        position: sticky;
        bottom: 0;
        z-index: 2;
        background-color: #e8f4ff;
        color: #12344d;
        font-weight: 700;
        border-top: 2px solid #9fc5e8;
    }

    #stock-mp-table tbody tr:hover td {
        background-color: #00e002 !important;
        color: #000 !important;
        font-weight: 700 !important;
        transition: background-color 0.15s ease-in-out;
    }

    #stock-mp-table tbody tr:hover td:first-child {
        box-shadow: inset 4px 0 0 #5d00e0;
    }

    #stock-mp-table tbody tr.stock-alert-row td {
        background-color: #f8d7da !important;
        color: #721c24;
        font-weight: 600;
    }

    #stock-mp-table tbody tr.stock-selected-row td {
        background-color: #fff3cd !important;
        color: #856404;
        font-weight: 700;
    }

    #stock-mp-table tbody tr.stock-selected-row td:first-child {
        box-shadow: inset 4px 0 0 #ff9800;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('vendor/adminlte/dist/js/shared/datatable-column-filters.js') }}"></script>
<script>
    $(function () {
        const selectedIngresoId = Number(@json((int) request('selected', 0)));
        const sumColumns = [4, 5, 7, 8, 9, 10, 11, 12, 13, 14];

        function parseEsNumber(value) {
            const normalized = String(value ?? '')
                .replace(/<[^>]*>/g, '')
                .replace(/\./g, '')
                .replace(',', '.')
                .trim();
            const parsed = Number.parseFloat(normalized);
            return Number.isFinite(parsed) ? parsed : 0;
        }

        function formatEsNumber(value, decimals) {
            return new Intl.NumberFormat('es-AR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(value);
        }

        const table = $('#stock-mp-table').DataTable({
            orderCellsTop: true,
            scrollX: true,
            scrollY: '60vh',
            scrollCollapse: true,
            responsive: false,
            pageLength: -1,
            lengthMenu: [[-1, 25, 50, 100], ['All', 25, 50, 100]],
            order: [[0, 'asc']],
            footerCallback: function () {
                const api = this.api();

                sumColumns.forEach(function (columnIndex) {
                    const total = api
                        .column(columnIndex, { search: 'applied' })
                        .data()
                        .toArray()
                        .reduce(function (carry, value) {
                            return carry + parseEsNumber(value);
                        }, 0);

                    const decimals = columnIndex === 6 || columnIndex === 14 ? 2 : 0;
                    $(api.column(columnIndex).footer()).html(formatEsNumber(total, decimals));
                });
            },
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
            }
        });

        DataTableColumnFilters.bind(table, '#stock-mp-table thead tr.filter-row th', [0, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]);

        if (selectedIngresoId > 0) {
            window.setTimeout(function () {
                const visibleNode = $('#stock-mp-table tbody tr[data-ingreso-id="' + selectedIngresoId + '"]').get(0);
                const scrollBody = $(table.table().container()).find('.dataTables_scrollBody').get(0);

                if (visibleNode && scrollBody) {
                    const targetTop = Math.max(0, visibleNode.offsetTop - (scrollBody.clientHeight / 2) + (visibleNode.clientHeight / 2));

                    scrollBody.scrollTo({
                        top: targetTop,
                        behavior: 'smooth'
                    });
                } else if (visibleNode) {
                    visibleNode.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }, 150);
        }
    });
</script>
@stop

