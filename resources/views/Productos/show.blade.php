@extends('adminlte::page')

@section('title', 'Detalle de Producto')

@section('content_header')
    <div class="show-header">
        <h1>Detalle de Producto</h1>
        <p>Consulta completa del registro seleccionado.</p>
    </div>
@stop

@section('content')
    <div class="card show-card mt-3">
        <div class="show-card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Producto #{{ $producto->Id_Producto }}</h3>
            <span class="badge {{ (int) $producto->reg_Status === 1 ? 'badge-success' : 'badge-secondary' }} detail-badge">
                {{ (int) $producto->reg_Status === 1 ? 'Activo' : 'Inactivo' }}
            </span>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Codigo</span>
                        <div class="detail-value">{{ $producto->Prod_Codigo }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Tipo</span>
                        <div class="detail-value">{{ $producto->productoTipo->Nombre_Tipo ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Cliente</span>
                        <div class="detail-value">{{ $producto->cliente->Cli_Nombre ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">Descripcion</span>
                        <div class="detail-value">{{ $producto->Prod_Descripcion }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">Nro de Plano</span>
                        <div class="detail-value">{{ $producto->Prod_N_Plano ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Categoria</span>
                        <div class="detail-value">{{ $producto->categoria->Nombre_Categoria ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Subcategoria</span>
                        <div class="detail-value">{{ $producto->subCategoria->Nombre_SubCategoria ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Grupo Subcategoria</span>
                        <div class="detail-value">{{ $producto->grupoSubCategoria->Nombre_GrupoSubCategoria ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Grupo Conjuntos</span>
                        <div class="detail-value">{{ $producto->grupoConjuntos->Nombre_GrupoConjuntos ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Ultima Revision Plano</span>
                        <div class="detail-value">{{ $producto->Prod_Plano_Ultima_Revision ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Longitud de Pieza</span>
                        <div class="detail-value">{{ $producto->Prod_Longitud_de_Pieza ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Material MP</span>
                        <div class="detail-value">{{ $producto->Prod_Material_MP ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Diametro MP</span>
                        <div class="detail-value">{{ $producto->Prod_Diametro_de_MP ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Codigo MP</span>
                        <div class="detail-value">{{ $producto->Prod_Codigo_MP ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">Creado</span>
                        <div class="detail-value">{{ $producto->created_at ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado</span>
                        <div class="detail-value">{{ $producto->updated_at ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="show-card-footer">
            <div class="show-actions">
                <a href="{{ route('productos.index') }}" class="btn btn-secondary">Volver</a>
                <a href="{{ route('productos.edit', $producto->Id_Producto) }}" class="btn btn-primary">Editar</a>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/show.css') }}">
@stop
