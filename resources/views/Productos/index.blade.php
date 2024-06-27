@extends('adminlte::page')

@section('title', 'Lista de Productos')

@section('content_header')
<x-header-card 
    title="Listado de Productos" 
    quantityTitle="Cantidad de productos:" 
    buttonRoute="{{ route('productos.create') }}" 
    buttonText="Crear registro" 
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table id="listado_productos" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Clasificación Piezas</th>
                            <th>Familia</th>
                            <th>Sub-Familia</th>
                            <th>Grupo-Sub-Familia</th>
                            <th>Código Conjunto</th>
                            <th>Cliente</th>
                            <th>Nº Plano</th>
                            <th>Ult. Revisión Plano</th>
                            <th>Material MP</th>
                            <th>Diámetro MP</th>
                            <th>Código MP</th>
                            <th>Longitud de Pieza</th>
                            <th>Prod Longitud Total</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_id" placeholder="Filtrar ID" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_codigo" placeholder="Filtrar Código" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_descripcion" placeholder="Filtrar Descripción" class="form-control filtro-texto" /></th>
                            <th><select id="filtro_clasificacion_piezas" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_familia" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_sub_familia" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_grupo_sub_familia" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_codigo_conjunto" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_cliente" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_plano" placeholder="Filtrar Nº Plano" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_revision_plano" placeholder="Filtrar Ult. Revisión Plano" class="form-control filtro-texto" /></th>
                            <th><select id="filtro_material_mp" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_diametro_mp" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_codigo_mp" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_longitud_pieza" placeholder="Filtrar Longitud de Pieza" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_longitud_total" placeholder="Filtrar Prod Longitud Total" class="form-control filtro-texto" /></th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/Productos_Index.css') }}">
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
    var table = $('#listado_productos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('productos.data') }}",
            type: 'GET',
            data: function (d) {
                d.filtro_clasificacion_piezas = $('#filtro_clasificacion_piezas').val();
                d.filtro_familia = $('#filtro_familia').val();
                d.filtro_sub_familia = $('#filtro_sub_familia').val();
                d.filtro_grupo_sub_familia = $('#filtro_grupo_sub_familia').val();
                d.filtro_codigo_conjunto = $('#filtro_codigo_conjunto').val();
                d.filtro_cliente = $('#filtro_cliente').val();
                d.filtro_material_mp = $('#filtro_material_mp').val();
                d.filtro_diametro_mp = $('#filtro_diametro_mp').val();
                d.filtro_codigo_mp = $('#filtro_codigo_mp').val();
                d.filtro_id = $('#filtro_id').val();
                d.filtro_codigo = $('#filtro_codigo').val();
                d.filtro_descripcion = $('#filtro_descripcion').val();
                d.filtro_plano = $('#filtro_plano').val();
                d.filtro_revision_plano = $('#filtro_revision_plano').val();
                d.filtro_longitud_pieza = $('#filtro_longitud_pieza').val();
                d.filtro_longitud_total = $('#filtro_longitud_total').val();
            }
        },
        columns: [
            { data: 'Id_Producto', name: 'Id_Producto' },
            { data: 'Prod_Codigo', name: 'Prod_Codigo' },
            { data: 'Prod_Descripcion', name: 'Prod_Descripcion' },
            { data: 'Nombre_Clasificacion', name: 'Nombre_Clasificacion' },
            { data: 'Nombre_Categoria', name: 'Nombre_Categoria' },
            { data: 'Nombre_SubCategoria', name: 'Nombre_SubCategoria' },
            { data: 'Nombre_GrupoSubCategoria', name: 'Nombre_GrupoSubCategoria' },
            { data: 'Nombre_GrupoConjuntos', name: 'Nombre_GrupoConjuntos' },
            { data: 'Cli_Nombre', name: 'Cli_Nombre' },
            { data: 'Prod_N_Plano', name: 'Prod_N_Plano' },
            { data: 'Prod_Plano_Ultima_Revisión', name: 'Prod_Plano_Ultima_Revisión' },
            { data: 'Prod_Material_MP', name: 'Prod_Material_MP' },
            { data: 'Prod_Diametro_de_MP', name: 'Prod_Diametro_de_MP' },
            { data: 'Prod_Codigo_MP', name: 'Prod_Codigo_MP' },
            { data: 'Prod_Longitud_de_Pieza', name: 'Prod_Longitud_de_Pieza' },
            { data: 'Prod_Longitug_Total', name: 'Prod_Longitug_Total' },
        ],
        scrollY: '60vh',
        scrollCollapse: true,
        searching: false,
        paging: true,
        fixedHeader: true,
        responsive: true,
        orderCellsTop: true,
        pageLength: 10,
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
            'Nombre_Clasificacion': new Set(),
            'Nombre_Categoria': new Set(),
            'Nombre_SubCategoria': new Set(),
            'Nombre_GrupoSubCategoria': new Set(),
            'Nombre_GrupoConjuntos': new Set(),
            'Cli_Nombre': new Set(),
            'Prod_Material_MP': new Set(),
            'Prod_Diametro_de_MP': new Set(),
            'Prod_Codigo_MP': new Set(),
        };

        var totalItems = json.data.length;  // Cuenta la cantidad de productos

        $.each(json.data, function (index, item) {
            uniqueValues['Nombre_Clasificacion'].add(item.Nombre_Clasificacion);
            uniqueValues['Nombre_Categoria'].add(item.Nombre_Categoria);
            uniqueValues['Nombre_SubCategoria'].add(item.Nombre_SubCategoria);
            uniqueValues['Nombre_GrupoSubCategoria'].add(item.Nombre_GrupoSubCategoria);
            uniqueValues['Nombre_GrupoConjuntos'].add(item.Nombre_GrupoConjuntos);
            uniqueValues['Cli_Nombre'].add(item.Cli_Nombre);
            uniqueValues['Prod_Material_MP'].add(item.Prod_Material_MP);
            uniqueValues['Prod_Diametro_de_MP'].add(item.Prod_Diametro_de_MP);
            uniqueValues['Prod_Codigo_MP'].add(item.Prod_Codigo_MP);
        });

        $('#totalCantPiezas').text(totalItems.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));  // Actualiza el total de productos

        // Llenar los selectores con los valores únicos
        fillSelect('#filtro_clasificacion_piezas', uniqueValues['Nombre_Clasificacion']);
        fillSelect('#filtro_familia', uniqueValues['Nombre_Categoria']);
        fillSelect('#filtro_sub_familia', uniqueValues['Nombre_SubCategoria']);
        fillSelect('#filtro_grupo_sub_familia', uniqueValues['Nombre_GrupoSubCategoria']);
        fillSelect('#filtro_codigo_conjunto', uniqueValues['Nombre_GrupoConjuntos']);
        fillSelect('#filtro_cliente', uniqueValues['Cli_Nombre']);
        fillSelect('#filtro_material_mp', uniqueValues['Prod_Material_MP']);
        fillSelect('#filtro_diametro_mp', uniqueValues['Prod_Diametro_de_MP']);
        fillSelect('#filtro_codigo_mp', uniqueValues['Prod_Codigo_MP']);
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
});
</script>
@stop
