@extends('adminlte::page')

@section('title', 'Egreso de Materia Prima')

@section('content_header')
    <h1>Registrar Egreso de Materia Prima</h1>
@stop

@section('content')
    @include('components.swal-session')
    <form action="{{ route('mp_egresos.store') }}" method="POST">
        @csrf
        @include('materia_prima.egresos.partials.form')
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_egreso_create.css') }}">
@stop

