@extends('adminlte::page')

@section('title', 'Listado OF Plain')

@section('content_header')
<x-header-card
    title="Listado OF - Prueba Sin DataTables"
    buttonRoute="{{ route('listado_of.index') }}"
    buttonText="Volver a listado"
/>
@stop

@section('content')
@php
    $displayPercent = function ($value) {
        $number = round((float) ($value ?? 0), 2);
        return $number > 100 ? round($number - 100, 2) : $number;
    };
    $percentSuffix = function ($value) {
        return (float) ($value ?? 0) > 100 ? ' de mas' : '';
    };
@endphp
<div class="container-fluid">
    <div class="alert alert-warning">
        <strong>Vista temporal:</strong> prueba de alineacion sin DataTables para validar header, filtros, columnas y fijado lateral.
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="top-horizontal-scroll" id="plain_top_scroll"><div></div></div>
            <div class="plain-table-wrap" id="plain_table_wrap">
                <table id="tabla_listado_of_plain" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nro OF</th><th>Planificacion</th><th>Producto</th><th>Descripcion</th><th>Categoria</th><th>Rev. Plano 1</th><th>Rev. Plano 2</th><th>Fecha Pedido</th><th>Nro Maquina</th><th>Familia Maquina</th><th>Nro Ingreso MP</th><th>Codigo MP</th><th>Certificado MP</th><th>Pedido MP</th><th>Proveedor</th><th>Cant. Fabricacion</th><th>Piezas Fabricadas</th><th>% Fabricado OF</th><th>Piezas Entregadas</th><th>Ult. Entrega</th><th>% Entregado OF</th><th>Saldo Entrega</th><th>Control Entrega</th><th>Saldo Fabricacion</th><th>Ult. Fabricacion</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td>{{ $row->Nro_OF }}</td><td>{{ $row->Estado_Planificacion }}</td><td>{{ $row->Prod_Codigo }}</td><td>{{ $row->Prod_Descripcion }}</td><td>{{ $row->Nombre_Categoria }}</td><td>{{ $row->Revision_Plano_1 }}</td><td>{{ $row->Revision_Plano_2 }}</td><td>{{ $row->Fecha_del_Pedido ? \Carbon\Carbon::parse($row->Fecha_del_Pedido)->format('d/m/Y') : '' }}</td><td>{{ $row->Nro_Maquina }}</td><td>{{ $row->Familia_Maquinas }}</td><td>{{ $row->Nro_Ingreso_MP }}</td><td>{{ $row->Codigo_MP }}</td><td>{{ $row->Nro_Certificado_MP }}</td><td>{{ $row->Pedido_de_MP }}</td><td>{{ $row->Prov_Nombre }}</td><td>{{ number_format((float) ($row->Cant_Fabricacion ?? 0), 0, ',', '.') }}</td><td>{{ number_format((float) ($row->Piezas_Fabricadas ?? 0), 0, ',', '.') }}</td><td>{{ number_format($displayPercent($row->Porcentaje_Fabricado ?? 0), 2, ',', '.') }} %{{ $percentSuffix($row->Porcentaje_Fabricado ?? 0) }}</td><td>{{ number_format((float) ($row->Piezas_Entregadas ?? 0), 0, ',', '.') }}</td><td>{{ $row->Ultima_Fecha_Entrega ? \Carbon\Carbon::parse($row->Ultima_Fecha_Entrega)->format('d/m/Y') : '' }}{{ !empty($row->Ultimo_Remito_Entrega) ? ' - R ' . $row->Ultimo_Remito_Entrega : '' }}</td><td>{{ number_format($displayPercent($row->Porcentaje_Entregado ?? 0), 2, ',', '.') }} %{{ $percentSuffix($row->Porcentaje_Entregado ?? 0) }}</td><td>{{ number_format((float) ($row->Saldo_Entrega ?? 0), 0, ',', '.') }}</td><td title="{{ $row->Control_Entrega }}">{{ $row->Control_Entrega }}</td><td>{{ number_format((float) ($row->Saldo_Fabricacion ?? 0), 0, ',', '.') }}</td><td>{{ $row->Ultima_Fecha_Fabricacion ? \Carbon\Carbon::parse($row->Ultima_Fecha_Fabricacion)->format('d/m/Y') : '' }}</td><td><div class="acciones-listado-of">@if(!empty($row->Id_OF))<a href="{{ route('pedido_cliente.show', $row->Id_OF) }}" class="btn btn-info btn-sm">Ver pedido</a>@endif @if(!empty($row->Id_Pedido_MP))<a href="{{ route('pedido_cliente_mp.editMassive', $row->Id_Pedido_MP) }}" class="btn btn-success btn-sm">MP</a>@endif @if(!empty($row->Id_OF))<a href="{{ route('fabricacion.showByNroOF', $row->Id_OF) }}" class="btn btn-primary btn-sm">Fabricacion</a>@endif</div></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="plain-table-footer">
                <div class="plain-table-footer__info">Mostrando {{ $rows->firstItem() ?? 0 }} a {{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }} registros</div>
                <div class="plain-table-footer__pagination">{{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/listado_of_plain.css') }}">
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const top = document.getElementById('plain_top_scroll');
    const topInner = top ? top.querySelector('div') : null;
    const wrap = document.getElementById('plain_table_wrap');
    const table = document.getElementById('tabla_listado_of_plain');
    let syncing = false;
    function syncWidths() {
        if (!topInner || !wrap || !table) return;
        topInner.style.width = table.scrollWidth + 'px';
        top.scrollLeft = wrap.scrollLeft;
    }
    if (wrap && top) {
        wrap.addEventListener('scroll', function () { if (syncing) return; syncing = true; top.scrollLeft = wrap.scrollLeft; syncing = false; });
        top.addEventListener('scroll', function () { if (syncing) return; syncing = true; wrap.scrollLeft = top.scrollLeft; syncing = false; });
    }
    window.addEventListener('load', syncWidths);
    window.addEventListener('resize', syncWidths);
    syncWidths();
});
</script>
@stop
