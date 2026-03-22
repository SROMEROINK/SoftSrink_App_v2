@extends('adminlte::page')

@section('title', 'Registrar Movimiento Adicional MP')

@section('content_header')
    <h1>Registrar Movimiento Adicional de MP</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nuevo movimiento adicional</h3>
        </div>
        <form action="{{ route('mp_movimientos_adicionales.store') }}" method="POST" id="form-mov-adicional">
            @csrf
            <div class="card-body">
                @include('materia_prima.movimientos_adicionales.partials.form')
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('mp_movimientos_adicionales.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_movimiento_adicional_form.css') }}">
@stop

@section('js')
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop
