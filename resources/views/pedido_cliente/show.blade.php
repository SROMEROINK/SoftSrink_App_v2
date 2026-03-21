@extends('adminlte::page')

@section('title', 'Detalle de Pedido del Cliente')

@section('content_header')
    <div class="show-header">
        <h1>Detalle de Pedido del Cliente</h1>
        <p>Consulta completa de la orden de fabricacion seleccionada.</p>
    </div>
@stop

@section('content')
<div class="card show-card mt-3 pedido-show-card">
    <div class="show-card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">OF #{{ $pedido->Nro_OF }}</h3>
            <div class="pedido-show-subtitle">{{ $pedido->producto->Prod_Codigo ?? 'Sin producto' }}</div>
        </div>
        <span class="badge detail-badge pedido-estado-badge">
            {{ $pedido->estadoPlanificacion->Nombre_Estado ?? 'Sin estado' }}
        </span>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Producto</span>
                    <div class="detail-value">{{ $pedido->producto->Prod_Codigo ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Categoria</span>
                    <div class="detail-value">{{ $pedido->producto->categoria->Nombre_Categoria ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Subcategoria</span>
                    <div class="detail-value">{{ $pedido->producto->subCategoria->Nombre_SubCategoria ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="detail-item">
                    <span class="detail-label">Descripcion</span>
                    <div class="detail-value">{{ $pedido->producto->Prod_Descripcion ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Fecha del Pedido</span>
                    <div class="detail-value">{{ optional($pedido->Fecha_del_Pedido)->format('d/m/Y') ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Cant. Fabricacion</span>
                    <div class="detail-value">{{ number_format((int) $pedido->Cant_Fabricacion, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <span class="detail-label">Estado del Registro</span>
                    <div class="detail-value">{{ (int) $pedido->reg_Status === 1 ? 'Activo' : 'Inactivo' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-6">
                <div class="detail-item">
                    <span class="detail-label">Creado</span>
                    <div class="detail-value">{{ optional($pedido->created_at)->format('d/m/Y H:i:s') ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detail-item">
                    <span class="detail-label">Actualizado</span>
                    <div class="detail-value">{{ optional($pedido->updated_at)->format('d/m/Y H:i:s') ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="show-card-footer">
        <div class="show-actions">
            <a href="{{ route('pedido_cliente.index') }}" class="btn btn-secondary">Volver</a>
            <a href="{{ route('pedido_cliente.edit', $pedido->Id_OF) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_show.css') }}">
@stop
