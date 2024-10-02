{{-- resources/views/materia_prima/ingresos/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Materia Prima - Ingresos')

@section('content_header')
<x-header-card 
    title="Ingresos de Materia Prima" 
    quantityTitle="Total de Unidades Ingresadas:" 
    buttonRoute="{{ route('mp_ingresos.create') }}" 
    buttonText="Crear Ingreso" 
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table id="ingresos_materia_prima" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Id_MP</th>
                            <th>Nro_Ingreso_MP</th>
                            <th>Nro_Pedido</th>
                            <th>Nro_Remito</th>
                            <th>Fecha_Ingreso</th>
                            <th>Nro_OC</th>
                            <th>Proveedor</th>
                            <th>Materia_Prima</th>
                            <th>Diametro_MP</th>
                            <th>Codigo_MP</th>
                            <th>Nro_Certificado_MP</th>
                            <th>Detalle_Origen_MP</th>
                            <th>Unidades_MP</th>
                            <th>Longitud_Unidad_MP</th>
                            <th>Mts_Totales</th>
                            <th>Kilos_Totales</th>
                            <th>created_at</th>
                            <th>updated_at</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th></th>
                            <th><input type="text" id="filtro_nro_ingreso" class="form-control filtro-texto" placeholder="Buscar Nro_Ingreso_MP"></th>
                            <th><input type="text" id="filtro_nro_pedido" class="form-control filtro-texto" placeholder="Filtrar Nro_Pedido"></th>
                            <th><input type="text" id="filtro_nro_remito" class="form-control filtro-texto" placeholder="Filtrar Nro_Remito"></th>
                            <th><input type="text" id="filtro_fecha_ingreso" class="form-control filtro-texto" placeholder="Filtrar Fecha"></th>
                            <th><input type="text" id="filtro_nro_oc" class="form-control filtro-texto" placeholder="Filtrar Nro_OC"></th>
                            <th><select id="filtro_proveedor" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_materia_prima" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_diametro" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_codigo_mp" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_certificado" class="form-control filtro-texto" placeholder="Filtrar Certificado"></th>
                            <th><input type="text" id="filtro_detalle_origen" class="form-control filtro-texto" placeholder="Filtrar Detalle_Origen"></th>
                            <th><input type="text" id="filtro_unidades" class="form-control filtro-texto" placeholder="Filtrar Unidades"></th>
                            <th><input type="text" id="filtro_longitud" class="form-control filtro-texto" placeholder="Filtrar Longitud"></th>
                            <th><input type="text" id="filtro_mts_totales" class="form-control filtro-texto" placeholder="Filtrar Mts_Totales"></th>
                            <th><input type="text" id="filtro_kilos_totales" class="form-control filtro-texto" placeholder="Filtrar Kilos"></th>
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
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_ingreso_index.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    // Variable para verificar si los filtros ya fueron cargados
    var filtersLoaded = false;

    // Inicialización de la tabla DataTables
    var table = $('#ingresos_materia_prima').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('mp_ingresos.data') }}",
            type: 'GET',
            data: function (d) {
                // Pasar todos los filtros al servidor como parámetros
                d.filtro_proveedor = $('#filtro_proveedor').val();
                d.filtro_materia_prima = $('#filtro_materia_prima').val();
                d.filtro_diametro = $('#filtro_diametro').val();
                d.filtro_codigo_mp = $('#filtro_codigo_mp').val();
                d.filtro_nro_ingreso = $('#filtro_nro_ingreso').val();
                d.filtro_nro_pedido = $('#filtro_nro_pedido').val();
                d.filtro_nro_remito = $('#filtro_nro_remito').val();
                d.filtro_fecha_ingreso = $('#filtro_fecha_ingreso').val();
                d.filtro_nro_oc = $('#filtro_nro_oc').val();
                d.filtro_certificado = $('#filtro_certificado').val();
                d.filtro_detalle_origen = $('#filtro_detalle_origen').val();
                d.filtro_unidades = $('#filtro_unidades').val();
                d.filtro_longitud = $('#filtro_longitud').val();
                d.filtro_mts_totales = $('#filtro_mts_totales').val();
                d.filtro_kilos_totales = $('#filtro_kilos_totales').val();
            }
        },
        columns: [
            { data: 'Id_MP' },
            { data: 'Nro_Ingreso_MP' },
            { data: 'Nro_Pedido' },
            { data: 'Nro_Remito' },
            { data: 'Fecha_Ingreso' },
            { data: 'Nro_OC' },
            { data: 'Proveedor' },
            { data: 'Materia_Prima' },
            { data: 'Diametro_MP' },
            { data: 'Codigo_MP' },
            { data: 'Nro_Certificado_MP' },
            { data: 'Detalle_Origen_MP' },
            { data: 'Unidades_MP' },
            { data: 'Longitud_Unidad_MP' },
            { data: 'Mts_Totales' },
            { data: 'Kilos_Totales' },
            { data: 'mp_ingreso_created_at' },
            { data: 'mp_ingreso_updated_at' },
            {
                data: 'Id_MP',
                render: function (data) {
                    return `<a href="/mp_ingresos/${data}" class="btn btn-info btn-sm">Ver</a>`;
                },
                orderable: false,
                searchable: false
            }
        ],
        responsive: true,
        paging: true,
        lengthMenu: [[10, 25, 50], [10, 25, 50]],
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
        },
        drawCallback: function () {
            // Evitar que los filtros se recarguen con base en los datos de la tabla
            if (!filtersLoaded) {
                loadUniqueFilters(); // Cargar los filtros únicos solo una vez
                filtersLoaded = true;
            }
        }
    });

    // Función para cargar los filtros únicos desde el servidor
    function loadUniqueFilters() {
        $.ajax({
            url: "{{ route('mp_ingresos.filters') }}",
            type: 'GET',
            success: function (data) {
                rellenarSelect('#filtro_proveedor', data.proveedores);
                rellenarSelect('#filtro_materia_prima', data.materias_primas);
                rellenarSelect('#filtro_diametro', data.diametros);
                rellenarSelect('#filtro_codigo_mp', data.codigos);
            }
        });
    }

    // Función para rellenar los selectores con los datos únicos
    function rellenarSelect(selector, data) {
        var select = $(selector);
        select.empty();
        select.append('<option value="">Todos</option>');
        data.forEach(function (value) {
            select.append('<option value="' + value + '">' + value + '</option>');
        });
    }

    // Recargar la tabla cuando cambie algún filtro
    $('#filtro_proveedor, #filtro_materia_prima, #filtro_diametro, #filtro_codigo_mp').on('change', function () {
        table.ajax.reload();
    });

    // Limpiar los filtros al hacer clic en el botón
    $('#clearFilters').click(function() {
        $('.filtro-select').val('');
        $('.filtro-texto').val('');
        table.ajax.reload();
    });
});
</script>
@stop
