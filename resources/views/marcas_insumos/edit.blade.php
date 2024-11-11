@extends('adminlte::page')

@section('title', 'Editar Marca de Insumo')

@section('content_header')
    <h1>Editar Marca de Insumo</h1>
@stop

@section('content')
    <form action="{{ route('marcas_insumos.update', $marca->Id_Marca) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="Nombre_marca">Nombre de Marca:</label>
            <input type="text" class="form-control" id="Nombre_marca" name="Nombre_marca" value="{{ $marca->Nombre_marca }}" required>
        </div>

        <div class="form-group">
            <label for="Id_Proveedor">Proveedor:</label>
            <select name="Id_Proveedor" id="Id_Proveedor" class="form-control">
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->Prov_Id }}" {{ $marca->Id_Proveedor == $proveedor->Prov_Id ? 'selected' : '' }}>
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
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Almacenar los valores originales de los campos al cargar la página
    const originalValues = {
        Nombre_marca: $('#Nombre_marca').val(),
        Id_Proveedor: $('#Id_Proveedor').val(),
        reg_Status: $('#reg_Status').val()
    };

    // Detectar cambios antes de enviar el formulario
    $('form').on('submit', function(e) {
        e.preventDefault();

        let hasChanges = false;
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

        // Enviar datos vía AJAX si hubo cambios
        var formData = $(this).serialize();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Marca de insumo actualizada correctamente',
                    showConfirmButton: false,
                    timer: 1500
                });
                setTimeout(function() {
                    window.location.href = "{{ route('marcas_insumos.index') }}";
                }, 1500);
            },
            error: function(response) {
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: 'Ocurrió un error al actualizar la marca de insumo',
                    showConfirmButton: true,
                });
            }
        });
    });
});
</script>
@stop
