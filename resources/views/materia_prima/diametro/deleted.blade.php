{{-- resources\views\materia_prima\diametro\deleted.blade.php --}}
@extends('adminlte::page')
@section('content')
<div class="container">
    <h1>Diametros Eliminados</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Valor del Diametro</th>
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
    <a href="{{ route('mp_diametro.index') }}" class="btn btn-return">Volver a Diametros</a>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_diametro_deleted.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault();

            const form = this;

            SwalUtils.confirmRestore('El diametro volvera al listado principal.').then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            SwalUtils.restored(response.message || 'El diametro ha sido restaurado exitosamente.').then(() => {
                                window.location.href = "{{ route('mp_diametro.index') }}";
                            });
                        },
                        error: function() {
                            SwalUtils.error('No se pudo restaurar el diametro.');
                        }
                    });
                }
            });
        });
    });
</script>
@stop
