{{-- resources/views/Materia_Prima/index.blade.php --}}

@extends('adminlte::page')

@section('title', 'Materia Prima - Salidas')

@section('content_header')
    <h2>Materia Prima - Salidas</h2>  
    <h1>
        Cantidad de Barras_OF: 
        <span id="totalCantBarras" class="total-numero">0</span>
    </h1>

    <style>
        /* Estilos personalizados para el título */
        .titulo-cantidad {
            font-weight: bold;
            color: blue; /* Cambia el color del texto a azul */
        }

        /* Estilos personalizados para el número */
        .total-numero {
            background-color: green; /* Cambia el color de fondo a verde */
            color: white; /* Cambia el color del texto a blanco */
            padding: 3px 5px; /* Añade un poco de relleno */
            border-radius: 3px; /* Agrega bordes redondeados */
        }
    </style>

@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-16">

                <!-- Tu contenido va aquí -->
                <table id="salidas_mp" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Id_Ingreso_MP</th>
                            <th>Nro_OF</th>
                            <th>Código_de_Producto</th>
                            <th>Descripción</th>
                            <th>Clase_Familia</th>
                            <th>Nro_de_Máquina</th>
                            <th>Familia_de_Máquinas</th>
                            <th>Nro_Ingreso_MP</th>
                            <th>Nombre_Proveedor</th>
                            <th>Código_MP</th>
                            <th>Cant_Unid_MP_OF</th>
                            <th>Cant_Unid_Preparadas</th>
                            <th>Cant_Unid_Adicionales</th>
                            <th>Cant_Unid_Devoluciones</th>
                            <th>Total_Salidas_MP</th>
                            <th>Longitud_Unid_MP</th>
                            <th>Total_Mts_Utilizados</th>
                            <th>Fecha_Pedido_Producción</th>
                            <th>Respons_Pedido</th>
                            <th>Nro_Pedido_MP</th>
                            <th>Fecha_Entrega_Calidad</th>
                            <th>Respons_Calidad</th>
         
                        </tr>
                    </thead>
                    <tbody>
                             @php
                                $totalCantBarras = 0; // Inicializa la variable para almacenar la suma
                             @endphp
                                    @foreach ($salidas_mp as $salida_mp)
                                    <!-- Verifica si el filtro está definido o si coincide con el Nro_OF -->
                                    @if (!isset($filtroNroOF) || $salida_mp->listado_of->Nro_OF == $filtroNroOF)
                            <tr>
                                <td>{{$salida_mp->Id_Ingreso_MP}}</td>
                                <td>{{$salida_mp->listado_of->Nro_OF}}</td>
                                <td>{{$salida_mp->listado_of?->producto->Prod_Codigo }}</td>
                                <td>{{$salida_mp->listado_of?->producto?->Prod_Descripcion }}</td>
                                <td>{{$salida_mp->listado_of?->producto?->categoria?->Nombre_Categoria }}</td>
                                <td>{{$salida_mp->listado_of?->Nro_Maquina }}</td>
                                <td>{{$salida_mp->listado_of?->Familia_Maquinas }}</td>
                                <td>{{$salida_mp->listado_of?->Ingreso_mp?->Nro_Ingreso_MP }}</td>
                                <td>{{$salida_mp->listado_of?->Ingreso_mp?->Proveedor?->Prov_Nombre}}</td>
                                <td>{{$salida_mp->listado_of?->Ingreso_mp?->Codigo_MP }}</td>
                                <td>{{$salida_mp->Cantidad_Unidades_MP }}</td>
                                <td>{{$salida_mp->Cantidad_Unidades_MP_Preparadas }}</td>
                                <td>{{$salida_mp->Cantidad_MP_Adicionales }}</td>
                                <td>{{$salida_mp->Cant_Devoluciones}}</td>
                                <td>{{$salida_mp->Total_Salidas_MP }}</td>
                                <td>{{$salida_mp->Longitud_Unidad_MP }}</td>
                                <td>{{$salida_mp->Total_Mtros_Utilizados }}</td>
                                <td>{{$salida_mp->Fecha_del_Pedido_Produccion }}</td>
                                <td>{{$salida_mp->Responsable_Pedido_Produccion}}</td>
                                <td>{{$salida_mp->Nro_Pedido_MP}}</td>
                                <td>{{$salida_mp->Fecha_de_Entrega_Pedido_Calidad}}</td>
                                <td>{{$salida_mp->Responsable_de_entrega_Calidad }}</td>
                                </td>
                                @php
                                    $totalCantBarras += $salida_mp->Total_Salidas_MP;
                                @endphp
                            </tr>
                            @endif
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
    <style>
        /* Estilos para centrar los datos en DataTables */
        #salidas_mp th,
        #salidas_mp td {
            text-align: center; /* Centra el contenido de las celdas */
        }

        <style>
        /* Estilos personalizados para el título */
        .titulo-cantidad {
            font-weight: bold;
            color: blue; /* Cambia el color del texto a azul */
        }

        /* Estilos personalizados para el número */
        .total-numero {
            background-color: green; /* Cambia el color de fondo a verde */
            color: white; /* Cambia el color del texto a blanco */
            padding: 3px 5px; /* Añade un poco de relleno */
            border-radius: 3px; /* Agrega bordes redondeados */
        }

        /* Establece el ancho de las primeras dos columnas */
        #salidas_mp th:nth-child(1),
        #salidas_mp td:nth-child(1),
        #salidas_mp th:nth-child(2),
        #salidas_mp td:nth-child(2) {
            width: 5px; /* Ancho deseado para las primeras dos columnas */
        }
    </style>


    </style>
@endsection

@section('js')
    <!-- Scripts de DataTables aquí -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#salidas_mp').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                // scrollY: '600px', // Altura del área de desplazamiento vertical
                pageLength: 50, // Mostrar solo 10 resultados por defecto
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Clonar la fila de encabezado y agregar filtros
            $('#salidas_mp thead tr').clone(true).prependTo('#salidas_mp thead');

            $('#salidas_mp thead tr:eq(1) th').each(function (i) {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Buscar...' + title + '" />');

                $('input', this).on('keyup change', function () {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            });

            // Escucha el evento de cambio de filtro en la columna "Nro_OF"
            $('#salidas_mp thead tr:eq(1) th:nth-child(2) input').on('keyup change', function () {
                var filtroNroOF_entregas = $(this).val(); // Obtiene el valor del filtro aplicado a "Nro_OF"
                var totalCantBarras = 0; // Inicializa la variable para almacenar la suma

                // Recorre solo las filas visibles de la tabla
                table.rows({ search: 'applied' }).every(function () {
                    // Obtiene los datos de la fila actual como un arreglo
                    var rowData = this.data();

                    // Verifica si el valor de "Nro_OF" coincide con el filtro aplicado
                    if (rowData[1] === filtroNroOF || !filtroNroOF) { // Agrega !filtroNroOF_entregas para manejar el caso de que no haya filtro
                        // Si coincide o no hay filtro, suma el valor de "Cant_Piezas"
                        totalCantBarras += parseFloat(rowData[13]); // La columna "Cant_Piezas" es la décima columna (el índice es 9)
                    }
                });

                // Actualiza el valor total fuera de la tabla
                $('#totalCantBarras').text(totalCantBarras);
            });
        });
    </script>
@stop
