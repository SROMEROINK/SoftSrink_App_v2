{{-- resources\views\proveedores\deleted.blade.php --}}
@extends('adminlte::page')
@section('content')
<div class="container">
    <h1>Proveedores Eliminados</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Detalle</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($proveedores as $proveedor)
            <tr>
                <td>{{ $proveedor->Prov_Nombre }}</td>
                <td>{{ $proveedor->Prov_Detalle }}</td>
                <td>
                    <form method="POST" action="{{ route('proveedores.restore', $proveedor->Prov_Id) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Restaurar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('proveedores.index') }}" class="btn btn-return">Volver a Proveedores</a>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/proveedores_deleted.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault();

            const form = this;

            SwalUtils.confirmRestore('El proveedor volvera al listado principal.').then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            SwalUtils.restored(response.message || 'El proveedor ha sido restaurado exitosamente.').then(() => {
                                window.location.href = "{{ route('proveedores.index') }}";
                            });
                        },
                        error: function() {
                            SwalUtils.error('No se pudo restaurar el proveedor.');
                        }
                    });
                }
            });
        });
    });
</script>

@stop
