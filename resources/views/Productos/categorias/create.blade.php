@extends('adminlte::page')

@section('title', 'Crear Categoria de Producto')

@section('content_header')
    <h1>Crear Categoria de Producto</h1>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Nueva categoria de producto</h3>
        </div>

        <form method="POST"
              action="{{ route('producto_categoria.store') }}"
              data-ajax="true"
              data-redirect-url="{{ route('producto_categoria.index') }}">
            @csrf

            <div class="card-body">
                @if($ultimaCategoria)
                    <div class="alert alert-info">
                        Ultimo ID registrado: <strong>{{ $ultimaCategoria->Id_Categoria }}</strong> -
                        Ultima categoria: <strong>{{ $ultimaCategoria->Nombre_Categoria }}</strong>
                    </div>
                @endif

                <div class="form-group">
                    <label for="Nombre_Categoria">Nombre de la Categoria</label>
                    <input type="text" name="Nombre_Categoria" id="Nombre_Categoria" class="form-control" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="reg_Status">Estado</label>
                    <select name="reg_Status" id="reg_Status" class="form-control" required>
                        <option value="1" selected>Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('producto_categoria.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_create.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/categorias/create.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop
