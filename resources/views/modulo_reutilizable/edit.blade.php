@extends('adminlte::page')

@section('title', 'Editar Registro')

@section('content_header')
    <h1>Editar Registro</h1>
@stop

@section('content')
<form action="{{ route('modulo_reutilizable.update', $registro->Id_Modulo) }}"
      method="POST"
      data-edit-check="true"
      data-exclude-fields="_token,_method"
      data-redirect-url="{{ route('modulo_reutilizable.index') }}"
      data-success-message="Registro actualizado correctamente">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="Campo_1">Campo 1</label>
        <input type="text" class="form-control" id="Campo_1" name="Campo_1" value="{{ $registro->Campo_1 }}" required>
    </div>

    <div class="form-group">
        <label for="Campo_2">Campo 2</label>
        <input type="text" class="form-control" id="Campo_2" name="Campo_2" value="{{ $registro->Campo_2 }}" required>
    </div>

    <div class="form-group">
        <label for="reg_Status">Estado</label>
        <select name="reg_Status" id="reg_Status" class="form-control">
            <option value="1" {{ $registro->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ $registro->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="{{ route('modulo_reutilizable.index') }}" class="btn btn-default">Cancelar</a>
</form>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable_edit.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop