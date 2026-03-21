@extends('adminlte::page')

@section('title', 'Editar Estado de Planificación')

@section('content_header')
    <h1>Editar Estado de Planificación</h1>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Editar registro #{{ $estado->Estado_Plani_Id }}</h3>
        </div>

        <form method="POST"
              action="{{ route('estado_planificacion.update', $estado->Estado_Plani_Id) }}"
              data-edit-check="true"
              data-exclude-fields="_token,_method"
              data-redirect-url="{{ route('estado_planificacion.index') }}"
              data-success-message="Estado de planificación actualizado correctamente.">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="form-group">
                    <label for="Nombre_Estado">Nombre del Estado</label>
                    <input type="text"
                           name="Nombre_Estado"
                           id="Nombre_Estado"
                           class="form-control"
                           value="{{ old('Nombre_Estado', $estado->Nombre_Estado) }}"
                           maxlength="50"
                           required>
                </div>

                <div class="form-group">
                    <label for="Status">Status</label>
                    <select name="Status" id="Status" class="form-control" required>
                        <option value="1" {{ old('Status', $estado->Status) == 1 ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('Status', $estado->Status) == 0 ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('estado_planificacion.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/estado_planificacion_edit.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop
