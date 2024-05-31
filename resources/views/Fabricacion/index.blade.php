@extends('adminlte::page')

@section('title', 'Fabricación - Registro_De_Fabricación')

@section('content_header')
    <h2>Registro de Fabricación</h2>  
    <h1>
        Cantidad de piezas fabricadas: 
        <span id="totalCantPiezas" class="total-numero">0</span>
    </h1>
    <a href="{{ route('fabricacion.create') }}" class="btn btn-success">Ir a Carga de Producción</a>

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
                <table id="registro_de_fabricacion" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Id_OF</th>
                            <th>Nro_OF</th>
                            <th>Código de Producto</th>
                            <th>Descripción</th>
                            <th>Clase Familia</th>
                            <th>Nro de Máquina</th>
                            <th>Familia de Máquinas</th>
                            <th>Fecha_Fabricacion</th>
                            <th>Nro_Parcial</th>
                            <th>Cant_Piezas</th>
                            <th>Horario</th>
                            <th>Nombre_Operario</th>
                            <th>Turno</th>
                            <th>Cant_Horas_Extras</th>           
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalCantPiezas = 0; // Inicializa la variable para almacenar la suma
                        @endphp
                        @foreach ($registros_fabricacion as $registro_fabricacion)
                            @if (!isset($filtroNroOF) || $registro_fabricacion->listado_of->Nro_OF == $filtroNroOF)
                                <tr>
                                    <td>{{ $registro_fabricacion->Id_OF }}</td>
                                    <td>{{ $registro_fabricacion->listado_of->Nro_OF }}</td>
                                    <td>{{ $registro_fabricacion->listado_of->producto->Prod_Codigo }}</td>
                                    <td>{{ $registro_fabricacion->listado_of->producto->Prod_Descripcion }}</td>
                                    <td>{{ $registro_fabricacion->listado_of->producto->categoria->Nombre_Categoria }}</td>
                                    <td>{{ $registro_fabricacion->listado_of->Nro_Maquina }}</td>
                                    <td>{{ $registro_fabricacion->listado_of->Familia_Maquinas }}</td>
                                    <td>{{ $registro_fabricacion->Fecha_Fabricacion }}</td>
                                    <td>{{ $registro_fabricacion->Nro_Parcial }}</td>
                                    <td>{{ $registro_fabricacion->Cant_Piezas }}</td>
                                    <td>{{ $registro_fabricacion->Horario }}</td>
                                    <td>{{ $registro_fabricacion->Nombre_Operario }}</td>
                                    <td>{{ $registro_fabricacion->Turno }}</td>
                                    <td>{{ $registro_fabricacion->Cant_Horas_Extras }}</td>
                                </tr>
                                @php
                                    $totalCantPiezas += $registro_fabricacion->Cant_Piezas;
                                @endphp
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_index.css') }}">
    <!-- Agrega los estilos de DataTables aquí -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css">
@endsection

@section('js')
    <!-- Scripts de DataTables aquí -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#registro_de_fabricacion').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                pageLength: 50, // Mostrar solo 50 resultados por defecto
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Clonar la fila de encabezado y agregar filtros
            $('#registro_de_fabricacion thead tr').clone(true).prependTo('#registro_de_fabricacion thead');

            $('#registro_de_fabricacion thead tr:eq(1) th').each(function (i) {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Buscar ' + title + '" />');

                $('input', this).on('keyup change', function () {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            });

            // Escucha el evento de cambio de filtro en la columna "Nro_OF"
            $('#registro_de_fabricacion thead tr:eq(1) th:nth-child(2) input').on('keyup change', function () {
                var filtroNroOF = $(this).val(); // Obtiene el valor del filtro aplicado a "Nro_OF"
                var totalCantPiezas = 0; // Inicializa la variable para almacenar la suma

                // Recorre solo las filas visibles de la tabla
                table.rows({ search: 'applied' }).every(function () {
                    // Obtiene los datos de la fila actual como un arreglo
                    var rowData = this.data();

                    // Verifica si el valor de "Nro_OF" coincide con el filtro aplicado
                    if (rowData[1] === filtroNroOF || !filtroNroOF) { // Agrega !filtroNroOF para manejar el caso de que no haya filtro
                        // Si coincide o no hay filtro, suma el valor de "Cant_Piezas"
                        totalCantPiezas += parseFloat(rowData[9]); // La columna "Cant_Piezas" es la décima columna (el índice es 9)
                    }
                });

                // Actualiza el valor total fuera de la tabla
                $('#totalCantPiezas').text(totalCantPiezas);
            });
        });
    </script>
@stop

