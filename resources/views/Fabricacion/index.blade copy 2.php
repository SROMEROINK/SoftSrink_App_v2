@extends('adminlte::page')

@section('title', 'Fabricación - Registro_De_Fabricación')

@section('content_header')
<div class="card">
    
    <h4 class="text-center">Registro de Fabricación</h4>  
</div>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Cantidad de piezas fabricadas : </h3>
        <span id="totalCantPiezas" class="total-numero">0</span>
        <a href="{{ route('fabricacion.create') }}" class="btn btn-success text-right">Ir a Carga de Producción</a>
    </div>
</div>
    

@stop

@section('content')
    <div class="">
                <!-- Tu contenido va aquí -->
                <table id="registro_de_fabricacion" class="table table-striped" style="width:100%">
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
                            <th>Creado el</th>
                            <th>Creado por</th>
                            <th>Actualizado el</th>
                            <th>Actualizado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <td>1</td>
                        <td>2</td>
                        <td>3</td>
                        <td>4</td>
                        <td>5</td>
                        <td>6</td>
                        <td>7</td>
                        <td>8</td>
                        <td>9</td>
                        <td>10</td>
                        <td>11</td>
                        <td>12</td>
                        <td>13</td>
                        <td>14</td>
                        <td>15</td>
                        <td>16</td>
                        <td>17</td>
                        <td>18</td>
                        </tr>
                        <tr>
                        <td>a</td>
                        <td>b</td>
                        <td>c</td>
                        <td>d</td>
                        <td>e</td>
                        <td>f</td>
                        <td>g</td>
                        <td>h</td>
                        <td>i</td>
                        <td>j</td>
                        <td>k</td>
                        <td>m</td>
                        <td>n</td>
                        <td>o</td>
                        <td>p</td>
                        <td>r</td>
                        <td>s</td>
                        <td>t</td>
                        </tr>
                        {{-- @php
                            $totalCantPiezas = 0; // Inicializa la variable para almacenar la suma
                        @endphp
                        @foreach ($registros_fabricacion as $registro_fabricacion)
                            @if (!isset($filtroNroOF) || $registro_fabricacion->listado_of->Nro_OF == $filtroNroOF)
                                
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
                                    <td>{{ $registro_fabricacion->created_at }}</td>
                                    <td>{{ optional($registro_fabricacion->creator)->name }}</td> <!-- Mostrar nombre del creador -->
                                    <td>{{ $registro_fabricacion->updated_at }}</td>
                                    <td>{{ optional($registro_fabricacion->updater)->name }}</td> <!-- Mostrar nombre del actualizador -->
                                </tr>
                                @php
                                    $totalCantPiezas += $registro_fabricacion->Cant_Piezas;
                                @endphp
                            @endif
                        @endforeach --}}
                    </tbody>
                </table>
                <select id="mySelect">
                    <option value="">All</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
                </div> 
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_index.css') }}">
    <!-- Agrega los estilos de DataTables aquí -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
@endsection

@section('js')
    <!-- Scripts de DataTables aquí -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#registro_de_fabricacion').DataTable({
                layout: {
                    topEnd: function (){
                    let btn =  document.createElement('button')
                    btn.textContent = 'Botón de prueba';
                    btn.classList.add('btn', 'btn-primary');
                    btn.addEventListener('click', function () {
                        alert('¡Hola, mundo!');
                    });
                    return btn;
                    },
        topStart: {
                        search: {
            placeholder: 'Buscar en la tabla',
                        } // Puedo agregar un botón de búsqueda avanzada,
                    },

        bottomStart: { 
        pageLength:{ 
        menu:[10, 25, 50]}
}, // Puedo agregar "All" para mostrar todos los registros



        bottomEnd: {
            paging: {
               numbers: 5 // Puedo agregar "simple" para mostrar solo los botones de siguiente y anterior
            }
        }
    },
    

                
                language: {
                "decimal": "",
                "emptyTable": "No hay información",
                "info": "Mostrando START a END de TOTAL Entradas",
                "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                "infoFiltered": "(Filtrado de MAX total entradas)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar MENU Entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
                },
                "order": [[0, "desc"]],
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": true,
                "responsive": true,
                "fixedHeader": true,
                "scrollX": true,
                "scrollY": true,
                "scrollCollapse": true,
                "fixedColumns": {
                    leftColumns: 1,
                    rightColumns: 1
                }
            },  


});

table.caption('Registro de Fabricación: Tabla de datos de producción.');
// table.ready(function () {
//     table.search.fixed('Clase Familia', '1').draw(); // Filtra la tabla por la columna "Clase Familia" con el valor "1"
// });

$('#mySelect').on('change', function () {
    table.search.fixed('Clase Familia', $(this).value()).draw();
    console.log($(this).value());
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

