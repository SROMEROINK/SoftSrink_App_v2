@extends('adminlte::page')

@section('title', 'Lista de Usuarios')

@section('content_header')
    <h1>Lista de Usuarios</h1>
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
                        window.location.href = "{{ route('users.index') }}";
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
                        window.location.href = "{{ route('users.index') }}";
                    });
                </script>
            @endif

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Roles</th>
                        <th>Foto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->password }}</td>
                            <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
                            <td><img src="{{ asset('storage/photos/' . $user->photo) }}" alt="{{ $user->name }}" width="50"></td>
                            <td>
                                <a class="btn btn-primary" href="{{ route('users.edit', $user->id) }}">Editar</a>
                                <button class="btn btn-danger trigger-delete" data-id="{{ $user->id }}">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="card-footer">
                <a href="{{ route('users.create') }}" class="btn btn-primary">Crear Usuario</a>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/UserIndex.css') }}">
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
                        url: '/users/' + id,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Eliminado!",
                                text: "El usuario ha sido eliminado con éxito.",
                                icon: "success"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire(
                                'Error',
                                'No se pudo eliminar el usuario.',
                                'error'
                            );
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire(
                        'Cancelado',
                        'El usuario está a salvo :)',
                        'info'
                    );
                }
            });
        });
    });
</script>
@stop
