@extends('adminlte::page')

@section('title', 'Editar Diámetro')
{{-- resources\views\materia_prima\diametro\edit.blade.php --}}
@section('content_header')
    <h1>Editar Diámetro</h1>
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

    <form action="{{ route('mp_diametro.update', $diametro->Id_Diametro) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="Valor_Diametro">Valor del Diámetro:</label>
            <input type="text" class="form-control" id="Valor_Diametro" name="Valor_Diametro" value="{{ $diametro->Valor_Diametro }}" required>
        </div>
        <div class="form-group">
            <label for="reg_Status">Estado del Diámetro:</label>
            <select name="reg_Status" id="reg_Status" class="form-control">
                <option value="1" {{ $diametro->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ $diametro->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('mp_diametro.index') }}" class="btn btn-default">Cancelar</a>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_diametro_edit.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Almacenar los valores originales
        const originalValues = {
            Valor_Diametro: $('#Valor_Diametro').val(),
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
                        title: 'Diámetro actualizado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function() {
                        window.location.href = "{{ route('mp_diametro.index') }}";
                    }, 1500);
                },
                error: function(response) {
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Ocurrió un error al actualizar el diámetro',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });
    });
</script>
@stop
