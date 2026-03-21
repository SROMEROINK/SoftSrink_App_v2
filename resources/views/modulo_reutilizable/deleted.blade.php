@extends('adminlte::page')

@section('title', 'Registros Eliminados')

@section('content')
<div class="container">
    <h1>Registros Eliminados</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Campo 1</th>
                <th>Campo 2</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registrosEliminados as $registro)
            <tr>
                <td>{{ $registro->Id_Modulo }}</td>
                <td>{{ $registro->Campo_1 }}</td>
                <td>{{ $registro->Campo_2 }}</td>
                <td>
                    <form method="POST" action="{{ route('modulo_reutilizable.restore', $registro->Id_Modulo) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Restaurar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('modulo_reutilizable.index') }}" class="btn btn-return">Volver</a>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable_deleted.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
$(document).ready(function() {
    $('form').on('submit', function(e) {
        e.preventDefault();

        const form = this;

        SwalUtils.confirmRestore('El registro volvera al listado principal.').then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    success: function() {
                        SwalUtils.restored('El registro fue restaurado correctamente.')
                            .then(() => window.location.href = "{{ route('modulo_reutilizable.index') }}");
                    },
                    error: function() {
                        SwalUtils.error('No se pudo restaurar el registro.');
                    }
                });
            }
        });
    });
});
</script>
@stop
