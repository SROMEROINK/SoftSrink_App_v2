@extends('adminlte::page')

@section('title', 'Editar Categoria de Producto')

@section('content_header')
    <h1>Editar Categoria de Producto</h1>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Editar registro #{{ $categoria->Id_Categoria }}</h3>
        </div>

        <form method="POST"
              action="{{ route('producto_categoria.update', $categoria->Id_Categoria) }}"
              data-edit-check="true"
              data-exclude-fields="_token,_method"
              data-redirect-url="{{ route('producto_categoria.index') }}"
              data-success-message="Categoria de producto actualizada correctamente.">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="form-group">
                    <label for="Nombre_Categoria">Nombre de la Categoria</label>
                    <input type="text"
                           name="Nombre_Categoria"
                           id="Nombre_Categoria"
                           class="form-control"
                           value="{{ old('Nombre_Categoria', $categoria->Nombre_Categoria) }}"
                           maxlength="100"
                           required>
                </div>

                <div class="form-group">
                    <label for="reg_Status">Estado</label>
                    <select name="reg_Status" id="reg_Status" class="form-control" required>
                        <option value="1" {{ old('reg_Status', $categoria->reg_Status) == 1 ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('reg_Status', $categoria->reg_Status) == 0 ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('producto_categoria.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_create.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/categorias/edit.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop
