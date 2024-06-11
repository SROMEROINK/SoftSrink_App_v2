@extends('adminlte::page')

@section('title', 'Lista de Permisos')

@section('content_header')
    <h1>Lista de Permisos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: "{{ session('success') }}",
                        showConfirmButton: false,
                        timer: 3000
                    });
                </script>
            @endif

            @if(session('info'))
                <script>
                    Swal.fire({
                        icon: 'info',
                        title: 'Información',
                        text: "{{ session('info') }}",
                        showConfirmButton: false,
                        timer: 3000
                    });
                </script>
            @endif

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre del Permiso</th>
                        <th class="text-center acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td class="text-right">
                                <a class="btn btn-primary" href="{{ route('permissions.edit', $permission->id) }}">Editar</a>
                                <button class="btn btn-danger trigger-delete" data-id="{{ $permission->id }}">Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="card-footer text-left">
                <a href="{{ route('permissions.create') }}" class="btn btn-primary">Crear Permiso</a>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/PermissionIndex.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        $('.trigger-delete').on('click', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "¿Estás seguro?",
                text: "¡No podrás revertir esto!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar!",
                cancelButtonText: "No, cancelar!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/permissions/' + id,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Eliminado!",
                                text: "El permiso ha sido eliminado con éxito.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire(
                                'Error',
                                'No se pudo eliminar el permiso.',
                                'error'
                            );
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire(
                        'Cancelado',
                        'El permiso está a salvo :)',
                        'info'
                    );
                }
            });
        });
    });
</script>
@stop
