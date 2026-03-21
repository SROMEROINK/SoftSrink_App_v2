@extends('adminlte::page')
{{-- resources/views/proveedores/show.blade.php --}}

@section('title', 'Ver Proveedor')

@section('content_header')
    <div class="show-header">
        <h1 class="mb-0">Detalle del Proveedor</h1>
        <p class="text-muted mb-0">Consulta completa del registro seleccionado</p>
    </div>
@stop

@section('content')
    <div class="card mt-3 show-card proveedores-show">
        <div class="card-header show-card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="card-title mb-1">Proveedor #{{ $proveedor->Prov_Id }}</h3>
                <small class="text-muted">Información general y trazabilidad del registro</small>
            </div>

            <div class="mt-2 mt-md-0">
                @if($proveedor->reg_Status == 1)
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
                        <div class="detail-value">{{ $proveedor->Prov_Id }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Nombre</span>
                        <div class="detail-value">{{ $proveedor->Prov_Nombre }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Detalle</span>
                        <div class="detail-value">{{ $proveedor->Prov_Detalle ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Proveedor MP</span>
                        <div class="detail-value">
                            @if((int) $proveedor->Es_Proveedor_MP === 1)
                                <span class="badge badge-info detail-badge">Sí</span>
                            @else
                                <span class="badge badge-secondary detail-badge">No</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Proveedor Herramientas</span>
                        <div class="detail-value">
                            @if((int) $proveedor->Es_Proveedor_Herramientas === 1)
                                <span class="badge badge-info detail-badge">Sí</span>
                            @else
                                <span class="badge badge-secondary detail-badge">No</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Estado</span>
                        <div class="detail-value">
                            @if($proveedor->reg_Status == 1)
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
                        <span class="detail-label">Nombre Contacto</span>
                        <div class="detail-value">{{ $proveedor->Nombre_Contacto ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Nro Teléfono</span>
                        <div class="detail-value">{{ $proveedor->Nro_Telefono ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Creado</span>
                        <div class="detail-value">
                            {{ $proveedor->created_at ? $proveedor->created_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado</span>
                        <div class="detail-value">
                            {{ $proveedor->updated_at ? $proveedor->updated_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado</span>
                        <div class="detail-value">
                            @if($proveedor->deleted_at)
                                <span class="badge badge-danger detail-badge">
                                    {{ $proveedor->deleted_at->format('d/m/Y H:i') }}
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
                            {{ $proveedor->createdBy?->name ?? $proveedor->created_by ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Actualizado por</span>
                        <div class="detail-value">
                            {{ $proveedor->updatedBy?->name ?? $proveedor->updated_by ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Eliminado por</span>
                        <div class="detail-value">
                            {{ $proveedor->deletedBy?->name ?? $proveedor->deleted_by ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer show-card-footer">
            <div class="show-actions">
                <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
                    Volver
                </a>
                <a href="{{ route('proveedores.edit', $proveedor->Prov_Id) }}" class="btn btn-primary">
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
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/proveedores_show.css') }}">
@stop
