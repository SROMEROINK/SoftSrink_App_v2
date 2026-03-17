@extends('adminlte::page')

@section('title', 'Editar Proveedor')
{{-- resources/views/proveedores/edit.blade.php --}}

@section('content_header')
    <h1>Editar Proveedor</h1>
@stop

@section('content')
    @include('partials.navigation')

    <form action="{{ route('proveedores.update', $proveedor->Prov_Id) }}"
          method="POST"
          data-ajax="true"
          data-edit-check="true"
          data-exclude-fields="_token,_method"
          data-redirect-url="{{ route('proveedores.index') }}"
          data-success-message="Proveedor actualizado correctamente">
        @csrf
        @method('PUT')

        <div class="card proveedor-edit-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Modificar datos del proveedor</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Prov_Nombre">Nombre</label>
                            <input type="text"
                                   class="form-control"
                                   id="Prov_Nombre"
                                   name="Prov_Nombre"
                                   value="{{ old('Prov_Nombre', $proveedor->Prov_Nombre) }}"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Prov_Detalle">Detalle</label>
                            <input type="text"
                                   class="form-control"
                                   id="Prov_Detalle"
                                   name="Prov_Detalle"
                                   value="{{ old('Prov_Detalle', $proveedor->Prov_Detalle) }}"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Nombre_Contacto">Nombre de Contacto</label>
                            <input type="text"
                                   class="form-control"
                                   id="Nombre_Contacto"
                                   name="Nombre_Contacto"
                                   value="{{ old('Nombre_Contacto', $proveedor->Nombre_Contacto) }}"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Nro_Telefono">Nro Teléfono</label>
                            <input type="text"
                                   class="form-control"
                                   id="Nro_Telefono"
                                   name="Nro_Telefono"
                                   value="{{ old('Nro_Telefono', $proveedor->Nro_Telefono) }}"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Es_Proveedor_MP">Proveedor de MP</label>
                            <select name="Es_Proveedor_MP" id="Es_Proveedor_MP" class="form-control">
                                <option value="1" {{ old('Es_Proveedor_MP', $proveedor->Es_Proveedor_MP) == 1 ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('Es_Proveedor_MP', $proveedor->Es_Proveedor_MP) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Es_Proveedor_Herramientas">Proveedor de Herramientas</label>
                            <select name="Es_Proveedor_Herramientas" id="Es_Proveedor_Herramientas" class="form-control">
                                <option value="1" {{ old('Es_Proveedor_Herramientas', $proveedor->Es_Proveedor_Herramientas) == 1 ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('Es_Proveedor_Herramientas', $proveedor->Es_Proveedor_Herramientas) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="reg_Status">Estado del Proveedor</label>
                            <select name="reg_Status" id="reg_Status" class="form-control">
                                <option value="1" {{ old('reg_Status', $proveedor->reg_Status) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('reg_Status', $proveedor->reg_Status) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/proveedores_edit.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop