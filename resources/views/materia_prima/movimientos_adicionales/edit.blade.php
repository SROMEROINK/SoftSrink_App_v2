@extends('adminlte::page')

@section('title', 'Editar Movimiento Adicional MP')

@section('content_header')
    <h1>Editar Movimiento Adicional de MP</h1>
@stop

@section('content')
    @include('components.swal-session')
<div class="container-fluid">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Movimiento #{{ $movimiento->Id_Movimiento_MP }}</h3>
        </div>
        <form action="{{ route('mp_movimientos_adicionales.update', $movimiento->Id_Movimiento_MP) }}" method="POST" id="form-mov-adicional-edit" data-edit-check="true" data-exclude-fields="_token,_method" data-redirect-url="{{ route('mp_movimientos_adicionales.index') }}" data-no-changes-message="No se detectaron cambios en el formulario.">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('materia_prima.movimientos_adicionales.partials.form')
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('mp_movimientos_adicionales.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_movimiento_adicional_form.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop

