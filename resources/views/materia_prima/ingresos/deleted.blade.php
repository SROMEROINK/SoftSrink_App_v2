{{-- resources\views\materia_prima\ingresos\deleted.blade.php --}}
@extends('adminlte::page')
@section('content')
<div class="container">
    <h1>Ingresos de Materia Prima Eliminados</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Nro Ingreso</th>
                <th>Fecha de Ingreso</th>
                <th>Proveedor</th>
                <th>Materia Prima</th>
                <th>Diámetro</th>
                <th>Cantidad Unidades</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ingresosEliminados as $ingreso)
            <tr>
                <td>{{ $ingreso->Nro_Ingreso_MP }}</td>
                <td>{{ $ingreso->Fecha_Ingreso }}</td>
                <td>{{ $ingreso->proveedor->Prov_Nombre ?? 'No Asignado' }}</td>
                <td>{{ $ingreso->materiaPrima->Nombre_Materia ?? 'No Asignado' }}</td>
                <td>{{ $ingreso->diametro->Valor_Diametro ?? 'No Asignado' }}</td>
                <td>{{ $ingreso->Unidades_MP }}</td>
                <td>
                    <form method="POST" action="{{ route('mp_ingresos.restore', $ingreso->Id_MP) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Restaurar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('mp_ingresos.index') }}" class="btn btn-return">Volver a Ingresos de Materia Prima</a>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_ingreso_deleted.css') }}">
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
                text: "¿Quieres restaurar este ingreso de materia prima?",
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
                                'El ingreso de materia prima ha sido restaurado exitosamente.',
                                'success'
                            ).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('mp_ingresos.index') }}";
                                }
                            });
                        },
                        error: function(response) {
                            Swal.fire(
                                'Error!',
                                'No se pudo restaurar el ingreso de materia prima.',
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
