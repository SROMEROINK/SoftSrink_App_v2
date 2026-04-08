@extends('adminlte::page')

@section('title', 'Fabricacion - Resumen OF Fabricadas')

@section('content_header')
<x-header-card
    title="Fabricacion - Resumen de OF Fabricadas"
    buttonRoute="{{ route('fabricacion.index') }}"
    buttonText="Volver a fabricacion"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        <strong>Resumen de OF fabricadas:</strong>
        esta vista consolida una fila por <strong>OF</strong>, mostrando producto, categoria, maquina, familia,
        fecha del pedido, fecha de inicio y fin de fabricacion, y el total de piezas fabricadas segun el filtro.
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format((int) ($summary['total_parciales'] ?? 0), 0, ',', '.') }}</h3>
                    <p>Parciales fabricados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-stream"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format((int) ($summary['total_piezas'] ?? 0), 0, ',', '.') }}</h3>
                    <p>Piezas fabricadas segun filtro</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format((int) ($summary['total_of'] ?? 0), 0, ',', '.') }}</h3>
                    <p>OF fabricadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form method="GET" action="{{ route('fabricacion.resumenMensual') }}" class="fabricacion-summary-toolbar">
                <div class="fabricacion-summary-filter">
                    <label for="filtro_anio">Anio</label>
                    <select name="filtro_anio" id="filtro_anio" class="form-control form-control-sm">
                        <option value="">Todos los anios</option>
                        @foreach(($years ?? []) as $anio)
                            <option value="{{ $anio }}" {{ (string) request('filtro_anio') === (string) $anio ? 'selected' : '' }}>
                                {{ $anio }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fabricacion-summary-filter">
                    <label for="filtro_mes">Mes</label>
                    <select name="filtro_mes" id="filtro_mes" class="form-control form-control-sm">
                        <option value="">Todos los meses</option>
                        @foreach(($months ?? []) as $numero => $nombre)
                            <option value="{{ $numero }}" {{ (string) request('filtro_mes') === (string) $numero ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fabricacion-summary-filter fabricacion-summary-filter--wide">
                    <label for="filtro_familia">Familia</label>
                    <select name="filtro_familia" id="filtro_familia" class="form-control form-control-sm">
                        <option value="">Todas las familias</option>
                        @foreach(($familias ?? []) as $familia)
                            <option value="{{ $familia }}" {{ (string) request('filtro_familia') === (string) $familia ? 'selected' : '' }}>
                                {{ $familia }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fabricacion-summary-filter">
                    <label for="filtro_categoria">Categoria</label>
                    <select name="filtro_categoria" id="filtro_categoria" class="form-control form-control-sm">
                        <option value="">Todas las categorias</option>
                        @foreach(($categorias ?? []) as $categoria)
                            <option value="{{ $categoria }}" {{ request('filtro_categoria') === $categoria ? 'selected' : '' }}>
                                {{ $categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fabricacion-summary-filter">
                    <label for="filtro_nro_of">Nro OF</label>
                    <input type="number" name="filtro_nro_of" id="filtro_nro_of" class="form-control form-control-sm" value="{{ request('filtro_nro_of') }}" placeholder="Buscar OF">
                </div>

                <div class="fabricacion-summary-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                    <a href="{{ route('fabricacion.resumenMensual') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped fabricacion-summary-table">
                    <thead>
                        <tr>
                            <th>Nro OF</th>
                            <th>Codigo</th>
                            <th>Descripcion</th>
                            <th>Categoria</th>
                            <th>Nro Maquina</th>
                            <th>Familia</th>
                            <th>Fecha Pedido</th>
                            <th>Cant. Pedido</th>
                            <th>Inicio Fabricacion</th>
                            <th>Fin Fabricacion</th>
                            <th>Piezas Fabricadas</th>
                            <th>Piezas Entregadas Mes</th>
                            <th>Parciales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ number_format((int) $row->Nro_OF, 0, ',', '.') }}</td>
                                <td>{{ $row->Prod_Codigo }}</td>
                                <td>{{ $row->Prod_Descripcion }}</td>
                                <td>{{ $row->Nombre_Categoria }}</td>
                                <td>{{ $row->Nro_Maquina ?: '-' }}</td>
                                <td>{{ $row->Familia_Maquina ?: 'Sin familia' }}</td>
                                <td>{{ $row->Fecha_del_Pedido ? \Carbon\Carbon::parse($row->Fecha_del_Pedido)->format('d/m/Y') : '' }}</td>
                                <td>{{ number_format((int) $row->Cant_Pedido, 0, ',', '.') }}</td>
                                <td>{{ $row->Fecha_Inicio_Fabricacion ? \Carbon\Carbon::parse($row->Fecha_Inicio_Fabricacion)->format('d/m/Y') : '' }}</td>
                                <td>{{ $row->Fecha_Fin_Fabricacion ? \Carbon\Carbon::parse($row->Fecha_Fin_Fabricacion)->format('d/m/Y') : '' }}</td>
                                <td>{{ number_format((int) $row->Piezas_Fabricadas, 0, ',', '.') }}</td>
                                <td>{{ number_format((int) $row->Piezas_Entregadas_Mes, 0, ',', '.') }}</td>
                                <td>{{ number_format((int) $row->Parciales_Registrados, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center">No se encontraron registros para ese filtro.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="fabricacion-summary-footer">
                <div class="fabricacion-summary-footer__text">
                    Mostrando {{ $rows->firstItem() ?? 0 }} a {{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }} OF fabricadas
                </div>
                <div class="fabricacion-summary-footer__pagination">
                    {{ $rows->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/summary-boxes.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_index.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_resumen_mensual.css') }}">
@endsection
