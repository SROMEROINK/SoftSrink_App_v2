@extends('adminlte::page')

@section('title', 'Estados Eliminados')

@section('content_header')
    <h1>Estados de Planificacion Eliminados</h1>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Listado de eliminados</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Estado</th>
                            <th>Status</th>
                            <th>Eliminado el</th>
                            <th>Eliminado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($estadosEliminados as $estado)
                            <tr>
                                <td>{{ $estado->Estado_Plani_Id }}</td>
                                <td>{{ $estado->Nombre_Estado }}</td>
                                <td>
                                    @if($estado->Status == 1)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>{{ $estado->deleted_at }}</td>
                                <td>{{ $estado->deleted_by ?? '-' }}</td>
                                <td>
                                    <button type="button"
                                            class="btn btn-success btn-sm btn-restaurar"
                                            data-id="{{ $estado->Estado_Plani_Id }}">
                                        Restaurar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay registros eliminados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer text-right">
            <a href="{{ route('estado_planificacion.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/estado_planificacion_deleted.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script>
        $(document).on('click', '.btn-restaurar', function () {
            const id = $(this).data('id');

            SwalUtils.confirmRestore('El estado volvera a estar disponible.').then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('estado_planificacion/restore') }}/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            SwalUtils.restored(response.message).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function (xhr) {
                            SwalUtils.error(xhr.responseJSON?.message || 'No se pudo restaurar el registro.');
                        }
                    });
                }
            });
        });
    </script>
@stop
