@extends('adminlte::page')

@section('title', 'Programación de la Producción - Listado_OF')

@section('content_header')
<x-header-card 
    title="Programación de la Producción - Listado_OF" 
    quantityTitle="Cantidad de piezas solicitadas:" 
    buttonRoute="{{ route('listado_of.create') }}" 
    buttonText="Crear registro" 
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table id="listado_of" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nro_OF</th>
                            <th>Estado_Planificacion</th>
                            <th>Estado</th>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Nombre_Categoria</th>
                            <th>Nro Plano</th>
                            <th>Nro Revision</th>
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
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_nro_of" placeholder="Filtrar Nro_OF" class="form-control filtro-texto" /></th>
                            <th><select id="filtro_estado_planificacion" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_estado" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_producto" placeholder="Filtrar Producto" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_descripción" placeholder="Filtrar descripción" class="form-control filtro-texto" /></th>
                            <th><select id="filtro_nombre_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_nro_plano" placeholder="Filtrar nro_plano" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_nro_revision" placeholder="Filtrar nro_revision" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_fecha_pedido" placeholder="Filtrar Fecha_del_Pedido" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_cant_fabricacion" placeholder="Filtrar Cant_Fabricacion" class="form-control filtro-texto" /></th>
                            <th><select id="filtro_nro_maquina" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_familia_maquinas" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_mp_id" placeholder="Filtrar MP_Id" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_pedido_mp" placeholder="Filtrar Pedido_de_MP" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_tiempo_pieza_real" placeholder="Filtrar Tiempo_Pieza_Real" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_tiempo_pieza_aprox" placeholder="Filtrar Tiempo_Pieza_Aprox" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_cant_unidades_mp" placeholder="Filtrar Cant_Unidades_MP" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_cant_piezas_por_unidad_mp" placeholder="Filtrar Cant_Piezas_Por_Unidad_MP" class="form-control filtro-texto" /></th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/Listado_OF.css') }}">
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
    var table = $('#listado_of').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('listado_of.data') }}",
            type: 'GET',
            data: function (d) {
                d.filtro_estado_planificacion = $('#filtro_estado_planificacion').val();
                d.filtro_estado = $('#filtro_estado').val();
                d.filtro_nro_maquina = $('#filtro_nro_maquina').val();
                d.filtro_familia_maquinas = $('#filtro_familia_maquinas').val();
                d.filtro_nro_of = $('#filtro_nro_of').val();
                d.filtro_producto = $('#filtro_producto').val();
                d.filtro_Descripción = $('#filtro_descripción').val();
                d.filtro_nombre_categoria = $('#filtro_nombre_categoria').val();
                d.filtro_nro_plano = $('#filtro_nro_plano').val();
                d.filtro_nro_revision = $('#filtro_nro_revision').val();
                d.filtro_fecha_pedido = $('#filtro_fecha_pedido').val();
                d.filtro_cant_fabricacion = $('#filtro_cant_fabricacion').val();
                d.filtro_mp_id = $('#filtro_mp_id').val();
                d.filtro_pedido_mp = $('#filtro_pedido_mp').val();
                d.filtro_tiempo_pieza_real = $('#filtro_tiempo_pieza_real').val();
                d.filtro_tiempo_pieza_aprox = $('#filtro_tiempo_pieza_aprox').val();
                d.filtro_cant_unidades_mp = $('#filtro_cant_unidades_mp').val();
                d.filtro_cant_piezas_por_unidad_mp = $('#filtro_cant_piezas_por_unidad_mp').val();
            }
        },
        columns: [
            { data: 'Nro_OF', name: 'Nro_OF' },
            { data: 'Estado_Planificacion', name: 'Estado_Planificacion' },
            { data: 'Estado', name: 'Estado' },
            { data: 'Producto_Nombre', name: 'Producto_Nombre' },
            { data: 'Descripción', name: 'Descripción' },
            { data: 'Nombre_Categoria', name: 'Nombre_Categoria' },
            { data: 'Prod_N_Plano', name: 'Prod_N_Plano' },
            { data: 'Revision_Plano_2', name: 'Revision_Plano_2' },
            { data: 'Fecha_del_Pedido', name: 'Fecha_del_Pedido' },
            { data: 'Cant_Fabricacion', name: 'Cant_Fabricacion' },
            { data: 'Nro_Maquina', name: 'Nro_Maquina' },
            { data: 'Familia_Maquinas', name: 'Familia_Maquinas' },
            { data: 'MP_Id', name: 'MP_Id' },
            { data: 'Pedido_de_MP', name: 'Pedido_de_MP' },
            { data: 'Tiempo_Pieza_Real', name: 'Tiempo_Pieza_Real' },
            { data: 'Tiempo_Pieza_Aprox', name: 'Tiempo_Pieza_Aprox' },
            { data: 'Cant_Unidades_MP', name: 'Cant_Unidades_MP' },
            { data: 'Cant_Piezas_Por_Unidad_MP', name: 'Cant_Piezas_Por_Unidad_MP' },
        ],
        scrollX: true,
        scrollY: '60vh',
        scrollCollapse: true,
        paging: true,
        searching: false,
        fixedHeader: true,
        responsive: true,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            url: "{{ asset('Spanish.json') }}"
        }
    });

    // Llenar los selects con valores únicos de las columnas especificadas
    table.on('xhr', function () {
        var json = table.ajax.json();
        var uniqueValues = {
            'Estado_Planificacion': new Set(),
            'Estado': new Set(),
            'Nro_Maquina': new Set(),
            'Familia_Maquinas': new Set(),
            'Nombre_Categoria': new Set()
        };

        var totalCantPiezas = 0;

        $.each(json.data, function (index, item) {
            uniqueValues['Estado_Planificacion'].add(item.Estado_Planificacion);
            uniqueValues['Estado'].add(item.Estado);
            uniqueValues['Nro_Maquina'].add(item.Nro_Maquina);
            uniqueValues['Familia_Maquinas'].add(item.Familia_Maquinas);
            uniqueValues['Nombre_Categoria'].add(item.Nombre_Categoria);

            totalCantPiezas += parseFloat(item.Cant_Fabricacion);
        });

        $('#totalCantPiezas').text(totalCantPiezas.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));

        // Llenar los selectores con los valores únicos
        fillSelect('#filtro_estado_planificacion', uniqueValues['Estado_Planificacion']);
        fillSelect('#filtro_estado', uniqueValues['Estado']);
        fillSelect('#filtro_nro_maquina', uniqueValues['Nro_Maquina']);
        fillSelect('#filtro_familia_maquinas', uniqueValues['Familia_Maquinas']);
        fillSelect('#filtro_nombre_categoria', uniqueValues['Nombre_Categoria']);
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
        table.ajax.reload(null, false); // El segundo parámetro asegura que la tabla no se resetee
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
