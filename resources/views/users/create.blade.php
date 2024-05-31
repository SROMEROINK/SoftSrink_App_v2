@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
    <h1>Crear Usuario</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                @error('email')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" required>
                @error('password')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirmar Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" class="form-control" required>
                    @foreach (Spatie\Permission\Models\Role::all() as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="photo">Foto</label>
                <input type="file" name="photo" class="form-control">
                @error('photo')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
        </form>
    </div>
@stop
