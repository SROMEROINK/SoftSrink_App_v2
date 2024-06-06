@extends('adminlte::page')

@section('title', 'Roles y Permisos')

@section('content_header')
    <h1>Roles y Permisos</h1>
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
                    }).then(function() {
                        window.location.href = "{{ route('roles.index') }}";
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
                    }).then(function() {
                        window.location.href = "{{ route('roles.index') }}";
                    });
                </script>
            @endif

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre del Rol</th>
                        <th>Permisos</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $role->users->pluck('name')->implode(', ') }}</td>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->permissions->pluck('name')->implode(', ') }}</td>
                            <td class="text-center">
                                <div class="button-group">
                                    <a class="btn btn-primary" href="{{ route('roles.edit', $role->id) }}">Editar</a>
                                    <button class="btn btn-danger trigger-delete" data-id="{{ $role->id }}">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="card-footer">
                <a href="{{ route('roles.create') }}" class="btn btn-primary">Crear Rol</a>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/RoleIndex.css') }}">
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
                        url: '/roles/' + id,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Eliminado!",
                                text: "El rol ha sido eliminado con éxito.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire(
                                'Error',
                                'No se pudo eliminar el rol.',
                                'error'
                            );
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire(
                        'Cancelado',
                        'El rol está a salvo :)',
                        'info'
                    );
                }
            });
        });
    });
</script>
@stop
