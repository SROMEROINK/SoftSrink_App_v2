@extends('adminlte::page')

@section('title', 'Fabricación - Registro_De_Fabricación')

@section('content_header')
<div class="card card-title-header">
    <h4 class="text-center">Registro de Fabricación</h4>  
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="info-section">
            <span class="card-title titulo-cantidad">Cantidad de piezas fabricadas:</span>
            <span id="totalCantPiezas" class="total-numero">0</span>
        </div>
        <a href="{{ route('fabricacion.create') }}" class="btn btn-success create-button">Crear registro</a>
    </div>
</div>

@include('partials.navigation')

@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
    <table id="registro_de_fabricacion" class="table table-striped table-bordered" style="width:100%">
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
            <tr class="filter-row">
                <th></th>
                <th><input type="text" id="filtro_nro_of" class="form-control filtro-texto" placeholder="Buscar Nro_OF"></th>
                <th></th>
                <th></th>
                <th><select id="filtro_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                <th><select id="filtro_maquina" class="form-control filtro-select"><option value="">Todos</option></select></th>
                <th><select id="filtro_familia" class="form-control filtro-select"><option value="">Todos</option></select></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_index.css') }}">
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    var table = $('#registro_de_fabricacion').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('fabricacion.data') }}",
            type: 'GET',
            data: function (d) {
                d.filtro_nro_of = $('#filtro_nro_of').val();
                d.filtro_categoria = $('#filtro_categoria').val();
                d.filtro_maquina = $('#filtro_maquina').val();
                d.filtro_familia = $('#filtro_familia').val();
            }
        },
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
            { data: 'updater' },
            {
                data: 'Nro_OF',
                render: function (data) {
                    return `<a href="/fabricacion/show/${data}" class="btn btn-info btn-sm">Ver Parciales</a>`;
                },
                orderable: false,
                searchable: false
            }
        ],
        scrollX: true,
        scrollY: '60vh',
        scrollCollapse: true,
        paging: true,
        fixedHeader: true,
        responsive: true,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
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

    // Agregar opciones únicas a los filtros
    table.on('xhr', function () {
        var json = table.ajax.json();
        var totalCantPiezas = 0;
        var categorias = new Set();
        var maquinas = new Set();
        var familias = new Set();

        
        $.each(json.data, function (index, item) {
            totalCantPiezas += parseFloat(item.Cant_Piezas);
            categorias.add(item.Nombre_Categoria);
            maquinas.add(item.Nro_Maquina);
            familias.add(item.Familia_Maquinas);
        });

        $('#totalCantPiezas').text(totalCantPiezas.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));

        // Rellenar los selectores de filtro
        rellenarSelect('#filtro_categoria', categorias);
        rellenarSelect('#filtro_maquina', maquinas);
        rellenarSelect('#filtro_familia', familias);
    });

    // Función para rellenar los selectores de filtro
    function rellenarSelect(selector, data) {
        var select = $(selector);
        select.empty();
        select.append('<option value="">Todos</option>');
        data.forEach(function (value) {
            select.append('<option value="' + value + '">' + value + '</option>');
        });
    }

    // Filtros personalizados
    $('#filtro_categoria').on('change', function () {
        table.ajax.reload();
    });

    $('#filtro_maquina').on('change', function () {
        table.ajax.reload();
    });

    $('#filtro_familia').on('change', function () {
        table.ajax.reload();
    });

    $('#filtro_nro_of').on('keyup change', function () {
        table.ajax.reload();
    });

    
});
</script>
@stop
