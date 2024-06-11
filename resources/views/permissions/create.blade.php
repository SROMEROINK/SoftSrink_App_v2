@extends('adminlte::page')

@section('title', 'Crear Permiso')

@section('content_header')
    <h1>Crear Permiso</h1>
@stop

@section('content')
    <div class="container">
        <form id="createForm" action="{{ route('permissions.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nombre del Permiso</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Crear Permiso</button>
        </form>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/PermissionCreate.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script>
    $(document).ready(function() {
        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Permiso creado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.timer) {
                            window.location.href = '{{ route('permissions.index') }}';
                        }
                    });
                },
                error: function(response) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo crear el permiso.',
                        position: 'center'
                    });
                }
            });
        });
    });
</script>
@endsection
