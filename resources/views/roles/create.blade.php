@extends('adminlte::page')

@section('title', 'Crear Rol')
{{-- resources\views\roles\create.blade.php --}}
@section('content_header')
    <h1>Crear Rol</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nombre del Rol</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="permissions">Permisos</label>
                @foreach ($permissions as $permission)
                    <div class="form-check">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input">
                        <label class="form-check-label">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary">Crear Rol</button>
        </form>
    </div>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
