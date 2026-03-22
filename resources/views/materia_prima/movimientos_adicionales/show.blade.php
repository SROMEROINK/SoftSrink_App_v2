@extends('adminlte::page')

@section('title', 'Detalle Movimiento Adicional MP')

@section('content_header')
    <h1>Detalle de Movimiento Adicional de MP</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card movimiento-show-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title mb-0">Movimiento #{{ $movimiento->Id_Movimiento_MP }}</h3>
            </div>
            <span class="badge badge-info">{{ $movimiento->reg_Status ? 'ACTIVO' : 'INACTIVO' }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><div class="info-box-item"><span class="label">Fecha</span><span class="value">{{ optional($movimiento->Fecha_Movimiento)->format('d/m/Y') }}</span></div></div>
                <div class="col-md-4"><div class="info-box-item"><span class="label">Ingreso MP</span><span class="value">{{ $movimiento->Nro_Ingreso_MP }}</span></div></div>
                <div class="col-md-4"><div class="info-box-item"><span class="label">OF</span><span class="value">{{ $movimiento->Nro_OF ?: '-' }}</span></div></div>
            </div>
            <div class="row">
                <div class="col-md-4"><div class="info-box-item"><span class="label">Codigo Producto</span><span class="value">{{ $movimiento->Codigo_Producto ?: '-' }}</span></div></div>
                <div class="col-md-4"><div class="info-box-item"><span class="label">Nro Maquina</span><span class="value">{{ $movimiento->Nro_Maquina ?: '-' }}</span></div></div>
                <div class="col-md-4"><div class="info-box-item"><span class="label">Certificado</span><span class="value">{{ $movimiento->Nro_Certificado_MP ?: '-' }}</span></div></div>
            </div>
            <div class="row">
                <div class="col-md-3"><div class="info-box-item"><span class="label">Adicionales</span><span class="value">{{ number_format((int) $movimiento->Cantidad_Adicionales, 0, ',', '.') }}</span></div></div>
                <div class="col-md-3"><div class="info-box-item"><span class="label">Devoluciones</span><span class="value">{{ number_format((int) $movimiento->Cantidad_Devoluciones, 0, ',', '.') }}</span></div></div>
                <div class="col-md-3"><div class="info-box-item"><span class="label">Longitud (Mts)</span><span class="value">{{ number_format((float) $movimiento->Longitud_Unidad_Mts, 2, ',', '.') }}</span></div></div>
                <div class="col-md-3"><div class="info-box-item"><span class="label">Metros Netos</span><span class="value">{{ number_format((float) $movimiento->Total_Mtros_Movimiento, 2, ',', '.') }}</span></div></div>
            </div>
            <div class="row">
                <div class="col-md-12"><div class="info-box-item"><span class="label">Observaciones</span><span class="value">{{ $movimiento->Observaciones ?: '-' }}</span></div></div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('mp_movimientos_adicionales.index') }}" class="btn btn-secondary">Volver</a>
            <a href="{{ route('mp_movimientos_adicionales.edit', $movimiento->Id_Movimiento_MP) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_movimiento_adicional_show.css') }}">
@stop
