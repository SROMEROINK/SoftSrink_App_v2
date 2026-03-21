@extends('adminlte::page')

@section('title', 'Detalle de Definicion de Materia Prima')

@section('content_header')
    <div class="show-header">
        <h1>Detalle de Definicion de Materia Prima</h1>
        <p>Consulta completa de la etapa de abastecimiento de la OF seleccionada.</p>
    </div>
@stop

@section('content')
<div class="card show-card mt-3 pedido-show-card">
    <div class="show-card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">OF #{{ $pedidoMp->pedido->Nro_OF ?? '-' }}</h3>
            <div class="pedido-show-subtitle">{{ $pedidoMp->pedido->producto->Prod_Codigo ?? 'Sin producto' }}</div>
        </div>
        <span class="badge detail-badge pedido-estado-badge">
            {{ $pedidoMp->estadoPlanificacion->Nombre_Estado ?? 'Sin estado' }}
        </span>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Producto</span><div class="detail-value">{{ $pedidoMp->pedido->producto->Prod_Codigo ?? '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Categoria</span><div class="detail-value">{{ $pedidoMp->pedido->producto->categoria->Nombre_Categoria ?? '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Subcategoria</span><div class="detail-value">{{ $pedidoMp->pedido->producto->subCategoria->Nombre_SubCategoria ?? '-' }}</div></div></div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Descripcion</span><div class="detail-value">{{ $pedidoMp->pedido->producto->Prod_Descripcion ?? '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Fecha Pedido</span><div class="detail-value">{{ $pedidoMp->pedido && $pedidoMp->pedido->Fecha_del_Pedido ? \Carbon\Carbon::parse($pedidoMp->pedido->Fecha_del_Pedido)->format('d/m/Y') : '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Cant. Fabricacion</span><div class="detail-value">{{ $pedidoMp->pedido ? number_format((int) $pedidoMp->pedido->Cant_Fabricacion, 0, ',', '.') : '-' }}</div></div></div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Codigo MP</span><div class="detail-value">{{ $pedidoMp->Codigo_MP ?: '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Materia Prima</span><div class="detail-value">{{ $pedidoMp->Materia_Prima ?: '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Diametro MP</span><div class="detail-value">{{ $pedidoMp->Diametro_MP ?: '-' }}</div></div></div>
        </div>

        <div class="row">
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Ingreso MP Seleccionado</span><div class="detail-value">{{ $pedidoMp->Nro_Ingreso_MP ?? '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Pedido Material Nro</span><div class="detail-value">{{ $pedidoMp->Pedido_Material_Nro ?? '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Nro Certificado MP</span><div class="detail-value">{{ $pedidoMp->Nro_Certificado_MP ?: '-' }}</div></div></div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Longitud x Un.(MP)</span><div class="detail-value">{{ $pedidoMp->Longitud_Un_MP !== null ? number_format((float) $pedidoMp->Longitud_Un_MP, 2, ',', '.') : '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Largo de pieza</span><div class="detail-value">{{ $pedidoMp->Largo_Pieza !== null ? number_format((float) $pedidoMp->Largo_Pieza, 2, ',', '.') : '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Frenteado</span><div class="detail-value">{{ $pedidoMp->Frenteado !== null ? number_format((float) $pedidoMp->Frenteado, 2, ',', '.') : '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Ancho Cut Off</span><div class="detail-value">{{ $pedidoMp->Ancho_Cut_Off !== null ? number_format((float) $pedidoMp->Ancho_Cut_Off, 2, ',', '.') : '-' }}</div></div></div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Sobrematerial-Promedio</span><div class="detail-value">{{ $pedidoMp->Sobrematerial_Promedio !== null ? number_format((float) $pedidoMp->Sobrematerial_Promedio, 2, ',', '.') : '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Largo total de Pieza</span><div class="detail-value">{{ $pedidoMp->Largo_Total_Pieza !== null ? number_format((float) $pedidoMp->Largo_Total_Pieza, 2, ',', '.') : '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">mm Totales</span><div class="detail-value">{{ $pedidoMp->MM_Totales !== null ? number_format((float) $pedidoMp->MM_Totales, 2, ',', '.') : '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Longitud de Barra - SCRAP(mm)</span><div class="detail-value">{{ $pedidoMp->Longitud_Barra_Sin_Scrap !== null ? number_format((float) $pedidoMp->Longitud_Barra_Sin_Scrap, 2, ',', '.') : '-' }}</div></div></div>
        </div>

        <div class="row">
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Cant. de Barras MP</span><div class="detail-value">{{ $pedidoMp->Cant_Barras_MP !== null ? number_format((int) $pedidoMp->Cant_Barras_MP, 0, ',', '.') : '-' }}</div></div></div>
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Cant. de Piezas por Barra</span><div class="detail-value">{{ $pedidoMp->Cant_Piezas_Por_Barra !== null ? number_format((float) $pedidoMp->Cant_Piezas_Por_Barra, 2, ',', '.') : '-' }}</div></div></div>
        </div>

        <div class="detail-divider"></div>

        <div class="row"><div class="col-md-12"><div class="detail-item"><span class="detail-label">Observaciones</span><div class="detail-value">{{ $pedidoMp->Observaciones ?: '-' }}</div></div></div></div>

        <div class="row">
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Creado</span><div class="detail-value">{{ optional($pedidoMp->created_at)->format('d/m/Y H:i:s') ?? '-' }}</div></div></div>
            <div class="col-md-6"><div class="detail-item"><span class="detail-label">Actualizado</span><div class="detail-value">{{ optional($pedidoMp->updated_at)->format('d/m/Y H:i:s') ?? '-' }}</div></div></div>
        </div>
    </div>

    <div class="show-card-footer">
        <div class="show-actions">
            <a href="{{ route('pedido_cliente_mp.index') }}" class="btn btn-secondary">Volver</a>
            <a href="{{ route('pedido_cliente_mp.edit', $pedidoMp->Id_Pedido_MP) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_mp_show.css') }}">
@stop
