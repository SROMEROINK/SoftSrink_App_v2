{{-- resources\views\marcas_insumos\deleted.blade.php --}}
@extends('adminlte::page')

@section('title', 'Marcas de Insumos Eliminadas')

@section('content')
<div class="container">
    <h1>Marcas de Insumos Eliminadas</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nro Marca</th>
                <th>Nombre de Marca</th>
                <th>Proveedor</th>
                <th>Estado</th>
                <th>Fecha de Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($marcasEliminadas as $marca)
            <tr>
                <td>{{ $marca->Id_Marca }}</td>
                <td>{{ $marca->Nombre_marca }}</td>
                <td>{{ $marca->proveedor->Prov_Nombre ?? 'No Asignado' }}</td>
                <td>{{ $marca->reg_Status == 1 ? 'Habilitado' : 'Deshabilitado' }}</td>
                <td>{{ $marca->created_at ? $marca->created_at->format('d/m/Y H:i:s') : 'No Disponible' }}</td>
                <td>
                    <form method="POST" action="{{ route('marcas_insumos.restore', $marca->Id_Marca) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Restaurar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('marcas_insumos.index') }}" class="btn btn-return">Volver a Marcas de Insumos</a>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/marcas_insumos_deleted.css') }}">
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
                text: "¿Quieres restaurar esta marca de insumo?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, restaurarla!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            Swal.fire(
                                'Restaurado!',
                                'La marca de insumo ha sido restaurada exitosamente.',
                                'success'
                            ).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('marcas_insumos.index') }}";
                                }
                            });
                        },
                        error: function(response) {
                            Swal.fire(
                                'Error!',
                                'No se pudo restaurar la marca de insumo.',
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
