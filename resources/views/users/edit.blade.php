@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
    <h1>Editar Usuario</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="role">Rol</label>
                <select name="role" class="form-control" required>
                    <option value="">Seleccione un rol</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role', $user->roles->pluck('id')->contains($role->id) ? 'selected' : '') }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="permissions">Permisos</label>
                @foreach ($user->getPermissionsViaRoles() as $permission)
                    <div class="form-check">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input"
                            checked disabled>
                        <label class="form-check-label">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>
            <div class="form-group">
                <label for="password">Password <small>(dejar en blanco para mantener la actual)</small></label>
                <input type="password" name="password" class="form-control">
                @error('password')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirmar Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="form-group">
                <label for="photo">Foto</label>
                <input type="file" name="photo" class="form-control">
                @if ($user->photo)
                    <img src="{{ asset('storage/' . $user->photo) }}" alt="Foto de {{ $user->name }}" width="100">
                    <div class="form-check">
                        <input type="checkbox" name="remove_photo" class="form-check-input" id="remove_photo">
                        <label class="form-check-label" for="remove_photo">Eliminar foto</label>
                    </div>
                @endif
                @error('photo')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
        </form>
    </div>
@stop
