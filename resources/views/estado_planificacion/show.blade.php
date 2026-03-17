@extends('adminlte::page')

@section('title', 'Ver Estado de Planificación')

@section('content_header')
    <div class="show-header">
        <h1 class="mb-0">Detalle del Estado de Planificación</h1>
        <p class="text-muted mb-0">Consulta completa del registro seleccionado</p>
    </div>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3 show-card estado-planificacion-show">
        <div class="card-header show-card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="card-title mb-1">Estado #{{ $estado_planificacion->Estado_Plani_Id }}</h3>
                <small class="text-muted">Información general y trazabilidad del registro</small>
            </div>

            <div class="mt-2 mt-md-0">
                @if($estado_planificacion->Status == 1)
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
                        <div class="detail-value">{{ $estado_planificacion->Estado_Plani_Id }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Nombre del Estado</span>
                        <div class="detail-value">{{ $estado_planificacion->Nombre_Estado }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <div class="detail-value">
                            @if($estado_planificacion->Status == 1)
                                <span class="badge badge-success detail-badge">Activo</span>
                            @else
                                <span class="badge badge-secondary detail-badge">Inactivo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Creado</span>
                        <div class="detail-value">
                            {{ $estado_planificacion->created_at ? $estado_planificacion->created_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado</span>
                        <div class="detail-value">
                            {{ $estado_planificacion->updated_at ? $estado_planificacion->updated_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado</span>
                        <div class="detail-value">
                            @if($estado_planificacion->deleted_at)
                                <span class="badge badge-danger detail-badge">
                                    {{ $estado_planificacion->deleted_at->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="badge badge-success detail-badge">No</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Creado por</span>
                        <div class="detail-value">{{ $estado_planificacion->created_by ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado por</span>
                        <div class="detail-value">{{ $estado_planificacion->updated_by ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado por</span>
                        <div class="detail-value">{{ $estado_planificacion->deleted_by ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer show-card-footer">
            <div class="show-actions">
                <a href="{{ route('estado_planificacion.index') }}" class="btn btn-secondary">
                    Volver
                </a>
                <a href="{{ route('estado_planificacion.edit', $estado_planificacion->Estado_Plani_Id) }}" class="btn btn-primary">
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
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/estado_planificacion_show.css') }}">
@stop