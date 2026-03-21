{{-- resources\views\materia_prima\materias_base\deleted.blade.php --}}
@extends('adminlte::page')
@section('content')
<div class="container">
    <h1>Materias Primas Eliminadas</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Nombre de la Materia</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($materiasPrimas as $materiaPrima)
            <tr>
                <td>{{ $materiaPrima->Nombre_Materia }}</td>
                <td>{{ $materiaPrima->reg_Status ? 'Activo' : 'Inactivo' }}</td>
                <td>
                    <form method="POST" action="{{ route('mp_materias_primas.restore', $materiaPrima->Id_Materia_Prima) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Restaurar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('mp_materia_prima.index') }}" class="btn btn-return">Volver a Materias Primas</a>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_base_deleted.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault();

            const form = this;

            SwalUtils.confirmRestore('La materia prima volvera al listado principal.').then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            SwalUtils.restored(response.message || 'La materia prima ha sido restaurada exitosamente.').then(() => {
                                window.location.href = "{{ route('mp_materia_prima.index') }}";
                            });
                        },
                        error: function() {
                            SwalUtils.error('No se pudo restaurar la materia prima.');
                        }
                    });
                }
            });
        });
    });
</script>
@stop
