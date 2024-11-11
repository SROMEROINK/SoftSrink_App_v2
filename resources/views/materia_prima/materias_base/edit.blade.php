@extends('adminlte::page')

@section('title', 'Editar Materia Base')
{{-- resources\views\materia_prima\materias_base\edit.blade.php --}}
@section('content_header')
    <h1>Editar Materia Base</h1>
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

    <form action="{{ route('mp_materia_prima.update', $materiaBase->Id_Materia_Prima) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="Nombre_Materia">Nombre de la Materia:</label>
            <input type="text" class="form-control" id="Nombre_Materia" name="Nombre_Materia" value="{{ $materiaBase->Nombre_Materia }}" required>
        </div>
        <div class="form-group">
            <label for="reg_Status">Estado de la Materia:</label>
            <select name="reg_Status" id="reg_Status" class="form-control">
                <option value="1" {{ $materiaBase->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ $materiaBase->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('mp_materia_prima.index') }}" class="btn btn-default">Cancelar</a>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_base_edit.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const originalValues = {
            Nombre_Materia: $('#Nombre_Materia').val(),
            reg_Status: $('#reg_Status').val()
        };

        $('form').on('submit', function(e) {
            e.preventDefault();

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

            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Materia Base actualizada correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function() {
                        window.location.href = "{{ route('mp_materia_prima.index') }}";
                    }, 1500);
                },
                error: function(response) {
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Ocurrió un error al actualizar la Materia Base',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });
    });
</script>
@stop
