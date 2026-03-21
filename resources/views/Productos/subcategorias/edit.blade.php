@extends('adminlte::page')

@section('title', 'Editar Subcategoria de Producto')

@section('content_header')
    <h1>Editar Subcategoria de Producto</h1>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Editar registro #{{ $subcategoria->Id_SubCategoria }}</h3>
        </div>

        <form method="POST"
              action="{{ route('producto_subcategoria.update', $subcategoria->Id_SubCategoria) }}"
              data-edit-check="true"
              data-exclude-fields="_token,_method"
              data-redirect-url="{{ route('producto_subcategoria.index') }}"
              data-success-message="Subcategoria de producto actualizada correctamente.">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="form-group">
                    <label for="Id_Categoria">Categoria</label>
                    <select name="Id_Categoria" id="Id_Categoria" class="form-control" required>
                        <option value="">Seleccionar</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->Id_Categoria }}" {{ old('Id_Categoria', $subcategoria->Id_Categoria) == $categoria->Id_Categoria ? 'selected' : '' }}>
                                {{ $categoria->Nombre_Categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="Nombre_SubCategoria">Nombre de la Subcategoria</label>
                    <input type="text"
                           name="Nombre_SubCategoria"
                           id="Nombre_SubCategoria"
                           class="form-control"
                           value="{{ old('Nombre_SubCategoria', $subcategoria->Nombre_SubCategoria) }}"
                           maxlength="100"
                           required>
                </div>

                <div class="form-group">
                    <label for="reg_Status">Estado</label>
                    <select name="reg_Status" id="reg_Status" class="form-control" required>
                        <option value="1" {{ old('reg_Status', $subcategoria->reg_Status) == 1 ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('reg_Status', $subcategoria->reg_Status) == 0 ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('producto_subcategoria.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_create.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/subcategorias/edit.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop
