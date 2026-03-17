@extends('adminlte::page')

@section('title', 'Editar Rol')

@section('content_header')
    <h1>Editar Rol</h1>
@stop

@section('content')
    <div class="container">
        <form id="updateForm" action="{{ route('roles.update', $role->id) }}" method="POST" data-edit-check="true" data-exclude-fields="_token,_method">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre del Rol</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="permissions">Permisos</label>
                @foreach ($permissions as $permission)
                    <div class="form-check">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input"
                            {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>
            @error('permissions')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary">Actualizar Rol</button>
        </form>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/PermissionEdit.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
<script>
    $(document).ready(function() {
        @if ($errors->has('permissions'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Debe seleccionar aunque sea un permiso.'
            });
        @endif

        @if (session('success'))
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 1500
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.timer) {
                    window.location.href = '{{ route('roles.index') }}';
                }
            });
        @endif
    });
</script>
@stop
