@extends('adminlte::page')

@section('title', 'Lista de Productos')

@section('content_header')
<x-header-card 
    title="Listado de Productos" 
    quantityTitle="Cantidad de productos:" 
    buttonRoute="{{ route('fabricacion.create') }}" 
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
                            <th>Nombre Tipo</th>
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
                            <th><select id="filtro_tipo" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_familia" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_sub_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_grupo_sub_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
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
<link rel="stylesheet" href="{{ asset('vendor\adminlte\dist\css\Productos_Index.css') }}">
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

// Función genérica para cargar opciones en selectores de filtro
function loadFilterOptions(url, selectId, defaultText) {
    $.ajax({
        url: url,  // URL proporcionada como parámetro
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var selectElement = $(selectId);
                selectElement.empty();
                selectElement.append('<option value="">' + defaultText + '</option>');  // Añadir opción predeterminada
                
                // Llenar el select con los datos obtenidos
                response.data.forEach(function(item) {
                    var value = item.Nombre_Categoria || item.Nombre_Tipo || item.Nombre_SubCategoria || item.Prod_Material_MP || item.Cli_Nombre;
                    selectElement.append('<option value="' + value + '">' + value + '</option>');
                });
            }
        },
        error: function() {
            console.error('No se pudieron cargar los datos para el filtro: ' + selectId);
        }
    });
}

// Llamadas para cargar los filtros
loadFilterOptions("{{ route('productos.categorias') }}", '#filtro_familia', 'Todos');
loadFilterOptions("{{ route('productos.Tipos') }}", '#filtro_tipo', 'Todos');
loadFilterOptions("{{ route('productos.Subcategorias') }}", '#filtro_sub_categoria', 'Todos');
loadFilterOptions("{{ route('productos.getMaterialesMP') }}", '#filtro_material_mp', 'Todos');
loadFilterOptions("{{ route('productos.getClientes') }}", '#filtro_cliente', 'Todos');

var table = $('#listado_productos').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "{{ route('productos.data') }}",
        type: 'GET',
        data: function (d) {
            // Enviar los valores seleccionados en los filtros
            d.filtro_tipo = $('#filtro_tipo').val();
            d.filtro_familia = $('#filtro_familia').val();
            d.filtro_sub_familia = $('#filtro_sub_categoria').val();
            d.filtro_grupo_sub_categoria = $('#filtro_grupo_sub_categoria').val();
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
        { data: 'Nombre_Tipo', name: 'Nombre_Tipo' },
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
        // Llenar los filtros con valores únicos al cargar
        updateFilters(table);
    }
});

    // Función para llenar los selectores con valores únicos, se aplica después de cada búsqueda
    function updateFilters(table) {
        var uniqueValues = {
            'Nombre_Tipo': new Set(),
            'Nombre_Categoria': new Set(),
            'Nombre_SubCategoria': new Set(),
            'Nombre_GrupoSubCategoria': new Set(),
            'Nombre_GrupoConjuntos': new Set(),
            'Cli_Nombre': new Set(),
            'Prod_Material_MP': new Set(),
            'Prod_Diametro_de_MP': new Set(),
            'Prod_Codigo_MP': new Set(),
        };

        // Recorremos los datos actuales de la tabla y llenamos los selectores
        table.rows({search: 'applied'}).data().each(function (item) {
            uniqueValues['Nombre_Tipo'].add(item.Nombre_Tipo);
            uniqueValues['Nombre_Categoria'].add(item.Nombre_Categoria);
            uniqueValues['Nombre_SubCategoria'].add(item.Nombre_SubCategoria);
            uniqueValues['Nombre_GrupoSubCategoria'].add(item.Nombre_GrupoSubCategoria);
            uniqueValues['Nombre_GrupoConjuntos'].add(item.Nombre_GrupoConjuntos);
            uniqueValues['Cli_Nombre'].add(item.Cli_Nombre);
            uniqueValues['Prod_Material_MP'].add(item.Prod_Material_MP);
            uniqueValues['Prod_Diametro_de_MP'].add(item.Prod_Diametro_de_MP);
            uniqueValues['Prod_Codigo_MP'].add(item.Prod_Codigo_MP);
        });

        fillSelect('#filtro_tipo', uniqueValues['Nombre_Tipo']);
        fillSelect('#filtro_familia', uniqueValues['Nombre_Categoria']);
        fillSelect('#filtro_sub_categoria', uniqueValues['Nombre_SubCategoria']);
        fillSelect('#filtro_grupo_sub_categoria', uniqueValues['Nombre_GrupoSubCategoria']);
        fillSelect('#filtro_codigo_conjunto', uniqueValues['Nombre_GrupoConjuntos']);
        fillSelect('#filtro_cliente', uniqueValues['Cli_Nombre']);
        fillSelect('#filtro_material_mp', uniqueValues['Prod_Material_MP']);
        fillSelect('#filtro_diametro_mp', uniqueValues['Prod_Diametro_de_MP']);
        fillSelect('#filtro_codigo_mp', uniqueValues['Prod_Codigo_MP']);
    }

    // Función para llenar los selectores con valores únicos
    function fillSelect(selector, data) {
        var select = $(selector);
        select.empty();
        select.append('<option value="">Todos</option>');
        data.forEach(function (value) {
            select.append('<option value="' + value + '">' + value + '</option>');
        });
    }

    // Aplicar los filtros correlativamente
    $('.filtro-select, .filtro-texto').on('change keyup', function () {
        table.ajax.reload(null, false);  // Recargar sin mover la página
    });

    // Limpiar filtros
    $('#clearFilters').click(function() {
        $('.filtro-select').val('');
        $('.filtro-texto').val('');
        table.ajax.reload();
    });
});

</script>
@stop
