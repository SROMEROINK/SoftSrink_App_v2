@extends('adminlte::page')

@section('title', 'Detalle de Ingreso de Materia Prima')

@section('content_header')
    <div class="show-header">
        <h1>Detalle de Ingreso de Materia Prima</h1>
        <p>Consulta completa del ingreso de materia prima seleccionado.</p>
    </div>
@stop

@section('content')
<div class="card show-card mt-3 mp-ingreso-show-card">
    <div class="show-card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">Ingreso #{{ $mp_ingreso->Nro_Ingreso_MP }}</h3>
            <div class="mp-ingreso-show-subtitle">{{ $mp_ingreso->Codigo_MP ?: ($mp_ingreso->materiaPrima->Nombre_Materia ?? 'Sin codigo de MP') }}</div>
        </div>
        <span class="badge detail-badge mp-ingreso-estado-badge">
            {{ $mp_ingreso->proveedor->Prov_Nombre ?? 'Sin proveedor' }}
        </span>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Numero de Pedido</span>
                    <div class="detail-value">{{ $mp_ingreso->Nro_Pedido ?: '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Numero de Remito</span>
                    <div class="detail-value">{{ $mp_ingreso->Nro_Remito ?: '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Fecha de Ingreso</span>
                    <div class="detail-value">{{ optional($mp_ingreso->Fecha_Ingreso)->format('d/m/Y') ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Orden de Compra</span>
                    <div class="detail-value">{{ $mp_ingreso->Nro_OC ?: '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Proveedor</span>
                    <div class="detail-value">{{ $mp_ingreso->proveedor->Prov_Nombre ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Estado del Registro</span>
                    <div class="detail-value">{{ (int) $mp_ingreso->reg_Status === 1 ? 'Activo' : 'Inactivo' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Materia Prima</span>
                    <div class="detail-value">{{ $mp_ingreso->materiaPrima->Nombre_Materia ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Diametro</span>
                    <div class="detail-value">{{ $mp_ingreso->diametro->Valor_Diametro ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Codigo de Materia Prima</span>
                    <div class="detail-value">{{ $mp_ingreso->Codigo_MP ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Numero de Certificado</span>
                    <div class="detail-value">{{ $mp_ingreso->Nro_Certificado_MP ?: '-' }}</div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="detail-item">
                    <span class="detail-label">Detalle de Origen</span>
                    <div class="detail-value">{{ $mp_ingreso->Detalle_Origen_MP ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Unidades</span>
                    <div class="detail-value">{{ number_format((float) ($mp_ingreso->Unidades_MP ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Longitud por Unidad</span>
                    <div class="detail-value">{{ number_format((float) ($mp_ingreso->Longitud_Unidad_MP ?? 0), 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Metros Totales</span>
                    <div class="detail-value">{{ number_format((float) ($mp_ingreso->Mts_Totales ?? 0), 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Kilos Totales</span>
                    <div class="detail-value">{{ number_format((float) ($mp_ingreso->Kilos_Totales ?? 0), 2, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-6">
                <div class="detail-item">
                    <span class="detail-label">Creado</span>
                    <div class="detail-value">{{ optional($mp_ingreso->created_at)->format('d/m/Y H:i:s') ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detail-item">
                    <span class="detail-label">Actualizado</span>
                    <div class="detail-value">{{ optional($mp_ingreso->updated_at)->format('d/m/Y H:i:s') ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="show-card-footer">
        <div class="show-actions">
            <a href="{{ route('mp_ingresos.index') }}" class="btn btn-secondary">Volver</a>
            @if(Route::has('mp_ingresos.edit'))
                <a href="{{ route('mp_ingresos.edit', $mp_ingreso->Id_MP) }}" class="btn btn-primary">Editar</a>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_ingreso_show.css') }}">
@stop
