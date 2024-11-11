{{-- resources/views/materia_prima/ingresos/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Detalles del Ingreso de Materia Prima')

@section('content_header')
    <h1>Detalles del Ingreso de Materia Prima</h1>
@stop

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Ingreso Nro: {{ $mp_ingreso->Nro_Ingreso_MP }}</h3>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                
                <li class="list-group-item"><strong>Número de Pedido:</strong> {{ $mp_ingreso->Nro_Pedido }}</li>
                <li class="list-group-item"><strong>Número de Remito:</strong> {{ $mp_ingreso->Nro_Remito }}</li>
                <li class="list-group-item"><strong>Fecha de Ingreso:</strong> {{ $mp_ingreso->Fecha_Ingreso }}</li>
                <li class="list-group-item"><strong>Número de Orden de Compra:</strong> {{ $mp_ingreso->Nro_OC }}</li>
                <li class="list-group-item"><strong>Proveedor:</strong> {{ $mp_ingreso->proveedor->Prov_Nombre ?? 'No Asignado' }}</li>
                <li class="list-group-item"><strong>Materia Prima:</strong> {{ $mp_ingreso->materiaPrima->Nombre_Materia ?? 'No Asignado' }}</li>
                <li class="list-group-item"><strong>Diámetro:</strong> {{ $mp_ingreso->diametro->Valor_Diametro ?? 'No Asignado' }}</li>
                <li class="list-group-item"><strong>Código de Materia Prima:</strong> {{ $mp_ingreso->Codigo_MP }}</li>
                <li class="list-group-item"><strong>Número de Certificado:</strong> {{ $mp_ingreso->Nro_Certificado_MP }}</li>
                <li class="list-group-item"><strong>Detalle de Origen:</strong> {{ $mp_ingreso->Detalle_Origen_MP }}</li>
                <li class="list-group-item"><strong>Unidades:</strong> {{ $mp_ingreso->Unidades_MP }}</li>
                <li class="list-group-item"><strong>Longitud por Unidad:</strong> {{ $mp_ingreso->Longitud_Unidad_MP }}</li>
                <li class="list-group-item"><strong>Metros Totales:</strong> {{ $mp_ingreso->Mts_Totales }}</li>
                <li class="list-group-item"><strong>Kilos Totales:</strong> {{ $mp_ingreso->Kilos_Totales }}</li>
                <li class="list-group-item"><strong>Estado:</strong> {{ $mp_ingreso->reg_Status == 1 ? 'Activo' : 'Inactivo' }}</li>
                <li class="list-group-item"><strong>Creado en:</strong> {{ $mp_ingreso->created_at }}</li>
                <li class="list-group-item"><strong>Actualizado en:</strong> {{ $mp_ingreso->updated_at }}</li>
            </ul>
        </div>
        <div class="card-footer">
            <a href="{{ route('mp_ingresos.index') }}" class="btn btn-default">Volver a la lista</a>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_ingreso_show.css') }}">
@stop
