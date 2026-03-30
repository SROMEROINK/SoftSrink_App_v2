@extends('adminlte::page')

@section('title', 'Registrar Salida Inicial de MP')

@section('content_header')
    <div class="show-header">
        <h1>Registrar Salida Inicial de MP</h1>
        <p>Ajuste historico para cuadrar el stock real del ingreso de materia prima.</p>
    </div>
@stop

@section('content')
    @include('components.swal-session')
    <form action="{{ route('mp_salidas_iniciales.store') }}" method="POST" class="ajax-form">
        @csrf
        @include('materia_prima.salidas_iniciales.partials.form')
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_salida_inicial_form.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
    @stack('js')
@stop


