{{-- resources\views\materia_prima\diametro\deleted.blade.php --}}
@extends('adminlte::page')
@section('content')
<div class="container">
    <h1>Diámetros Eliminados</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Valor del Diámetro</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($diametros as $diametro)
            <tr>
                <td>{{ $diametro->Valor_Diametro }}</td>
                <td>{{ $diametro->reg_Status ? 'Activo' : 'Inactivo' }}</td>
                <td>
                    <form method="POST" action="{{ route('mp_diametro.restore', $diametro->Id_Diametro) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Restaurar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('mp_diametro.index') }}" class="btn btn-return">Volver a Diámetros</a>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_diametro_deleted.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault(); // Detener la acción por defecto

            const form = this; // Guardar referencia al formulario

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Quieres restaurar este diámetro?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, restaurarlo!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            Swal.fire(
                                'Restaurado!',
                                'El diámetro ha sido restaurado exitosamente.',
                                'success'
                            ).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('mp_diametro.index') }}";
                                }
                            });
                        },
                        error: function(response) {
                            Swal.fire(
                                'Error!',
                                'No se pudo restaurar el diámetro.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });
</script>
@stop

