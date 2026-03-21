@extends('adminlte::page')

@section('title', 'Detalle de Grupo de Subcategoria')

@section('content_header')
    <div class="show-header">
        <h1>Detalle de Grupo de Subcategoria</h1>
        <p>Consulta completa del registro seleccionado.</p>
    </div>
@stop

@section('content')
    <div class="card show-card mt-3">
        <div class="show-card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Grupo #{{ $producto_grupo_subcategoria->Id_GrupoSubCategoria }}</h3>
            <span class="badge {{ (int) $producto_grupo_subcategoria->reg_Status === 1 ? 'badge-success' : 'badge-secondary' }} detail-badge">
                {{ (int) $producto_grupo_subcategoria->reg_Status === 1 ? 'Activo' : 'Inactivo' }}
            </span>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">ID</span>
                        <div class="detail-value">{{ $producto_grupo_subcategoria->Id_GrupoSubCategoria }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">Estado</span>
                        <div class="detail-value">{{ (int) $producto_grupo_subcategoria->reg_Status === 1 ? 'Activo' : 'Inactivo' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">Nombre</span>
                        <div class="detail-value">{{ $producto_grupo_subcategoria->Nombre_GrupoSubCategoria }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">Creado</span>
                        <div class="detail-value">{{ $producto_grupo_subcategoria->created_at ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="show-card-footer">
            <div class="show-actions">
                <a href="{{ route('producto_grupo_subcategoria.index') }}" class="btn btn-secondary">Volver</a>
                <a href="{{ route('producto_grupo_subcategoria.edit', $producto_grupo_subcategoria->Id_GrupoSubCategoria) }}" class="btn btn-primary">Editar</a>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/grupos_subcategoria/show.css') }}">
@stop
