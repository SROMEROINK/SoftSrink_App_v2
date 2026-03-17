{{--resources\views\marcas_insumos\create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Crear Marca de Insumo')

@section('content_header')
    <h1>Crear Marca de Insumo</h1>
@stop

@section('content')
    <form method="POST"
          action="{{ route('marcas_insumos.store') }}"
          data-ajax="true"
          data-redirect-url="{{ route('marcas_insumos.index') }}"
          data-success-message="Marca de insumo creada exitosamente">
        @csrf

        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="Nombre_marca">Nombre de Marca</label>
                    <input type="text"
                           name="Nombre_marca"
                           id="Nombre_marca"
                           class="form-control"
                           required>
                </div>

                <div class="form-group">
                    <label for="Id_Proveedor">Proveedor</label>
                    <select name="Id_Proveedor" id="Id_Proveedor" class="form-control" required>
                        <option value="">Seleccione un proveedor</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->Prov_Id }}">
                                {{ $proveedor->Prov_Nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-3 text-right">
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="{{ route('marcas_insumos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/marcas_insumos_create.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop