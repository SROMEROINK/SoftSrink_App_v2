{{-- resources/views/Materia_Prima/index.blade.php --}}

@extends('adminlte::page')

@section('title', 'Entrega de Productos - Listado')

@section('content_header')
    <h2>Listado de entregas de Productos</h2>  
    <h1>
        Cantidad de piezas Entregadas: 
        <span id="totalCantPiezas" class="total-numero">0</span>
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
                <table id="entrega_productos" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Id_OF</th>
                            <th>Nro_OF</th>
                            <th>Código de Producto</th>
                            <th>Descripción</th>
                            <th>Clase Familia</th>
                            <th>Nro de Máquina</th>
                            <th>Familia de Máquinas</th>
                            <th>Nro Ingreso_MP</th>
                            <th>Código MP</th>
                            <th>Nro Certificado MP</th>
                            <th>Nombre Proveedor</th>
                            <th>Nro Parcial OF</th>
                            <th>Cant. Piezas entregadas</th>
                            <th>Nro Remito</th>
                            <th>Fecha de entrega</th>
                            <th>Nombre Inspector-control</th>            
                        </tr>
                    </thead>
                    <tbody>
                            @php
                                $totalCantPiezas = 0; // Inicializa la variable para almacenar la suma
                            @endphp
                                    @foreach ($entrega_productos as $entrega_producto)
                                    <!-- Verifica si el filtro está definido o si coincide con el Nro_OF -->
                                    @if (!isset($filtroNroOF_entregas) || $entrega_producto->listado_of->Nro_OF == $filtroNroOF_entregas)
                            <tr>
                                <td>{{$entrega_producto ->Id_List_Entreg_Prod }}</td>
                                <td>{{$entrega_producto ->Id_OF}}</td>
                                <td>{{$entrega_producto ->listado_of->producto->Prod_Codigo }}</td>
                                <td>{{$entrega_producto ->listado_of->producto->Prod_Descripcion }}</td>
                                <td>{{$entrega_producto ->listado_of->producto->categoria->Nombre_Categoria }}</td>
                                <td>{{$entrega_producto ->listado_of->Nro_Maquina }}</td>
                                <td>{{$entrega_producto ->listado_of->Familia_Maquinas }}</td>
                                <td>{{$entrega_producto ->listado_of->Ingreso_mp->Nro_Ingreso_MP }}</td>
                                <td>{{$entrega_producto ->listado_of->Ingreso_mp->Codigo_MP }}</td>
                                <td>{{$entrega_producto ->listado_of->Ingreso_mp->N°_Certificado_MP }}</td>
                                <td>{{$entrega_producto ->listado_of->Ingreso_mp->Proveedor->Prov_Nombre}}</td>
                                <td>{{$entrega_producto ->Nro_Parcial_Calidad }}</td>
                                <td>{{$entrega_producto ->Cant_Piezas_Entregadas }}</td>
                                <td>{{$entrega_producto ->Nro_Remito_Entrega_Calidad }}</td>
                                <td>{{$entrega_producto ->Fecha_Entrega_Calidad }}</td>
                                <td>{{$entrega_producto ->Inspector_Calidad}}</td>
                                </td>
                                @php
                                $totalCantPiezas += $entrega_producto->Cant_Piezas_Entregadas;
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
        #entrega_productos th,
        #entrega_productos td {
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
        #entrega_productos th:nth-child(1),
        #entrega_productos td:nth-child(1),
        #entrega_productos th:nth-child(2),
        #entrega_productos td:nth-child(2) {
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
            var table = $('#entrega_productos').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                // scrollY: '600px', // Altura del área de desplazamiento vertical
                pageLength: 50, // Mostrar solo 10 resultados por defecto
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Clonar la fila de encabezado y agregar filtros
            $('#entrega_productos thead tr').clone(true).prependTo('#entrega_productos thead');

            $('#entrega_productos thead tr:eq(1) th').each(function (i) {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Buscar...' + title + '" />');

                $('input', this).on('keyup change', function () {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            });

            // Escucha el evento de cambio de filtro en la columna "Nro_OF"
            $('#entrega_productos thead tr:eq(1) th:nth-child(2) input').on('keyup change', function () {
                var filtroNroOF_entregas = $(this).val(); // Obtiene el valor del filtro aplicado a "Nro_OF"
                var totalCantPiezas = 0; // Inicializa la variable para almacenar la suma

                // Recorre solo las filas visibles de la tabla
                table.rows({ search: 'applied' }).every(function () {
                    // Obtiene los datos de la fila actual como un arreglo
                    var rowData = this.data();

                    // Verifica si el valor de "Nro_OF" coincide con el filtro aplicado
                    if (rowData[1] === filtroNroOF_entregas || !filtroNroOF_entregas) { // Agrega !filtroNroOF_entregas para manejar el caso de que no haya filtro
                        // Si coincide o no hay filtro, suma el valor de "Cant_Piezas"
                        totalCantPiezas += parseFloat(rowData[12]); // La columna "Cant_Piezas" es la décima columna (el índice es 9)
                    }
                });

                // Actualiza el valor total fuera de la tabla
                $('#totalCantPiezas').text(totalCantPiezas);
            });
        });
    </script>
@stop
