@extends('adminlte::page')

@section('title', 'Grupos de Conjuntos Eliminados')

@section('content_header')
<x-header-card
    title="Grupos de Conjuntos Eliminados"
    buttonRoute="{{ route('producto_grupo_conjuntos.index') }}"
    buttonText="Volver al listado"
/>
@stop

@section('content')
    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla_grupo_conjuntos_eliminados" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Grupo</th>
                            <th>Estado</th>
                            <th>Eliminado el</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gruposEliminados as $grupo)
                            <tr>
                                <td>{{ $grupo->Id_GrupoConjuntos }}</td>
                                <td>{{ $grupo->Nombre_GrupoConjuntos }}</td>
                                <td>{{ (int) $grupo->reg_Status === 1 ? 'Activo' : 'Inactivo' }}</td>
                                <td>{{ $grupo->deleted_at }}</td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm btn-restaurar" data-id="{{ $grupo->Id_GrupoConjuntos }}">
                                        Restaurar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay registros eliminados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_index.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/grupos_conjuntos/deleted.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
    $(document).ready(function () {
        $('#tabla_grupo_conjuntos_eliminados').DataTable({
            pageLength: 10,
            order: [[0, 'asc']],
            language: {
                lengthMenu: "Mostrar _MENU_ registros",
                zeroRecords: "No se encontraron registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "No hay registros disponibles",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                paginate: {
                    first: "Primero",
                    last: "Ultimo",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        });

        $(document).on('click', '.btn-restaurar', function () {
            const id = $(this).data('id');

            SwalUtils.confirmRestore('El grupo volvera al listado principal.').then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('producto_grupo_conjuntos/restore') }}/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            SwalUtils.restored(response.message).then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            SwalUtils.error(xhr.responseJSON?.message || 'No se pudo restaurar el registro.');
                        }
                    });
                }
            });
        });
    });
</script>
@stop
