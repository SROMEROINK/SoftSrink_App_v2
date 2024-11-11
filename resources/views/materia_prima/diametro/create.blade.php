@extends('adminlte::page')
{{-- resources\views\materia_prima\diametro\create.blade.php --}}
@section('title', 'Registrar Nuevo Diámetro')

@section('content_header')
    <h1>Registrar Nuevo Diámetro</h1>
@stop

@section('content')
<form method="post" action="{{ route('mp_diametro.store') }}">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label for="Valor_Diametro">Valor del Diámetro:</label>
                <input type="text" name="Valor_Diametro" id="Valor_Diametro" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="reg_Status">Estado del Diámetro:</label>
                <select name="reg_Status" id="reg_Status" class="form-control" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
        </div>
    </div>
    <div class="btn-der">
        <input type="submit" class="btn btn-primary" value="Registrar Diámetro">
    </div>
</form>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_diametro_create.css') }}">
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
                        title: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Redirige a la vista index en caso de éxito
                        window.location.href = '{{ route('mp_diametro.index') }}';
                    });
                },
                error: function(xhr) {
                    let errorMessages = [];
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessages = Object.values(xhr.responseJSON.errors).flat();
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessages.push(xhr.responseJSON.message);
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Errores de validación',
                        html: errorMessages.join('<br>'),
                        confirmButtonText: 'Corregir'
                    });
                }
            });
        });
    });
</script>
@stop

