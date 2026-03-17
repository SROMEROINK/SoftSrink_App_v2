@extends('adminlte::page')

@section('title', 'Editar Marca de Insumo')

@section('content_header')
    <h1>Editar Marca de Insumo</h1>
@stop

@section('content')

    @include('partials.navigation')
    <form action="{{ route('marcas_insumos.update', $marca->Id_Marca) }}"
          method="POST"
          data-ajax="true"
          data-edit-check="true"
          data-exclude-fields="_token,_method"
          data-redirect-url="{{ route('marcas_insumos.index') }}"
          data-success-message="Marca de insumo actualizada correctamente">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="Nombre_marca">Nombre de Marca:</label>
            <input type="text"
                   class="form-control"
                   id="Nombre_marca"
                   name="Nombre_marca"
                   value="{{ $marca->Nombre_marca }}"
                   required>
        </div>

        <div class="form-group">
            <label for="Id_Proveedor">Proveedor:</label>
            <select name="Id_Proveedor" id="Id_Proveedor" class="form-control">
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->Prov_Id }}"
                        {{ $marca->Id_Proveedor == $proveedor->Prov_Id ? 'selected' : '' }}>
                        {{ $proveedor->Prov_Nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="reg_Status">Estado:</label>
            <select name="reg_Status" id="reg_Status" class="form-control">
                <option value="1" {{ $marca->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ $marca->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('marcas_insumos.index') }}" class="btn btn-default">Cancelar</a>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/marcas_insumos_edit.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop