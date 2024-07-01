@extends('adminlte::page')

@section('title', 'Listado de Entregas de Productos')

@section('content_header')
<x-header-card 
    title="Listado de Entregas de Productos" 
    quantityTitle="Cantidad de piezas Entregadas:" 
    buttonRoute="{{ route('fabricacion.create') }}" 
    buttonText="Crear registro" 
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table id="entrega_productos" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Id_OF</th>
                            <th>Nro_OF</th>
                            <th>Código de Producto</th>
                            <th>Descripción</th>
                            <th>Clase Familia</th>
                            <th>Nro de Máquina</th>
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
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_id" placeholder="Filtrar ID" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_nro_of" placeholder="Filtrar Nro_OF" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_codigo_producto" placeholder="Filtrar Código de Producto" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_descripcion" placeholder="Filtrar Descripción" class="form-control filtro-texto" /></th>
                            <th><select id="filtro_clase_familia" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_nro_maquina" placeholder="Filtrar Nro de Máquina" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_nro_ingreso_mp" placeholder="Filtrar Nro Ingreso_MP" class="form-control filtro-texto" /></th>
                            <th><select id="filtro_codigo_mp" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_nro_certificado_mp" placeholder="Filtrar Nro Certificado MP" class="form-control filtro-texto" /></th>
                            <th><select id="filtro_nombre_proveedor" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_nro_parcial_of" placeholder="Filtrar Nro Parcial OF" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_cant_piezas" placeholder="Filtrar Cant. Piezas entregadas" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_nro_remito" placeholder="Filtrar Nro Remito" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_fecha_entrega" placeholder="Filtrar Fecha de entrega" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_nombre_inspector" placeholder="Filtrar Nombre Inspector-control" class="form-control filtro-texto" /></th>
                        </tr>
                    </thead>
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
<link rel="stylesheet" href="{{ asset('vendor\adminlte\dist\css\Listado_Entregas.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#entrega_productos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('entregas_productos.data') }}",
            type: 'GET',
            data: function (d) {
                d.filtro_clase_familia = $('#filtro_clase_familia').val();
                d.filtro_codigo_mp = $('#filtro_codigo_mp').val();
                d.filtro_nombre_proveedor = $('#filtro_nombre_proveedor').val();
                d.filtro_nombre_inspector = $('#filtro_nombre_inspector').val();
                d.filtro_id = $('#filtro_id').val();
                d.filtro_nro_of = $('#filtro_nro_of').val();
                d.filtro_codigo_producto = $('#filtro_codigo_producto').val();
                d.filtro_descripcion = $('#filtro_descripcion').val();
                d.filtro_nro_maquina = $('#filtro_nro_maquina').val();
                d.filtro_nro_ingreso_mp = $('#filtro_nro_ingreso_mp').val();
                d.filtro_nro_certificado_mp = $('#filtro_nro_certificado_mp').val();
                d.filtro_nro_parcial_of = $('#filtro_nro_parcial_of').val();
                d.filtro_cant_piezas = $('#filtro_cant_piezas').val();
                d.filtro_nro_remito = $('#filtro_nro_remito').val();
                d.filtro_fecha_entrega = $('#filtro_fecha_entrega').val();
            }
        },
        columns: [
            { data: 'Id_List_Entreg_Prod', name: 'Id_List_Entreg_Prod' },
            { data: 'Id_OF', name: 'Id_OF' },
            { data: 'Prod_Codigo', name: 'Prod_Codigo' },
            { data: 'Prod_Descripcion', name: 'Prod_Descripcion' },
            { data: 'Nombre_Categoria', name: 'Nombre_Categoria' },
            { data: 'Nro_Maquina', name: 'Nro_Maquina' },
            { data: 'Nro_Ingreso_MP', name: 'Nro_Ingreso_MP' },
            { data: 'Codigo_MP', name: 'Codigo_MP' },
            { data: 'N_Certificado_MP', name: 'N_Certificado_MP' },
            { data: 'Nombre_Proveedor', name: 'Nombre_Proveedor' },
            { data: 'Nro_Parcial_Calidad', name: 'Nro_Parcial_Calidad' },
            { data: 'Cant_Piezas_Entregadas', name: 'Cant_Piezas_Entregadas' },
            { data: 'Nro_Remito_Entrega_Calidad', name: 'Nro_Remito_Entrega_Calidad' },
            { data: 'Fecha_Entrega_Calidad', name: 'Fecha_Entrega_Calidad' },
            { data: 'Inspector_Calidad', name: 'Inspector_Calidad' },
        ],
        scrollY: '60vh',
        scrollCollapse: true,
        searching: false,
        paging: true,
        fixedHeader: true,
        responsive: true,
        orderCellsTop: true,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        language: {
            url: "{{ asset('Spanish.json') }}"
        },
        initComplete: function () {
            var api = this.api();
            api.columns().every(function () {
                var column = this;
                if ($(column.header()).hasClass('filtro-select')) {
                    var select = $('<select><option value="">Todos</option></select>')
                        .appendTo($(column.header()).find('input').parent().empty())
                        .on('change', function () {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());
                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                        });

                    column.data().unique().sort().each(function (d, j) {
                        select.append('<option value="' + d + '">' + d + '</option>')
                    });
                }
            });
        }
    });

    // Llenar los selects con valores únicos de las columnas especificadas
    table.on('xhr', function () {
        var json = table.ajax.json();
        var uniqueValues = {
            'Nombre_Categoria': new Set(),
            'Codigo_MP': new Set(),
            'Nombre_Proveedor': new Set()
        };

        var totalCantPiezas = 0;  // Inicializa la variable para almacenar la suma

        $.each(json.data, function (index, item) {
            uniqueValues['Nombre_Categoria'].add(item.Nombre_Categoria);
            uniqueValues['Codigo_MP'].add(item.Codigo_MP);
            uniqueValues['Nombre_Proveedor'].add(item.Nombre_Proveedor);

            totalCantPiezas += parseFloat(item.Cant_Piezas_Entregadas);  // Suma las piezas entregadas
        });

        $('#totalCantPiezas').text(totalCantPiezas.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));  // Actualiza el total de piezas entregadas

        // Llenar los selectores con los valores únicos
        fillSelect('#filtro_clase_familia', uniqueValues['Nombre_Categoria']);
        fillSelect('#filtro_codigo_mp', uniqueValues['Codigo_MP']);
        fillSelect('#filtro_nombre_proveedor', uniqueValues['Nombre_Proveedor']);
    });

    function fillSelect(selector, data) {
        var select = $(selector);
        select.empty();
        select.append('<option value="">Todos</option>');
        data.forEach(function (value) {
            select.append('<option value="' + value + '">' + value + '</option>');
        });
    }

    // Recargar la tabla al cambiar los selectores y campos de texto
    $('.filtro-select, .filtro-texto').on('change keyup', function () {
        table.ajax.reload();
    });

    // Funcionalidad para limpiar filtros
    $('#clearFilters').click(function() {
        $('.filtro-select').val('');
        $('.filtro-texto').val('');
        table.ajax.reload();
    });
});
</script>
@stop
