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
<script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault(); // Detener la acción por defecto

            const form = this; // Guardar referencia al formulario

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Quieres restaurar este proveedor?",
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
                                'El proveedor ha sido restaurado exitosamente.',
                                'success'
                            ).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('proveedores.index') }}";
                                }
                            });
                        },
                        error: function(response) {
                            Swal.fire(
                                'Error!',
                                'No se pudo restaurar el proveedor.',
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
