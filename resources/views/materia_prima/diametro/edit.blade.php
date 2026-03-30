@extends('adminlte::page')

@section('title', 'Editar DiÃ¡metro')
{{-- resources\views\materia_prima\diametro\edit.blade.php --}}
@section('content_header')
    <h1>Editar DiÃ¡metro</h1>
@stop

@section('content')
    @include('components.swal-session')

    <form action="{{ route('mp_diametro.update', $diametro->Id_Diametro) }}" method="POST" data-edit-check="true" data-exclude-fields="_token,_method">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="Valor_Diametro">Valor del DiÃ¡metro:</label>
            <input type="text" class="form-control" id="Valor_Diametro" name="Valor_Diametro" value="{{ $diametro->Valor_Diametro }}" required>
        </div>
        <div class="form-group">
            <label for="reg_Status">Estado del DiÃ¡metro:</label>
            <select name="reg_Status" id="reg_Status" class="form-control">
                <option value="1" {{ $diametro->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ $diametro->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('mp_diametro.index') }}" class="btn btn-default">Cancelar</a>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_diametro_edit.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop

