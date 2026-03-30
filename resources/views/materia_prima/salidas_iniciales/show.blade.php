@extends('adminlte::page')

@section('title', 'Detalle de Salida Inicial de MP')

@section('content_header')
    <div class="show-header">
        <h1>Detalle de Salida Inicial de MP</h1>
        <p>Consulta completa del ajuste historico aplicado al ingreso de materia prima seleccionado.</p>
    </div>
@stop

@section('content')
<div class="card show-card mt-3 salida-inicial-show-card">
    <div class="show-card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">Ingreso #{{ $salidaInicial->ingresoMp->Nro_Ingreso_MP ?? '-' }}</h3>
            <div class="pedido-show-subtitle">{{ $salidaInicial->ingresoMp->Codigo_MP ?? 'Sin codigo' }}</div>
        </div>
        <span class="badge detail-badge pedido-estado-badge">
            {{ (int) $salidaInicial->reg_Status === 1 ? 'ACTIVO' : 'INACTIVO' }}
        </span>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Codigo MP</span><div class="detail-value">{{ $salidaInicial->ingresoMp->Codigo_MP ?? '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Materia Prima</span><div class="detail-value">{{ $salidaInicial->ingresoMp->materiaPrima->Nombre_Materia ?? '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Diametro MP</span><div class="detail-value">{{ $salidaInicial->ingresoMp->diametro->Valor_Diametro ?? '-' }}</div></div></div>
        </div>

        <div class="row">
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Proveedor</span><div class="detail-value">{{ $salidaInicial->ingresoMp->proveedor->Prov_Nombre ?? '-' }}</div></div></div>
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Certificado</span><div class="detail-value">{{ $salidaInicial->ingresoMp->Nro_Certificado_MP ?? '-' }}</div></div></div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Unidades ingresadas</span><div class="detail-value">{{ number_format((int) $salidaInicial->Unidades_Ingresadas, 0, ',', '.') }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Stock inicial</span><div class="detail-value">{{ number_format((int) $salidaInicial->Stock_Inicial_Calculado, 0, ',', '.') }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Devoluciones al proveedor</span><div class="detail-value">{{ number_format((int) $salidaInicial->Devoluciones_Proveedor_Calculadas, 0, ',', '.') }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Diferencia de stock</span><div class="detail-value">{{ number_format((int) $salidaInicial->Ajuste_Stock_Calculado, 0, ',', '.') }}</div></div></div>
        </div>

        <div class="row">
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Salidas final</span><div class="detail-value">{{ number_format((int) $salidaInicial->Total_Salidas_Calculadas, 0, ',', '.') }}</div></div></div>
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Mts. Totales</span><div class="detail-value">{{ number_format((float) $salidaInicial->Total_mm_Utilizados_Calculados, 2, ',', '.') }}</div></div></div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Creado</span><div class="detail-value">{{ optional($salidaInicial->created_at)->format('d/m/Y H:i:s') ?? '-' }}</div></div></div>
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Actualizado</span><div class="detail-value">{{ optional($salidaInicial->updated_at)->format('d/m/Y H:i:s') ?? '-' }}</div></div></div>
        </div>
    </div>

    <div class="show-card-footer">
        <div class="show-actions">
            <a href="{{ route('mp_salidas_iniciales.index') }}" class="btn btn-secondary">Volver</a>
            <a href="{{ route('mp_salidas_iniciales.edit', $salidaInicial->Id_Ingreso_MP) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_salida_inicial_show.css') }}">
@stop
