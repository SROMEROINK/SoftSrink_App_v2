@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Sistema</h1>
@stop

@section('content')
    <p class="primary">Bienvenido <strong>{{ Auth::user()->name }}</strong> al sistema!!!</p>
@stop

@section('css')
<style>
    .sidebar-mini.sidebar-collapse .nav-sidebar .nav-link {
        width: auto;
    }

    .sidebar-mini.sidebar-collapse .nav-sidebar .nav-item {
        width: auto;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function () {
        // Asegúrate de que el estado del sidebar se recuerde
        $('[data-widget="pushmenu"]').PushMenu();

        // Opcional: eventos para expandir y contraer el menú lateral
        $('.main-sidebar').on('mouseenter', function () {
            $('body').removeClass('sidebar-collapse').addClass('sidebar-open');
        }).on('mouseleave', function () {
            $('body').removeClass('sidebar-open').addClass('sidebar-collapse');
        });
    });
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@stop
