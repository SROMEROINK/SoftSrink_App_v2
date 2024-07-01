{{-- resources/views/Materia_Prima/index.blade.php --}}

@extends('adminlte::page')

@section('title', 'Programación de la Producción - Listado_OF')

@section('content_header')
    <h1>Programación de la Producción - Listado_OF</h1>
@section('plugins,Datatables',true)    
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-16">
                <!-- Tu contenido va aquí -->
                <table id="listado_of" class="stripe" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nro_OF</th>
                            <th>Estado_Planificacion</th>
                            <th>Estado</th>
                            <th>Producto_Id</th>
                            <th>Revision_Plano_1</th>
                            <th>Revision_Plano_2</th>
                            <th>Fecha_del_Pedido</th>
                            <th>Cant_Fabricacion</th>
                            <th>Nro_Maquina</th>
                            <th>Familia_Maquinas</th>
                            <th>MP_Id</th>
                            <th>Pedido_de_MP</th>
                            <th>Tiempo_Pieza_Real</th>
                            <th>Tiempo_Pieza_Aprox</th>
                            <th>Cant_Unidades_MP</th>
                            <th>Cant_Piezas_Por_Unidad_MP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($listados_of as $listado_of )
                            <tr>
                                <td>{{$listado_of ->Nro_OF }}</td>
                                <td>{{$listado_of ->Estado_Planificacion}}</td>
                                <td>{{$listado_of ->Estado }}</td>
                                <td>{{ optional($listado_of ->Producto)->Prod_Codigo }}</td>
                                <td>{{$listado_of ->Revision_Plano_1 }}</td>
                                <td>{{$listado_of ->Revision_Plano_2 }}</td>
                                <td>{{$listado_of ->Fecha_del_Pedido }}</td>
                                <td>{{$listado_of ->Cant_Fabricacion}}</td>
                                <td>{{$listado_of ->Nro_Maquina }}</td>
                                <td>{{$listado_of ->Familia_Maquinas }}</td>
                                <td>{{$listado_of ->Ingreso_mp->Nro_Ingreso_MP}}</td>
                                <td>{{$listado_of ->Pedido_de_MP }}</td>
                                <td>{{$listado_of ->Tiempo_Pieza_Real }}</td>
                                <td>{{$listado_of ->Tiempo_Pieza_Aprox}}</td>
                                <td>{{$listado_of ->Cant_Unidades_MP }}</td>
                                <td>{{$listado_of ->Cant_Piezas_Por_Unidad_MP}}</td>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- Agrega los estilos de DataTables aquí -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css">
@stop

@section('js')
    <!-- Agrega los scripts de DataTables aquí -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#listado_of').DataTable({
                "order": []
            });
        });
    </script>

{{-- Aquí puedes incluir archivos JavaScript adicionales --}}
@stop

