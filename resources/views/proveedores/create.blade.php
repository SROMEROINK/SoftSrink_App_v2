@extends('adminlte::page')
{{-- resources\views\proveedores\create.blade.php --}}
@section('title', 'Registrar Nuevo Proveedor')

@section('content_header')
    <h1>Registrar Nuevo Proveedor</h1>
@stop

@section('content')
<form method="post" action="{{ route('proveedores.store') }}">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label for="Prov_Nombre">Nombre del Proveedor:</label>
                <input type="text" name="Prov_Nombre" id="Prov_Nombre" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="Prov_Detalle">Detalle:</label>
                <input type="text" name="Prov_Detalle" id="Prov_Detalle" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="Nombre_Contacto">Nombre Contacto:</label>
                <input type="text" name="Nombre_Contacto" id="Nombre_Contacto" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="Nro_Telefono">Nro Teléfono:</label>
                <input type="text" name="Nro_Telefono" id="Nro_Telefono" class="form-control" required>
            </div>
        </div>
    </div>
    <div class="btn-der">
        <input type="submit" class="btn btn-primary" value="Registrar Proveedor">
    </div>
</form>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/proveedores_create.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Proveedor actualizado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function() {
                        window.location.href = '{{ route('proveedores.index') }}';
                    }, 1500);
                  
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors || {};
                    var errorMessages = Object.values(errors).map(function(error) {
                        return error.join('<br>');
                    });
    
                    var errorMessage = translateErrorMessages(xhr.responseJSON.message);
    
                    Swal.fire({
                        icon: 'error',
                        title: 'Errores de validación',
                        html: errorMessages.join('<br>') + '<br>' + errorMessage,
                        confirmButtonText: 'Corregir'
                    });
                }
            });
        });
    
        function translateErrorMessages(message) {
            if (message.includes('Duplicate entry')) {
                return 'Entrada duplicada: El nombre ya existe en la base de datos.';
            }
            return message; // retorna el mensaje original si no es de tipo 'Duplicate entry'
        }
    });
    </script>
@stop
