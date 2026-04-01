@extends('adminlte::page')

@section('title', 'Listado de Entregas de Productos')

@section('content_header')
<x-header-card
    title="Listado de Entregas de Productos"
    buttonRoute="{{ route('entregas_productos.create') }}"
    buttonText="Registrar entrega"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="alert alert-info mt-3">
        <strong>Listado de entregas:</strong> esta vista registra todas las entregas finales al cliente por OF y parcial, usando una vista consolidada optimizada de entregas para producto, maquina y MP.
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-entregas">0</h3>
                    <p>Entregas registradas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="total-piezas">0</h3>
                    <p>Piezas entregadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="total-remitos">0</h3>
                    <p>Remitos emitidos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla_entregas_productos" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nro OF</th>
                            <th>Producto</th>
                            <th>Descripcion</th>
                            <th>Categoria</th>
                            <th>Nro Maquina</th>
                            <th>Familia Maquina</th>
                            <th>Nro Ingreso MP</th>
                            <th>Codigo MP</th>
                            <th>Certificado MP</th>
                            <th>Proveedor</th>
                            <th>Nro Parcial</th>
                            <th>Cant. Piezas</th>
                            <th>Nro Remito</th>
                            <th>Fecha Entrega</th>
                            <th>Inspector</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_nro_of" class="form-control filtro-texto" placeholder="Filtrar OF"></th>
                            <th><input type="text" id="filtro_producto" class="form-control filtro-texto" placeholder="Filtrar Producto"></th>
                            <th><input type="text" id="filtro_descripcion" class="form-control filtro-texto" placeholder="Filtrar Descripcion"></th>
                            <th><select id="filtro_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_nro_maquina" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_familia_maquina" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_nro_ingreso_mp" class="form-control filtro-texto" placeholder="Filtrar Ingreso"></th>
                            <th><input type="text" id="filtro_codigo_mp" class="form-control filtro-texto" placeholder="Filtrar Codigo"></th>
                            <th><input type="text" id="filtro_certificado_mp" class="form-control filtro-texto" placeholder="Filtrar Certificado"></th>
                            <th><select id="filtro_proveedor" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_nro_parcial" class="form-control filtro-texto" placeholder="Filtrar Parcial"></th>
                            <th><input type="text" id="filtro_cant_piezas" class="form-control filtro-texto" placeholder="Filtrar Cantidad"></th>
                            <th><input type="text" id="filtro_nro_remito" class="form-control filtro-texto" placeholder="Filtrar Remito"></th>
                            <th><input type="date" id="filtro_fecha_entrega" class="form-control filtro-texto"></th>
                            <th><input type="text" id="filtro_inspector" class="form-control filtro-texto" placeholder="Filtrar Inspector"></th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/entregas_productos_index.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
function formatearNumeroEntero(valor) {
    return Number(valor || 0).toLocaleString('es-AR');
}

function filtrosActuales() {
    return {
        filtro_nro_of: $('#filtro_nro_of').val(),
        filtro_producto: $('#filtro_producto').val(),
        filtro_descripcion: $('#filtro_descripcion').val(),
        filtro_categoria: $('#filtro_categoria').val(),
        filtro_nro_maquina: $('#filtro_nro_maquina').val(),
        filtro_familia_maquina: $('#filtro_familia_maquina').val(),
        filtro_nro_ingreso_mp: $('#filtro_nro_ingreso_mp').val(),
        filtro_codigo_mp: $('#filtro_codigo_mp').val(),
        filtro_certificado_mp: $('#filtro_certificado_mp').val(),
        filtro_proveedor: $('#filtro_proveedor').val(),
        filtro_nro_parcial: $('#filtro_nro_parcial').val(),
        filtro_cant_piezas: $('#filtro_cant_piezas').val(),
        filtro_nro_remito: $('#filtro_nro_remito').val(),
        filtro_fecha_entrega: $('#filtro_fecha_entrega').val(),
        filtro_inspector: $('#filtro_inspector').val()
    };
}

function cargarResumenEntregas() {
    $.get("{{ route('entregas_productos.resumen') }}", filtrosActuales(), function (data) {
        $('#total-entregas').text(formatearNumeroEntero(data.total_entregas));
        $('#total-piezas').text(formatearNumeroEntero(data.total_piezas));
        $('#total-remitos').text(formatearNumeroEntero(data.total_remitos));
    });
}

function cargarFiltrosEntregas() {
    $.get("{{ route('entregas_productos.filters') }}", filtrosActuales(), function (data) {
        [
            ['#filtro_categoria', data.categorias || []],
            ['#filtro_nro_maquina', data.maquinas || []],
            ['#filtro_familia_maquina', data.familias || []],
            ['#filtro_proveedor', data.proveedores || []]
        ].forEach(function ([selector, values]) {
            const select = $(selector);
            const actual = select.val();
            select.empty().append('<option value="">Todos</option>');
            values.forEach(function (value) {
                if (value !== null && value !== '') {
                    select.append(`<option value="${value}">${value}</option>`);
                }
            });
            if (actual && select.find(`option[value="${actual}"]`).length) {
                select.val(actual);
            }
        });
    });
}

let filtroTimer = null;

function recargarTablaEntregas(table, options = {}) {
    const resetPaging = options.resetPaging ?? true;
    const refreshFilters = options.refreshFilters ?? false;

    if (resetPaging) {
        table.page('first');
    }

    table.ajax.reload(null, !resetPaging);
    cargarResumenEntregas();

    if (refreshFilters) {
        cargarFiltrosEntregas();
    }
}

function deleteEntrega(id) {
    SwalUtils.confirmDelete('La entrega sera enviada a eliminados del sistema.').then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: `/entregas_productos/${id}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (response) {
                $('#tabla_entregas_productos').DataTable().ajax.reload(null, false);
                cargarResumenEntregas();
                SwalUtils.deleted(response.message || 'Entrega eliminada correctamente.');
            },
            error: function (xhr) {
                SwalUtils.error(xhr.responseJSON?.message || 'No se pudo eliminar la entrega.');
            }
        });
    });
}

$(document).ready(function () {
    cargarResumenEntregas();
    cargarFiltrosEntregas();

    const table = $('#tabla_entregas_productos').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        autoWidth: false,
        scrollX: true,
        scrollY: '60vh',
        scrollCollapse: true,
        responsive: false,
        orderCellsTop: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('entregas_productos.data') }}",
            data: function (d) {
                Object.assign(d, filtrosActuales());
            }
        },
        columns: [
            { data: 'Nro_OF', name: 'lep.Nro_OF' },
            { data: 'Prod_Codigo', name: 'lep.Prod_Codigo', orderable: false, searchable: false },
            { data: 'Prod_Descripcion', name: 'lep.Prod_Descripcion', orderable: false, searchable: false },
            { data: 'Nombre_Categoria', name: 'lep.Nombre_Categoria', orderable: false, searchable: false },
            { data: 'Nro_Maquina', name: 'lep.Nro_Maquina', orderable: false, searchable: false },
            { data: 'Familia_Maquinas', name: 'lep.Familia_Maquinas', orderable: false, searchable: false },
            { data: 'Nro_Ingreso_MP', name: 'lep.Nro_Ingreso_MP', orderable: false, searchable: false },
            { data: 'Codigo_MP', name: 'lep.Codigo_MP', orderable: false, searchable: false },
            { data: 'Nro_Certificado_MP', name: 'lep.Nro_Certificado_MP', orderable: false, searchable: false },
            { data: 'Prov_Nombre', name: 'lep.Prov_Nombre', orderable: false, searchable: false },
            { data: 'Nro_Parcial_Calidad', name: 'lep.Nro_Parcial_Calidad' },
            {
                data: 'Cant_Piezas_Entregadas',
                name: 'lep.Cant_Piezas_Entregadas',
                render: function (data, type) {
                    return (type === 'display' || type === 'filter') ? formatearNumeroEntero(data) : data;
                }
            },
            { data: 'Nro_Remito_Entrega_Calidad', name: 'lep.Nro_Remito_Entrega_Calidad' },
            { data: 'Fecha_Entrega_Calidad', name: 'lep.Fecha_Entrega_Calidad' },
            { data: 'Inspector_Calidad', name: 'lep.Inspector_Calidad' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        order: [[13, 'desc'], [12, 'desc'], [0, 'desc']],
        language: {
            url: "{{ asset('Spanish.json') }}"
        }
    });

    $(document).on('click', '.trigger-delete', function () {
        deleteEntrega($(this).data('id'));
    });

    $('.filtro-texto').on('keyup change', function () {
        clearTimeout(filtroTimer);
        filtroTimer = setTimeout(function () {
            recargarTablaEntregas(table, { resetPaging: true, refreshFilters: false });
        }, 300);
    });

    $('.filtro-select').on('change', function () {
        recargarTablaEntregas(table, { resetPaging: true, refreshFilters: true });
    });

    $(document).on('click', '#clearFilters', function (e) {
        e.preventDefault();
        clearTimeout(filtroTimer);
        $('.filtro-texto').val('');
        $('.filtro-select').val('');
        table.search('');
        table.order([[13, 'desc'], [12, 'desc'], [0, 'desc']]);
        recargarTablaEntregas(table, { resetPaging: true, refreshFilters: true });
    });
});
</script>
@stop



