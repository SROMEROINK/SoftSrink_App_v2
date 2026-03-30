@extends('adminlte::page')

@section('title', 'Editar Egreso de Materia Prima')

@section('content_header')
    <h1>Editar Egreso de Materia Prima</h1>
@stop

@section('content')
    @include('components.swal-session')
    <form action="{{ route('mp_egresos.update', $egreso->Id_Egresos_MP) }}" method="POST" data-edit-check="true" data-exclude-fields="_token,_method,meta_nro_of,meta_producto,meta_ingreso,meta_maquina,meta_longitud,Codigo_MP,Total_Salidas_MP,Total_Mtros_Utilizados">
        @csrf
        @method('PUT')
        @include('materia_prima.egresos.partials.form')
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_egreso_create.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop

