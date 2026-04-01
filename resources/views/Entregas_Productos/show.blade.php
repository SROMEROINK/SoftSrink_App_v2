@extends('adminlte::page')

@section('title', 'Detalle de Entrega de Producto')

@section('content_header')
    <div class="show-header">
        <h1>Detalle de Entrega de Producto</h1>
        <p>Consulta completa de la entrega seleccionada al cliente.</p>
    </div>
@stop

@section('content')
<div class="card show-card mt-3 entrega-show-card">
    <div class="show-card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">Entrega #{{ $entrega->Id_List_Entreg_Prod }}</h3>
            <div class="pedido-show-subtitle">OF #{{ $entrega->Nro_OF }} - {{ $entrega->Prod_Codigo ?? 'Sin producto' }}</div>
        </div>
        <span class="badge detail-badge pedido-estado-badge">
            Remito {{ $entrega->Nro_Remito_Entrega_Calidad }}
        </span>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Producto</span>
                    <div class="detail-value">{{ $entrega->Prod_Codigo ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="detail-item">
                    <span class="detail-label">Descripcion</span>
                    <div class="detail-value">{{ $entrega->Prod_Descripcion ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item">
                    <span class="detail-label">Categoria</span>
                    <div class="detail-value">{{ $entrega->Nombre_Categoria ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item">
                    <span class="detail-label">Parcial calidad</span>
                    <div class="detail-value">{{ $entrega->Nro_Parcial_Calidad ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Cant. piezas entregadas</span>
                    <div class="detail-value">{{ number_format((int) $entrega->Cant_Piezas_Entregadas, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Fecha de entrega</span>
                    <div class="detail-value">{{ $entrega->Fecha_Entrega_Calidad ? \Carbon\Carbon::parse($entrega->Fecha_Entrega_Calidad)->format('d/m/Y') : '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Inspector calidad</span>
                    <div class="detail-value">{{ $entrega->Inspector_Calidad ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Estado del registro</span>
                    <div class="detail-value">{{ (int) $entrega->reg_Status === 1 ? 'Activo' : 'Inactivo' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Nro maquina</span>
                    <div class="detail-value">{{ $entrega->Nro_Maquina ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Familia maquina</span>
                    <div class="detail-value">{{ $entrega->Familia_Maquinas ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item">
                    <span class="detail-label">Nro ingreso MP</span>
                    <div class="detail-value">{{ $entrega->Nro_Ingreso_MP ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item">
                    <span class="detail-label">Codigo MP</span>
                    <div class="detail-value">{{ $entrega->Codigo_MP ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item">
                    <span class="detail-label">Certificado MP</span>
                    <div class="detail-value">{{ $entrega->Nro_Certificado_MP ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Proveedor</span>
                    <div class="detail-value">{{ $entrega->Prov_Nombre ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item">
                    <span class="detail-label">Pedido de MP</span>
                    <div class="detail-value">{{ $entrega->Pedido_de_MP ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item">
                    <span class="detail-label">Piezas fabricadas</span>
                    <div class="detail-value">{{ number_format((int) ($entrega->Piezas_Fabricadas ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item">
                    <span class="detail-label">Total entregado</span>
                    <div class="detail-value">{{ number_format((int) ($detalleOf['Total_Entregado'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item detail-item-highlight">
                    <span class="detail-label">Saldo para entregar</span>
                    <div class="detail-value">{{ number_format((int) ($detalleOf['Saldo_Entrega'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-6">
                <div class="detail-item">
                    <span class="detail-label">Creado</span>
                    <div class="detail-value">
                        {{ $entrega->created_at ? \Carbon\Carbon::parse($entrega->created_at)->format('d/m/Y H:i:s') : '-' }}
                        @if(!empty($entrega->creator_name))<br><small>{{ $entrega->creator_name }}</small>@endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detail-item">
                    <span class="detail-label">Actualizado</span>
                    <div class="detail-value">
                        {{ $entrega->updated_at ? \Carbon\Carbon::parse($entrega->updated_at)->format('d/m/Y H:i:s') : '-' }}
                        @if(!empty($entrega->updater_name))<br><small>{{ $entrega->updater_name }}</small>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="show-card-footer">
        <div class="show-actions">
            <a href="{{ route('entregas_productos.index') }}" class="btn btn-secondary">Volver</a>
            @if(!empty($entrega->Pedido_Id))
                <a href="{{ route('pedido_cliente.show', $entrega->Pedido_Id) }}" class="btn btn-info">Pedido</a>
            @endif
            @if(!empty($entrega->Id_Pedido_MP))
                <a href="{{ route('pedido_cliente_mp.editMassive', $entrega->Id_Pedido_MP) }}" class="btn btn-success">MP</a>
            @endif
            <a href="{{ route('fabricacion.showByNroOF', ['nroOF' => $entrega->Nro_OF]) }}" class="btn btn-primary">Fabricacion</a>
            <a href="{{ route('entregas_productos.edit', $entrega->Id_List_Entreg_Prod) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/entregas_productos_show.css') }}">
@stop
