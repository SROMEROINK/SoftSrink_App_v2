@extends('adminlte::page')

@section('title', 'Detalle de Egreso de MP')

@section('content_header')
    <h1>Detalle de Egreso de Materia Prima</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title">OF #{{ $mp_egreso->pedidoMp->pedido->Nro_OF ?? $mp_egreso->Id_OF_Salidas_MP }}</h3>
                <div class="text-muted">{{ $mp_egreso->pedidoMp->pedido->producto->Prod_Codigo ?? 'Sin producto' }}</div>
            </div>
            <span class="badge badge-{{ (int) $mp_egreso->reg_Status === 1 ? 'success' : 'secondary' }} px-3 py-2">
                {{ (int) $mp_egreso->reg_Status === 1 ? 'ACTIVO' : 'INACTIVO' }}
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Ingreso MP:</strong> {{ $mp_egreso->pedidoMp->Nro_Ingreso_MP ?? '-' }}</div>
                <div class="col-md-4"><strong>Codigo MP:</strong> {{ $mp_egreso->pedidoMp->Codigo_MP ?? '-' }}</div>
                <div class="col-md-4"><strong>Maquina:</strong> {{ $mp_egreso->pedidoMp->Nro_Maquina ?? '-' }}</div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3"><strong>Barras solicitadas:</strong> {{ number_format((int) $mp_egreso->Cantidad_Unidades_MP, 0, ',', '.') }}</div>
                <div class="col-md-3"><strong>Barras preparadas:</strong> {{ number_format((int) $mp_egreso->Cantidad_Unidades_MP_Preparadas, 0, ',', '.') }}</div>
                <div class="col-md-3"><strong>Adicionales:</strong> {{ number_format((int) $mp_egreso->Cantidad_MP_Adicionales, 0, ',', '.') }}</div>
                <div class="col-md-3"><strong>Devoluciones:</strong> {{ number_format((int) $mp_egreso->Cant_Devoluciones, 0, ',', '.') }}</div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4"><strong>Total salidas:</strong> {{ number_format((int) $mp_egreso->Total_Salidas_MP, 0, ',', '.') }}</div>
                <div class="col-md-4"><strong>Total metros utilizados:</strong> {{ number_format((float) $mp_egreso->Total_Mtros_Utilizados, 2, ',', '.') }}</div>
                <div class="col-md-4"><strong>Pedido Material Nro:</strong> {{ $mp_egreso->Nro_Pedido_MP ?? '-' }}</div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3"><strong>Fecha pedido produccion:</strong> {{ optional($mp_egreso->Fecha_del_Pedido_Produccion)->format('d/m/Y') ?? '-' }}</div>
                <div class="col-md-3"><strong>Resp. produccion:</strong> {{ $mp_egreso->Responsable_Pedido_Produccion ?? '-' }}</div>
                <div class="col-md-3"><strong>Fecha entrega calidad:</strong> {{ optional($mp_egreso->Fecha_de_Entrega_Pedido_Calidad)->format('d/m/Y') ?? '-' }}</div>
                <div class="col-md-3"><strong>Resp. calidad:</strong> {{ $mp_egreso->Responsable_de_entrega_Calidad ?? '-' }}</div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('mp_egresos.index') }}" class="btn btn-secondary">Volver</a>
            @if(optional($mp_egreso->pedidoMp)->Id_Pedido_MP)
                <a href="{{ route('pedido_cliente_mp.editMassive', $mp_egreso->pedidoMp->Id_Pedido_MP) }}" class="btn btn-outline-primary">Editar MP</a>
            @endif
            <a href="{{ route('mp_egresos.edit', $mp_egreso->Id_Egresos_MP) }}" class="btn btn-primary">Editar salida</a>
        </div>
    </div>
</div>
@stop
