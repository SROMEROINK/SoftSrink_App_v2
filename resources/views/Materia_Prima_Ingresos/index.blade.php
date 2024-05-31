{{-- resources/views/Materia_Prima/index.blade.php --}}

@extends('adminlte::page')

@section('title', 'Materia Prima - Ingresos')

@section('content_header')
    <h1>Materia Prima - Ingresos</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Tu contenido va aquí -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nro_Ingreso_MP</th>
                            <th>Nro_Pedido</th>
                            <th>Nro_Remito</th>
                            <th>Fecha_Ingreso</th>
                            <th>Nro_OC</th>
                            <th>Proveedor</th>
                            <th>Materia_Prima</th>
                            <th>Diametro_MP</th>
                            <th>Codigo_MP</th>
                            <th>N°_Certificado_MP</th>
                            <th>Detalle_Origen_MP</th>
                            <th>Unidades_MP</th>
                            <th>Longitud_Unidad_MP</th>
                            <th>Mts_Totales</th>
                            <th>Kilos_Totales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ingresos_mp  as $ingreso_mp )
                            <tr>
                                <td>{{ $ingreso_mp ->Nro_Ingreso_MP }}</td>
                                <td>{{ $ingreso_mp ->Nro_Pedido}}</td>
                                <td>{{ $ingreso_mp ->Nro_Remito }}</td>
                                <td>{{ $ingreso_mp ->Fecha_Ingreso }}</td>
                                <td>{{ $ingreso_mp ->Nro_OC }}</td>
                                <td>{{ optional($ingreso_mp ->Proveedor)->Prov_Nombre }}</td>
                                <td>{{ $ingreso_mp ->Materia_Prima }}</td>
                                <td>{{ $ingreso_mp ->Diametro_MP }}</td>
                                <td>{{ $ingreso_mp ->Codigo_MP }}</td>
                                <td>{{ $ingreso_mp ->N°_Certificado_MP }}</td>
                                <td>{{ $ingreso_mp ->Detalle_Origen_MP }}</td>
                                <td>{{ $ingreso_mp ->Unidades_MP }}</td>
                                <td>{{ $ingreso_mp ->Longitud_Unidad_MP }}</td>
                                <td>{{ $ingreso_mp ->Mts_Totales }}</td>
                                <td>{{ $ingreso_mp ->Kilos_Totales }}</td>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $ingresos_mp ->links() }} {{-- Paginación --}}
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Aquí puedes incluir archivos CSS adicionales --}}
@stop

@section('js')
    {{-- Aquí puedes incluir archivos JavaScript adicionales --}}
@stop

