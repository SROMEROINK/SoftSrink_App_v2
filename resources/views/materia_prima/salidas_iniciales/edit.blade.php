@extends('adminlte::page')

@section('title', 'Editar Salida Inicial de MP')

@section('content_header')
    <div class="show-header">
        <h1>Editar Salida Inicial de MP</h1>
        <p>Actualizacion del ajuste historico aplicado al ingreso de materia prima.</p>
    </div>
@stop

@section('content')
    <form action="{{ route('mp_salidas_iniciales.update', $salidaInicial->Id_Ingreso_MP) }}" method="POST">
        @csrf
        @method('PUT')
        @include('materia_prima.salidas_iniciales.partials.form')
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_salida_inicial_form.css') }}">
@stop

@section('js')
    @stack('js')
@stop
