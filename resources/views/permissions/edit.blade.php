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

        <form id="updateForm" action="{{ route('permissions.update', $permission->id) }}" method="POST" data-edit-check="true" data-exclude-fields="_token,_method">
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
<script src="{{ asset('js/form-edit-check.js') }}"></script>
@endsection
