@extends('adminlte::page')

@section('title', 'Listado OF')

@section('content_header')
<x-header-card
    title="Listado OF - Resumen de Produccion"
    buttonRoute="{{ route('pedido_cliente.index') }}"
    buttonText="Ir a Pedidos"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        <strong>Listado OF:</strong>
        resumen vivo de <strong>listado_of_db</strong> con pedido, maquina, MP y fabricacion consolidados.
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-of">0</h3>
                    <p>OF cargadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="total-piezas-solicitadas">0</h3>
                    <p>Piezas solicitadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="total-piezas-fabricadas">0</h3>
                    <p>Piezas fabricadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-industry"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive table-responsive-listado-of">
                <table id="tabla_listado_of" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nro OF</th>
                            <th>Planificacion</th>
                            <th>Estado</th>
                            <th>Producto</th>
                            <th>Descripcion</th>
                            <th>Categoria</th>
                            <th>Rev. Plano 1</th>
                            <th>Rev. Plano 2</th>
                            <th>Fecha Pedido</th>
                            <th>Cant. Fabricacion</th>
                            <th>Nro Maquina</th>
                            <th>Familia Maquina</th>
                            <th>MP Id</th>
                            <th>Nro Ingreso MP</th>
                            <th>Codigo MP</th>
                            <th>Certificado MP</th>
                            <th>Pedido MP</th>
                            <th>Proveedor</th>
                            <th>Piezas Fabricadas</th>
                            <th>Saldo</th>
                            <th>Ult. Fabricacion</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_nro_of" class="form-control filtro-texto" placeholder="Filtrar OF"></th>
                            <th><select id="filtro_estado_planificacion" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_estado" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_producto" class="form-control filtro-texto" placeholder="Filtrar Producto"></th>
                            <th><input type="text" id="filtro_descripcion" class="form-control filtro-texto" placeholder="Filtrar Descripcion"></th>
                            <th><select id="filtro_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th></th>
                            <th></th>
                            <th><input type="date" id="filtro_fecha_pedido" class="form-control filtro-texto"></th>
                            <th></th>
                            <th><select id="filtro_nro_maquina" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_familia_maquina" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th></th>
                            <th><input type="text" id="filtro_nro_ingreso_mp" class="form-control filtro-texto" placeholder="Filtrar Ingreso"></th>
                            <th><input type="text" id="filtro_codigo_mp" class="form-control filtro-texto" placeholder="Filtrar Codigo MP"></th>
                            <th><input type="text" id="filtro_certificado_mp" class="form-control filtro-texto" placeholder="Filtrar Certificado"></th>
                            <th></th>
                            <th><select id="filtro_proveedor" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_piezas_fabricadas" class="form-control filtro-texto" placeholder="Filtrar Fabricadas"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/datatables.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/summary-boxes.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/listado_of_index.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
function formatearNumeroEntero(valor) {
    const numero = Number(valor || 0);
    return numero.toLocaleString('es-AR');
}

$(document).ready(function () {
    function filtrosActuales() {
        return {
            filtro_nro_of: $('#filtro_nro_of').val(),
            filtro_estado_planificacion: $('#filtro_estado_planificacion').val(),
            filtro_estado: $('#filtro_estado').val(),
            filtro_producto: $('#filtro_producto').val(),
            filtro_descripcion: $('#filtro_descripcion').val(),
            filtro_categoria: $('#filtro_categoria').val(),
            filtro_fecha_pedido: $('#filtro_fecha_pedido').val(),
            filtro_nro_maquina: $('#filtro_nro_maquina').val(),
            filtro_familia_maquina: $('#filtro_familia_maquina').val(),
            filtro_nro_ingreso_mp: $('#filtro_nro_ingreso_mp').val(),
            filtro_codigo_mp: $('#filtro_codigo_mp').val(),
            filtro_certificado_mp: $('#filtro_certificado_mp').val(),
            filtro_proveedor: $('#filtro_proveedor').val(),
            filtro_piezas_fabricadas: $('#filtro_piezas_fabricadas').val()
        };
    }

    function cargarResumen() {
        $.get("{{ route('listado_of.resumen') }}", filtrosActuales(), function (data) {
            $('#total-of').text(formatearNumeroEntero(data.total_of));
            $('#total-piezas-solicitadas').text(formatearNumeroEntero(data.total_piezas_solicitadas));
            $('#total-piezas-fabricadas').text(formatearNumeroEntero(data.total_piezas_fabricadas));
        });
    }

    const table = $('#tabla_listado_of').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: true,
        responsive: false,
        orderCellsTop: true,
        pageLength: 25,
        order: [[0, 'desc']],
        ajax: {
            url: "{{ route('listado_of.data') }}",
            data: function (d) {
                Object.assign(d, filtrosActuales());
            }
        },
        columns: [
            { data: 'Nro_OF', name: 'lo.Nro_OF', className: 'text-nowrap' },
            { data: 'Estado_Planificacion', name: 'lo.Estado_Planificacion', className: 'text-nowrap' },
            { data: 'Estado', name: 'lo.Estado', className: 'text-nowrap' },
            { data: 'Prod_Codigo', name: 'lo.Prod_Codigo', className: 'text-nowrap' },
            { data: 'Prod_Descripcion', name: 'lo.Prod_Descripcion' },
            { data: 'Nombre_Categoria', name: 'lo.Nombre_Categoria', className: 'text-nowrap' },
            { data: 'Revision_Plano_1', name: 'lo.Revision_Plano_1', className: 'text-nowrap' },
            { data: 'Revision_Plano_2', name: 'lo.Revision_Plano_2', className: 'text-nowrap' },
            { data: 'Fecha_del_Pedido', name: 'lo.Fecha_del_Pedido', className: 'text-nowrap' },
            {
                data: 'Cant_Fabricacion',
                name: 'lo.Cant_Fabricacion',
                className: 'text-nowrap',
                render: function (data, type) {
                    return (type === 'display' || type === 'filter') ? formatearNumeroEntero(data) : data;
                }
            },
            { data: 'Nro_Maquina', name: 'lo.Nro_Maquina', className: 'text-nowrap' },
            { data: 'Familia_Maquinas', name: 'lo.Familia_Maquinas', className: 'text-nowrap' },
            { data: 'MP_Id', name: 'lo.MP_Id', className: 'text-nowrap' },
            { data: 'Nro_Ingreso_MP', name: 'lo.Nro_Ingreso_MP', className: 'text-nowrap' },
            { data: 'Codigo_MP', name: 'lo.Codigo_MP', className: 'text-nowrap' },
            { data: 'Nro_Certificado_MP', name: 'lo.Nro_Certificado_MP', className: 'text-nowrap' },
            { data: 'Pedido_de_MP', name: 'lo.Pedido_de_MP', className: 'text-nowrap' },
            { data: 'Prov_Nombre', name: 'lo.Prov_Nombre', className: 'text-nowrap' },
            {
                data: 'Piezas_Fabricadas',
                name: 'lo.Piezas_Fabricadas',
                className: 'text-nowrap',
                render: function (data, type) {
                    return (type === 'display' || type === 'filter') ? formatearNumeroEntero(data) : data;
                }
            },
            {
                data: 'Saldo_Fabricacion',
                name: 'lo.Saldo_Fabricacion',
                className: 'text-nowrap',
                render: function (data, type) {
                    return (type === 'display' || type === 'filter') ? formatearNumeroEntero(data) : data;
                }
            },
            { data: 'Ultima_Fecha_Fabricacion', name: 'lo.Ultima_Fecha_Fabricacion', className: 'text-nowrap' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'columna-acciones' }
        ],
        language: {
            url: "{{ asset('Spanish.json') }}"
        }
    });

    table.on('xhr.dt', function () {
        const json = table.ajax.json();
        const estadosPlanificacion = new Set();
        const estados = new Set();
        const categorias = new Set();
        const maquinas = new Set();
        const familias = new Set();
        const proveedores = new Set();

        if (!json || !json.data) {
            return;
        }

        json.data.forEach(function (item) {
            if (item.Estado_Planificacion) estadosPlanificacion.add(item.Estado_Planificacion);
            if (item.Estado) estados.add(item.Estado);
            if (item.Nombre_Categoria) categorias.add(item.Nombre_Categoria);
            if (item.Nro_Maquina) maquinas.add(item.Nro_Maquina);
            if (item.Familia_Maquinas) familias.add(item.Familia_Maquinas);
            if (item.Prov_Nombre) proveedores.add(item.Prov_Nombre);
        });

        function rellenarSelect(selector, valores) {
            const select = $(selector);
            const actual = select.val();
            select.empty().append('<option value="">Todos</option>');

            valores.forEach(function (valor) {
                select.append(`<option value="${valor}">${valor}</option>`);
            });

            if (actual && select.find(`option[value="${actual}"]`).length) {
                select.val(actual);
            }
        }

        rellenarSelect('#filtro_estado_planificacion', estadosPlanificacion);
        rellenarSelect('#filtro_estado', estados);
        rellenarSelect('#filtro_categoria', categorias);
        rellenarSelect('#filtro_nro_maquina', maquinas);
        rellenarSelect('#filtro_familia_maquina', familias);
        rellenarSelect('#filtro_proveedor', proveedores);
        cargarResumen();
    });

    $('.filtro-texto').on('keyup change', function () {
        table.ajax.reload(null, false);
    });

    $('.filtro-select').on('change', function () {
        table.ajax.reload(null, false);
    });

    $('#clearFilters').on('click', function () {
        $('.filtro-texto').val('');
        $('.filtro-select').val('');
        table.ajax.reload(null, false);
    });

    cargarResumen();
});
</script>
@stop
