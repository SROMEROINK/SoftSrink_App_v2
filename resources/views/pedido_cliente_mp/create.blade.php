@extends('adminlte::page')

@section('title', 'Definir Materia Prima del Pedido')

@section('content_header')
    <div class="show-header">
        <h1>Definir Materia Prima del Pedido</h1>
        <p>Primera etapa tecnica para abastecimiento y calculo de barras por OF.</p>
    </div>
@stop

@section('content')
    @php($pedidoMp = null)
    @php($formTitle = 'Nueva definicion de materia prima')
    @php($formAction = route('pedido_cliente_mp.store'))
    @php($submitText = 'Guardar')
    @php($isEdit = false)

    @include('pedido_cliente_mp.partials.form')
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_mp_form.css') }}">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-ajax-submit.js') }}"></script>
    @include('pedido_cliente_mp.partials.form-script')
@stop


