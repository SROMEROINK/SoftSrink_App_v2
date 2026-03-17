@extends('adminlte::page')

@section('title', 'Crear Registro')

@section('content_header')
    <h1>Crear Registro</h1>
@stop

@section('content')
<form method="POST"
      action="{{ route('modulo_reutilizable.store') }}"
      data-ajax="true"
      data-redirect-url="{{ route('modulo_reutilizable.index') }}">
    @csrf

    <div class="form-group">
        <label for="Campo_1">Campo 1</label>
        <input type="text" name="Campo_1" id="Campo_1" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="Campo_2">Campo 2</label>
        <input type="text" name="Campo_2" id="Campo_2" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('modulo_reutilizable.index') }}" class="btn btn-default">Cancelar</a>
</form>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable_create.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop