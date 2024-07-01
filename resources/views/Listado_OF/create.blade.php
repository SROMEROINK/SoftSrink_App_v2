@extends('adminlte::page')

@section('title', 'Programación de la Producción - Listado_OF')

@section('content_header')

<x-header-card 
    title="Listado de Ordenes de Fabricación" 
    quantityTitle="Cantidad de Ordenes de Fabricación:" 
    buttonRoute="{{ route('listado_OF.create') }}" 
    buttonText="Crear Orden de Fabricación"
/>

@stop

<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="listado_de_OF" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Id_OF</th>
                                <th class="column-nro-of">Nro_OF</th>
                                <th>Prod_Codigo</th>
                                <th>Prod_Descripcion</th>
                                <th>Nombre_Categoria</th>
                                <th>Nro_Maquina</th>
                                <th>Familia_Maquinas</th>
                                <th>Fecha_Fabricacion</th>
                                <th>Nro_Parcial</th>
                                <th>Cant_Piezas</th>
                                <th>Horario</th>
                                <th>Nombre_Operario</th>
                                <th>Turno</th>
                                <th>Cant_Horas_Extras</th>
                                <th>created_at</th>
                                <th>creator</th>
                                <th>updated_at</th>
                                <th>updater</th>
                                <th>Acciones</th>
                            </tr>
                            <tr class="filter-row
</div>

@section('content')
@stop