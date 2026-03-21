@extends('adminlte::page')
{{--resources\views\marcas_insumos\show.blade.php --}}
@section('title', 'Ver Marca de Insumo')

@section('content_header')
    <div class="show-header">
        <h1 class="mb-0">Detalle de la Marca de Insumo</h1>
        <p class="text-muted mb-0">Consulta completa del registro seleccionado</p>
    </div>
@stop

@section('content')
    <div class="card mt-3 show-card marcas-insumos-show">
        <div class="card-header show-card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="card-title mb-1">Marca #{{ $marca->Id_Marca }}</h3>
                <small class="text-muted">Información general y trazabilidad del registro</small>
            </div>

            <div class="mt-2 mt-md-0">
                @if($marca->reg_Status == 1)
                    <span class="badge badge-success detail-badge">Activo</span>
                @else
                    <span class="badge badge-secondary detail-badge">Inactivo</span>
                @endif
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">ID</span>
                        <div class="detail-value">{{ $marca->Id_Marca }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Nombre de Marca</span>
                        <div class="detail-value">{{ $marca->Nombre_marca }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Proveedor</span>
                        <div class="detail-value">
                            {{ $marca->proveedor?->Prov_Nombre ?? 'No asignado' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Estado</span>
                        <div class="detail-value">
                            @if($marca->reg_Status == 1)
                                <span class="badge badge-success detail-badge">Activo</span>
                            @else
                                <span class="badge badge-secondary detail-badge">Inactivo</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Creado</span>
                        <div class="detail-value">
                            {{ $marca->created_at ? $marca->created_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado</span>
                        <div class="detail-value">
                            {{ $marca->updated_at ? $marca->updated_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado</span>
                        <div class="detail-value">
                            @if($marca->deleted_at)
                                <span class="badge badge-danger detail-badge">
                                    {{ $marca->deleted_at->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="badge badge-success detail-badge">No</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Creado por</span>
                        <div class="detail-value">
                            {{ $marca->createdBy?->name ?? $marca->created_by ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado por</span>
                        <div class="detail-value">
                            {{ $marca->updatedBy?->name ?? $marca->updated_by ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado por</span>
                        <div class="detail-value">
                            {{ $marca->deletedBy?->name ?? $marca->deleted_by ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer show-card-footer">
            <div class="show-actions">
                <a href="{{ route('marcas_insumos.index') }}" class="btn btn-secondary">
                    Volver
                </a>
                <a href="{{ route('marcas_insumos.edit', ['marcas_insumo' => $marca->Id_Marca]) }}" class="btn btn-primary">
                    Editar
                </a>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/marcas_insumos_show.css') }}">
@stop
