@extends('adminlte::page')

@section('title', 'Editar Proveedor')
{{-- resources\views\proveedores\edit.blade.php --}}
@section('content_header')
    <h1>Editar Proveedor</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    <form action="{{ route('proveedores.update', $proveedor->Prov_Id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="Prov_Nombre">Nombre:</label>
            <input type="text" class="form-control" id="Prov_Nombre" name="Prov_Nombre" value="{{ $proveedor->Prov_Nombre }}" required>
        </div>
        <div class="form-group">
            <label for="Prov_Detalle">Detalle:</label>
            <input type="text" class="form-control" id="Prov_Detalle" name="Prov_Detalle" value="{{ $proveedor->Prov_Detalle }}" required>
        </div>
        <div class="form-group">
            <label for="Nombre_Contacto">Contacto:</label>
            <input type="text" class="form-control" id="Nombre_Contacto" name="Nombre_Contacto" value="{{ $proveedor->Nombre_Contacto }}" required>
        </div>
        <div class="form-group">
            <label for="Nro_Telefono">Teléfono:</label>
            <input type="text" class="form-control" id="Nro_Telefono" name="Nro_Telefono" value="{{ $proveedor->Nro_Telefono }}" required>
        </div>
        <div class="form-group">
            <label for="reg_Status">Estado del Proveedor:</label>
            <select name="reg_Status" id="reg_Status" class="form-control">
                <option value="1" {{ $proveedor->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ $proveedor->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('proveedores.index') }}" class="btn btn-default">Cancelar</a>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/proveedores_edit.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Almacenar los valores originales
        const originalValues = {
            Prov_Nombre: $('#Prov_Nombre').val(),
            Prov_Detalle: $('#Prov_Detalle').val(),
            Nombre_Contacto: $('#Nombre_Contacto').val(),
            Nro_Telefono: $('#Nro_Telefono').val(),
            reg_Status: $('#reg_Status').val()  // Añadir el estado inicial de reg_Status
        };

        $('form').on('submit', function(e) {
            e.preventDefault();

            // Comprobar cambios
            var hasChanges = false;
            for (const key in originalValues) {
                if (originalValues[key] !== $('#' + key).val()) {
                    hasChanges = true;
                    break;
                }
            }

            if (!hasChanges) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin cambios',
                    text: 'No se detectaron cambios en el formulario.',
                    showConfirmButton: true,
                });
                return;
            }

            // Enviar datos si hay cambios
            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Proveedor actualizado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function() {
                        window.location.href = "{{ route('proveedores.index') }}";
                    }, 1500);
                },
                error: function(response) {
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Ocurrió un error al actualizar el proveedor',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });
    });
</script>
@stop

