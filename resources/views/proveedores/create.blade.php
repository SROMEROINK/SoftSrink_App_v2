@extends('adminlte::page')
{{-- resources/views/proveedores/create.blade.php --}}

@section('title', 'Registrar Nuevo Proveedor')

@section('content_header')
    <h1>Registrar Nuevo Proveedor</h1>
@stop

@section('content')
    @include('partials.navigation')

    <form method="POST"
          action="{{ route('proveedores.store') }}"
          data-ajax="true"
          data-redirect-url="{{ route('proveedores.index') }}"
          data-success-message="Proveedor creado correctamente">
        @csrf

        <div class="card proveedor-create-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Alta de proveedor</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Prov_Nombre">Nombre del Proveedor</label>
                            <input type="text"
                                   name="Prov_Nombre"
                                   id="Prov_Nombre"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Prov_Detalle">Detalle</label>
                            <input type="text"
                                   name="Prov_Detalle"
                                   id="Prov_Detalle"
                                   class="form-control"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Nombre_Contacto">Nombre de Contacto</label>
                            <input type="text"
                                   name="Nombre_Contacto"
                                   id="Nombre_Contacto"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Nro_Telefono">Nro Teléfono</label>
                            <input type="text"
                                   name="Nro_Telefono"
                                   id="Nro_Telefono"
                                   class="form-control"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Es_Proveedor_MP">Proveedor MP</label>
                            <select name="Es_Proveedor_MP" id="Es_Proveedor_MP" class="form-control" required>
                                <option value="1">Sí</option>
                                <option value="0" selected>No</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Es_Proveedor_Herramientas">Proveedor Herramientas</label>
                            <select name="Es_Proveedor_Herramientas" id="Es_Proveedor_Herramientas" class="form-control" required>
                                <option value="1">Sí</option>
                                <option value="0" selected>No</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="reg_Status">Estado</label>
                            <select name="reg_Status" id="reg_Status" class="form-control" required>
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Proveedor</button>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/proveedores_create.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop
