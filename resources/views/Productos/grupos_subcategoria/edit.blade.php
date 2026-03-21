@extends('adminlte::page')

@section('title', 'Editar Grupo de Subcategoria')

@section('content_header')
    <h1>Editar Grupo de Subcategoria</h1>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Modificar grupo de subcategoria</h3>
        </div>

        <form method="POST"
              action="{{ route('producto_grupo_subcategoria.update', $grupo->Id_GrupoSubCategoria) }}"
              data-ajax="true"
              data-edit-check="true"
              data-redirect-url="{{ route('producto_grupo_subcategoria.index') }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="form-group">
                    <label for="Nombre_GrupoSubCategoria">Nombre del Grupo</label>
                    <input type="text" name="Nombre_GrupoSubCategoria" id="Nombre_GrupoSubCategoria" class="form-control" maxlength="100" value="{{ old('Nombre_GrupoSubCategoria', $grupo->Nombre_GrupoSubCategoria) }}" required>
                </div>

                <div class="form-group">
                    <label for="reg_Status">Estado</label>
                    <select name="reg_Status" id="reg_Status" class="form-control" required>
                        <option value="1" {{ old('reg_Status', $grupo->reg_Status) == 1 ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('reg_Status', $grupo->reg_Status) == 0 ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('producto_grupo_subcategoria.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_edit.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/grupos_subcategoria/edit.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop
