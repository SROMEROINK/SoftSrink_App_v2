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
    </div>
</div>
@stop

@section('content')
<div class="">
    <table id="registro_de_fabricacion" class="table-striped table-bordered" style="width:100%">
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
        </tbody>
    </table>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_index.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js"></script>
<script>
$(document).ready(function () {
    var table =  $('#registro_de_fabricacion').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('fabricacion.data') }}',
        columns: [
            { data: 'Id_OF' },
            { data: 'Nro_OF' },
            { data: 'Prod_Codigo' },
            { data: 'Prod_Descripcion' },
            { data: 'Nombre_Categoria' },
            { data: 'Nro_Maquina' },
            { data: 'Familia_Maquinas' },
            { data: 'Fecha_Fabricacion' },
            { data: 'Nro_Parcial' },
            { data: 'Cant_Piezas' },
            { data: 'Horario' },
            { data: 'Nombre_Operario' },
            { data: 'Turno' },
            { data: 'Cant_Horas_Extras' },
            { data: 'created_at' },
            { data: 'creator' },
            { data: 'updated_at' },
            { data: 'updater' }
        ],
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        language: {
            lengthMenu: "Mostrar _MENU_ registros por página",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando página _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search: "Buscar:",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });

    table.on('xhr', function () {
    var json = table.ajax.json();
    var totalCantPiezas = 0;
    $.each(json.data, function (index, item) {
        totalCantPiezas += parseFloat(item.Cant_Piezas);
    });
    $('#totalCantPiezas').text(totalCantPiezas);
});

$('#registro_de_fabricacion thead tr:eq(1) th:nth-child(2) input').on('keyup change', function () {
    var filtroNroOF = $(this).val();
    var totalCantPiezas = 0;
    table.rows({ search: 'applied' }).every(function () {
        var rowData = this.data();
        if (rowData.Nro_OF === filtroNroOF || !filtroNroOF) {
            totalCantPiezas += parseFloat(rowData.Cant_Piezas);
        }
    });
    $('#totalCantPiezas').text(totalCantPiezas);
});
});



</script>
@stop
