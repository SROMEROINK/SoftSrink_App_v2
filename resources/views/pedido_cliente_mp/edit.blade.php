@extends('adminlte::page')

@section('title', 'Editar Definicion de Materia Prima')

@section('content_header')
    <div class="show-header">
        <h1>Editar Definicion de Materia Prima</h1>
        <p>Actualiza la etapa de abastecimiento de materia prima de la OF seleccionada.</p>
    </div>
@stop

@section('content')
    @include('components.swal-session')
    @php($formTitle = 'Editar definicion de materia prima')
    @php($formAction = route('pedido_cliente_mp.update', $pedidoMp->Id_Pedido_MP))
    @php($submitText = 'Actualizar')
    @php($isEdit = true)
    @php($plannerEditMode = true)

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
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
    @include('pedido_cliente_mp.partials.form-script')
@stop



