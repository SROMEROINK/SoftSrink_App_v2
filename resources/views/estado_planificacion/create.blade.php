@extends('adminlte::page')

@section('title', 'Crear Estado de Planificación')

@section('content_header')
    <h1>Crear Estado de Planificación</h1>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Nuevo estado</h3>
        </div>
        <form method="POST"
              action="{{ route('estado_planificacion.store') }}"
              data-ajax="true"
              data-redirect-url="{{ route('estado_planificacion.index') }}">
            @csrf

            <div class="card-body">
                @if($ultimoEstado)
                    <div class="alert alert-info">
                        Último ID registrado: <strong>{{ $ultimoEstado->Estado_Plani_Id }}</strong> -
                        Último estado: <strong>{{ $ultimoEstado->Nombre_Estado }}</strong>
                    </div>
                @endif

                <div class="form-group">
                    <label for="Nombre_Estado">Nombre del Estado</label>
                    <input type="text"
                           name="Nombre_Estado"
                           id="Nombre_Estado"
                           class="form-control"
                           maxlength="50"
                           required>
                </div>

                <div class="form-group">
                    <label for="Status">Status</label>
                    <select name="Status" id="Status" class="form-control" required>
                        <option value="1" selected>Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('estado_planificacion.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </form>
    </div>
@stop

@section('css')
    @section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_create.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/estado_planificacion_create.css') }}">
@stop


@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop