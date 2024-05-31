@extends('adminlte::page')

@section('title', 'Perfil de Usuario')

@section('content_header')
    <h1>Perfil de Usuario</h1>
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información del Usuario</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        @if ($user->photo)
                            <p><strong>Foto:</strong></p>
                            <img src="{{ asset('storage/' . $user->photo) }}" alt="Foto de {{ $user->name }}" width="150">
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="btn-group" role="group" aria-label="Acciones">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">Volver</a>
                            @can('ver solo administrador')
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary ml-2">Editar Perfil</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
