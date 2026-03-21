@extends('adminlte::page')

@section('title', 'Crear Subcategoria de Producto')

@section('content_header')
    <h1>Crear Subcategoria de Producto</h1>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Nueva subcategoria de producto</h3>
        </div>

        <form method="POST"
              action="{{ route('producto_subcategoria.store') }}"
              data-ajax="true"
              data-redirect-url="{{ route('producto_subcategoria.index') }}">
            @csrf

            <div class="card-body">
                @if($ultimaSubcategoria)
                    <div class="alert alert-info">
                        Ultimo ID registrado: <strong>{{ $ultimaSubcategoria->Id_SubCategoria }}</strong> -
                        Ultima subcategoria: <strong>{{ $ultimaSubcategoria->Nombre_SubCategoria }}</strong>
                    </div>
                @endif

                <div class="form-group">
                    <label for="Id_Categoria">Categoria</label>
                    <select name="Id_Categoria" id="Id_Categoria" class="form-control" required>
                        <option value="">Seleccionar</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->Id_Categoria }}">{{ $categoria->Nombre_Categoria }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="Nombre_SubCategoria">Nombre de la Subcategoria</label>
                    <input type="text" name="Nombre_SubCategoria" id="Nombre_SubCategoria" class="form-control" maxlength="100" required>
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
                <a href="{{ route('producto_subcategoria.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_create.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/subcategorias/create.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop
