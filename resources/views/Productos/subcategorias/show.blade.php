@extends('adminlte::page')

@section('title', 'Detalle de Subcategoria de Producto')

@section('content_header')
    <div class="show-header">
        <h1>Detalle de Subcategoria de Producto</h1>
        <p>Consulta completa del registro seleccionado.</p>
    </div>
@stop

@section('content')
    <div class="card show-card mt-3">
        <div class="show-card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Subcategoria #{{ $producto_subcategoria->Id_SubCategoria }}</h3>
            <span class="badge {{ (int) $producto_subcategoria->reg_Status === 1 ? 'badge-success' : 'badge-secondary' }} detail-badge">
                {{ (int) $producto_subcategoria->reg_Status === 1 ? 'Activo' : 'Inactivo' }}
            </span>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">ID</span>
                        <div class="detail-value">{{ $producto_subcategoria->Id_SubCategoria }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Categoria</span>
                        <div class="detail-value">{{ $producto_subcategoria->categoria->Nombre_Categoria ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Estado</span>
                        <div class="detail-value">{{ (int) $producto_subcategoria->reg_Status === 1 ? 'Activo' : 'Inactivo' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">Nombre</span>
                        <div class="detail-value">{{ $producto_subcategoria->Nombre_SubCategoria }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">Creado</span>
                        <div class="detail-value">{{ $producto_subcategoria->created_at ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="show-card-footer">
            <div class="show-actions">
                <a href="{{ route('producto_subcategoria.index') }}" class="btn btn-secondary">Volver</a>
                <a href="{{ route('producto_subcategoria.edit', $producto_subcategoria->Id_SubCategoria) }}" class="btn btn-primary">Editar</a>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/subcategorias/show.css') }}">
@stop
