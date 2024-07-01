@extends('adminlte::page')
{{-- resources\views\home.blade.php --}}
@section('title', 'Dashboard')

@section('content_header')
    <h1>Sistema</h1>
@stop

@section('content')
    <p class="primary">Bienvenido <strong>{{ Auth::user()->name }}</strong> al sistema!!!</p>
</br>
    <strong> Ir a ...</strong>
    <div class="navigation-container">
        @include('partials.navigation_2') <!-- Aquí incluyes la botonera personalizada -->
    </div>
@stop

@section('css')
    <style>
        .sidebar-mini.sidebar-collapse .nav-sidebar .nav-link {
            width: auto;
        }

        .sidebar-mini.sidebar-collapse .nav-sidebar .nav-item {
            width: auto;
        }

        /* Estilos para los botones de navegación */
        .btn-toolbar .btn-group {
            margin-right: 10px; /* Espacia los grupos de botones */
        }

        .btn-toolbar .btn {
            margin: 5px; /* Añade margen alrededor de cada botón */
            padding: 10px 20px; /* Añade espacio interno a los botones */
            border-radius: 5px; /* Bordes redondeados */
            transition: background-color 0.3s ease; /* Transición suave para el cambio de color de fondo */
        }

        /* Estilos adicionales para el contenedor de navegación */
        .navigation-container {
            margin-top: 20px;
            text-align: center; /* Centra el contenido horizontalmente */
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
