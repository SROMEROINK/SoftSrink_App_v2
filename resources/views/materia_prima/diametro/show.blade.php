@extends('adminlte::page')

@section('title', 'Ver Diámetro')

@section('content_header')
    <div class="show-header">
        <h1 class="mb-0">Detalle de Diámetro</h1>
        <p class="text-muted mb-0">Consulta completa del registro seleccionado</p>
    </div>
@stop

@section('content')
    <div class="card mt-3 show-card diametro-show">
        <div class="card-header show-card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="card-title mb-1">Diámetro #{{ $diametro->Id_Diametro }}</h3>
                <small class="text-muted">Información general y trazabilidad del registro</small>
            </div>

            <div class="mt-2 mt-md-0">
                @if((int) $diametro->reg_Status === 1)
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
                        <div class="detail-value">{{ $diametro->Id_Diametro }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Valor Diámetro</span>
                        <div class="detail-value">{{ $diametro->Valor_Diametro }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Estado</span>
                        <div class="detail-value">
                            @if((int) $diametro->reg_Status === 1)
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
                            {{ $diametro->created_at ? $diametro->created_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado</span>
                        <div class="detail-value">
                            {{ $diametro->updated_at ? $diametro->updated_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado</span>
                        <div class="detail-value">
                            @if($diametro->deleted_at)
                                <span class="badge badge-danger detail-badge">
                                    {{ $diametro->deleted_at->format('d/m/Y H:i') }}
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
                        <div class="detail-value">{{ $diametro->created_by ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado por</span>
                        <div class="detail-value">{{ $diametro->updated_by ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado por</span>
                        <div class="detail-value">{{ $diametro->deleted_by ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer show-card-footer">
            <div class="show-actions">
                <a href="{{ route('mp_diametro.index') }}" class="btn btn-secondary">
                    Volver
                </a>
                <a href="{{ route('mp_diametro.edit', $diametro->Id_Diametro) }}" class="btn btn-primary">
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
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_diametro_show.css') }}">
@stop
