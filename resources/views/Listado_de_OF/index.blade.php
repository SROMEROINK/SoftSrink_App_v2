    {{-- resources/views/Materia_Prima/index.blade.php --}}

    @extends('adminlte::page')

    @section('title', 'Programación de la Producción - Listado_OF')

    @section('content_header')
    <h1 class="text-center custom-cyan-bg text-white fw-bold">Programación de la Producción - Listado_OF</h1>

    <style>
    .custom-cyan-bg {
        background-color: #00BCD4; /* Este es un tono cian */
    }
    </style>

        <h3>
            Cantidad de piezas solicitadas: 
            <span id="totalCantPiezas" class="total-numero">0</span>
        </h3>

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
                    <table id="listado_of" class="display" style="width:100%">
                        <thead>
                            <tr>
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
                                @php
                                    $totalCantPiezas = 0; // Inicializa la variable para almacenar la suma
                                @endphp
                                        @foreach ($listados_of as $listado_of)
                                        <!-- Verifica si el filtro está definido o si coincide con el Nro_OF -->
                                        @if (!isset($filtroNroOF) || $listado_of->Nro_OF == $filtroNroOF)
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
                                    @php
                                    $totalCantPiezas += $listado_of->Cant_Fabricacion;
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
            #listado_of th,
            #listado_of td {
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
            #listado_of th:nth-child(1),
            #listado_of td:nth-child(1),
            #listado_of th:nth-child(2),
            #listado_of td:nth-child(2) {
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
                var table = $('#listado_of').DataTable({
                    orderCellsTop: true,
                    fixedHeader: true,
                    // scrollY: '600px', // Altura del área de desplazamiento vertical
                    pageLength: 50, // Mostrar solo 10 resultados por defecto
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                });

                // Clonar la fila de encabezado y agregar filtros
                $('#listado_of thead tr').clone(true).prependTo('#listado_of thead');

                $('#listado_of thead tr:eq(1) th').each(function (i) {
                    var title = $(this).text();
                    $(this).html('<input type="text" placeholder="Buscar...' + title + '" />');

                    $('input', this).on('keyup change', function () {
                        if (table.column(i).search() !== this.value) {
                            table.column(i).search(this.value).draw();
                        }
                    });
                });

    // Asegúrate de que el índice del 'th' donde se ingresa el filtro coincida con el índice de la columna Nro_OF
    $('#listado_of thead tr:eq(1) th input').on('keyup change', function () {
            var filtroNroOF = $(this).val();
            var totalCantPiezas = 0;

            table.rows({ search: 'applied' }).every(function () {
                var data = this.data();
                if (!filtroNroOF || data[0] === filtroNroOF) { // Asegúrate de que este índice es correcto para Nro_OF
                    totalCantPiezas += parseFloat(data[7]) || 0; // Verifica que el índice 7 corresponde a Cant_Fabricacion
                }
            });

            $('#totalCantPiezas').text(totalCantPiezas); // Actualiza el texto con dos decimales
                });
            });
        </script>
    @stop
