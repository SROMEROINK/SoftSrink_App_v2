@extends('adminlte::page')

@section('title', 'Editar Permiso')

@section('content_header')
    <h1>Editar Permiso</h1>
@stop

@section('content')
    <div class="container">
        @if(session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3000
                }).then(function() {
                    window.location.href = "{{ route('permissions.index') }}";
                });
            </script>
        @endif

        <form id="updateForm" action="{{ route('permissions.update', $permission->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre del Permiso</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $permission->name) }}" required>
                @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Permiso</button>
        </form>
    </div>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#updateForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route('permissions.update', $permission->id) }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Permiso actualizado correctamente',
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
                        text: 'No se pudo actualizar el permiso.',
                        position: 'center'
                    });
                }
            });
        });
    });
</script>
@endsection
