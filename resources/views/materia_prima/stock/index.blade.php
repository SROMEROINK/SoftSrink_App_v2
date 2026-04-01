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
        <strong>Stock neto MP:</strong> esta vista consolida salidas final base + devoluciones - adicionales - egresos de OF - reservas pendientes de pedidos MP.
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
                        <th>Pedido Prov.</th>
                        <th>Certificado</th>
                        <th>Materia Prima</th>
                        <th>Diametro MP</th>
                        <th>Codigo MP</th>
                        <th>Barras Ingreso</th>
                        <th>Devol. Proveedor</th>
                        <th>Diferencia de Stock</th>
                        <th>Salidas Final Base</th>
                        <th>Devoluciones</th>
                        <th>Adicionales</th>
                        <th>Reservadas</th>
                        <th>Egresos OF</th>
                        <th>Barras Disponibles</th>
                        <th>Longitud x Un.</th>
                        <th>Metros Disponibles</th>
                        <th>Origen</th>
                        <th>Motivo de alerta</th>
                        <th>Ajuste Inicial</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar ingreso"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar fecha"></th>
                        <th><select class="form-control form-control-sm column-filter select-filter"><option value="">Todos</option></select></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar pedido"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Filtrar certificado"></th>
                        <th><select class="form-control form-control-sm column-filter select-filter"><option value="">Todos</option></select></th>
                        <th><select class="form-control form-control-sm column-filter select-filter"><option value="">Todos</option></select></th>
                        <th><select class="form-control form-control-sm column-filter select-filter"><option value="">Todos</option></select></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Ingreso"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Dev. prov."></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Diferencia"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Salidas"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Dev."></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Adic."></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Reserv."></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Egresos OF"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Disponibles"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Longitud"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Metros"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Origen"></th>
                        <th><input type="text" class="form-control form-control-sm column-filter" placeholder="Motivo"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr @class(['stock-alert-row' => ($row['Tiene_Alerta_Stock'] ?? false)]) title="{{ $row['Motivo_Alerta'] ?: '' }}">
                            <td>{{ $row['Nro_Ingreso_MP'] }}</td>
                            <td>{{ $row['Fecha_Ingreso'] }}</td>
                            <td>{{ $row['Proveedor'] ?? '-' }}</td>
                            <td>{{ $row['Pedido_Proveedor_MP'] ?? '-' }}</td>
                            <td>{{ $row['Nro_Certificado_MP'] ?? '-' }}</td>
                            <td>{{ $row['Materia_Prima'] ?? '-' }}</td>
                            <td>{{ $row['Diametro_MP'] ?? '-' }}</td>
                            <td>{{ $row['Codigo_MP'] ?? '-' }}</td>
                            <td>{{ number_format((float) $row['Barras_Ingreso'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Devoluciones_Proveedor'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Ajuste_Stock'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Salidas_Final_Base'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Cantidad_Devoluciones_Stock'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Cantidad_Adicionales_Stock'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Barras_Reservadas'], 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $row['Barras_Egresadas_Pedidos'], 0, ',', '.') }}</td>
                            <td><strong>{{ number_format((float) $row['Barras_Disponibles'], 0, ',', '.') }}</strong></td>
                            <td>{{ number_format((float) $row['Longitud_Unidad_MP'], 2, ',', '.') }}</td>
                            <td>
                                <strong class="{{ ((float) ($row['Mts_Disponibles'] ?? 0)) < 0 ? 'text-danger' : '' }}">{{ number_format((float) $row['Mts_Disponibles'], 2, ',', '.') }}</strong>
                            </td>
                            <td>{{ $row['Detalle_Origen_MP'] ?? '-' }}</td>
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
                                <a href="{{ $row['Url_Salida_Inicial'] }}" class="btn btn-sm {{ ($row['Tiene_Salida_Inicial'] ?? false) ? 'btn-primary' : 'btn-success' }}">
                                    {{ $row['Texto_Salida_Inicial'] }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
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

    #stock-mp-table .filter-row th {
        padding: 8px;
    }

    #stock-mp-table tbody tr.stock-alert-row td {
        background-color: #f8d7da !important;
        color: #721c24;
        font-weight: 600;
    }

    #stock-mp-table tbody tr.stock-alert-row:hover td {
        background-color: #f1bfc5 !important;
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
        const table = $('#stock-mp-table').DataTable({
            orderCellsTop: true,
            scrollX: true,
            scrollY: '60vh',
            scrollCollapse: true,
            responsive: false,
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
            }
        });

        DataTableColumnFilters.bind(table, '#stock-mp-table thead tr.filter-row th', [0, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18]);
    });
</script>
@stop

