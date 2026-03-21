@extends('adminlte::page')

@section('title', 'Editar Materia Base')
{{-- resources\views\materia_prima\materias_base\edit.blade.php --}}
@section('content_header')
    <h1>Editar Materia Base</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    <form action="{{ route('mp_materia_prima.update', $materiaBase->Id_Materia_Prima) }}" method="POST" data-edit-check="true" data-exclude-fields="_token,_method">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="Nombre_Materia">Nombre de la Materia:</label>
            <input type="text" class="form-control" id="Nombre_Materia" name="Nombre_Materia" value="{{ $materiaBase->Nombre_Materia }}" required>
        </div>
        <div class="form-group">
            <label for="reg_Status">Estado de la Materia:</label>
            <select name="reg_Status" id="reg_Status" class="form-control">
                <option value="1" {{ $materiaBase->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ $materiaBase->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('mp_materia_prima.index') }}" class="btn btn-default">Cancelar</a>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_base_edit.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop
