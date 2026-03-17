@extends('adminlte::page')

@section('title', 'Ver Registro')

@section('content_header')
    <h1>Detalle del Registro</h1>
@stop

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Detalle del registro</h3>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Campo 1:</strong> {{ $registro->Campo_1 }}</li>
                <li class="list-group-item"><strong>Campo 2:</strong> {{ $registro->Campo_2 }}</li>
                <li class="list-group-item"><strong>Estado:</strong> {{ $registro->reg_Status == 1 ? 'Activo' : 'Inactivo' }}</li>
                <li class="list-group-item"><strong>Creado en:</strong> {{ $registro->created_at }}</li>
                <li class="list-group-item"><strong>Actualizado en:</strong> {{ $registro->updated_at }}</li>
            </ul>
        </div>
        <div class="card-footer">
            <a href="{{ route('modulo_reutilizable.index') }}" class="btn btn-default">Volver</a>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable_show.css') }}">
@stop