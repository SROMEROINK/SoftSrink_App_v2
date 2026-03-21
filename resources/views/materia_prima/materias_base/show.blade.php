@extends('adminlte::page')

@section('title', 'Ver Materia Base')

@section('content_header')
    <div class="show-header">
        <h1 class="mb-0">Detalle de Materia Base</h1>
        <p class="text-muted mb-0">Consulta completa del registro seleccionado</p>
    </div>
@stop

@section('content')
    <div class="card mt-3 show-card materia-base-show">
        <div class="card-header show-card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="card-title mb-1">Materia Base #{{ $materiaBase->Id_Materia_Prima }}</h3>
                <small class="text-muted">Información general y trazabilidad del registro</small>
            </div>

            <div class="mt-2 mt-md-0">
                @if((int) $materiaBase->reg_Status === 1)
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
                        <div class="detail-value">{{ $materiaBase->Id_Materia_Prima }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Nombre de Materia</span>
                        <div class="detail-value">{{ $materiaBase->Nombre_Materia }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Estado</span>
                        <div class="detail-value">
                            @if((int) $materiaBase->reg_Status === 1)
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
                            {{ $materiaBase->created_at ? $materiaBase->created_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado</span>
                        <div class="detail-value">
                            {{ $materiaBase->updated_at ? $materiaBase->updated_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado</span>
                        <div class="detail-value">
                            @if($materiaBase->deleted_at)
                                <span class="badge badge-danger detail-badge">
                                    {{ $materiaBase->deleted_at->format('d/m/Y H:i') }}
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
                        <div class="detail-value">{{ $materiaBase->created_by ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado por</span>
                        <div class="detail-value">{{ $materiaBase->updated_by ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado por</span>
                        <div class="detail-value">{{ $materiaBase->deleted_by ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer show-card-footer">
            <div class="show-actions">
                <a href="{{ route('mp_materia_prima.index') }}" class="btn btn-secondary">
                    Volver
                </a>
                <a href="{{ route('mp_materia_prima.edit', $materiaBase->Id_Materia_Prima) }}" class="btn btn-primary">
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
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_base_show.css') }}">
@stop
