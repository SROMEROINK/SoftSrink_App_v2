@extends('adminlte::page')

@section('title', 'Definicion de Materia Prima por Pedido')

@section('content_header')
<x-header-card
    title="Definicion de Materia Prima por Pedido"
    buttonRoute="{{ route('pedido_cliente_mp.createMassive') }}"
    buttonText="Definir Materia Prima"
    deletedRouteUrl="{{ route('pedido_cliente_mp.deleted') }}"
    deletedButtonText="Ver eliminados"
/>
@stop

@section('content')
<div class="container-fluid">
    @include('components.swal-session')

    @if(!empty($legacyMaxNroOf))
        <div class="alert alert-info pedido-mp-index-alert mt-3" role="alert">
            Historico detectado en <strong>listado_of</strong> hasta la OF
            <strong>#{{ number_format($legacyMaxNroOf, 0, ',', '.') }}</strong>.
            @if(!empty($pendingOfCount))
                Quedan <strong>{{ number_format($pendingOfCount, 0, ',', '.') }}</strong> OF nuevas pendientes de definicion MP,
                desde la OF <strong>#{{ number_format($pendingMinNroOf, 0, ',', '.') }}</strong>
                hasta la <strong>#{{ number_format($pendingMaxNroOf, 0, ',', '.') }}</strong>.
            @else
                No hay OF nuevas pendientes de definicion MP fuera del historico legacy.
            @endif
        </div>
    @endif

    <div class="alert alert-secondary pedido-mp-index-alert mt-2" role="alert">
        <strong>Circuito operativo:</strong>
        el <strong>alta</strong> de materia prima se realiza desde la <strong>hoja masiva</strong>,
        la <strong>edicion</strong> se realiza desde la <strong>hoja de edicion</strong>,
        y esta vista queda como panel de <strong>control, filtros y auditoria</strong>.
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-pedido-mp">0</h3>
                    <p>Total de definiciones MP</p>
                </div>
                <div class="icon">
                    <i class="fas fa-vial"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="definidas-pedido-mp">0</h3>
                    <p>MP definidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="eliminadas-pedido-mp">0</h3>
                    <p>Definiciones eliminadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trash"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3" style="gap:8px;">
                <button type="button" class="btn btn-outline-success btn-sm" id="agregar_of_grupo_pedido_mp" disabled>
                    Agregar OF al grupo
                </button>
                <button type="button" class="btn btn-outline-info btn-sm" id="editar_grupo_pedido_mp" disabled>
                    Editar grupo filtrado
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="toggle_pedido_mp_order">
                    Orden Pedido MP: Descendente
                </button>
            </div>
            <div class="table-responsive">
                <table id="tabla_pedido_cliente_mp" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nro OF</th>
                            <th>Producto</th>
                            <th>Categoria</th>
                            <th>Fecha Pedido</th>
                            <th>Cant. Pedido</th>
                            <th>Estado MP</th>
                            <th>Nro Ingreso MP</th>
                            <th>Pedido MP Interno</th>
                            <th>Codigo MP</th>
                            <th>Cant. Barras MP</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_of" class="form-control filtro-texto" placeholder="Filtrar OF"></th>
                            <th><input type="text" id="filtro_producto" class="form-control filtro-texto" placeholder="Filtrar Producto"></th>
                            <th><select id="filtro_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th></th>
                            <th></th>
                            <th><select id="filtro_estado" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_ingreso_mp" class="form-control filtro-texto" placeholder="Filtrar ingreso MP" value="{{ $initialIngresoMpFilter ?? '' }}"></th>
                            <th><input type="text" id="filtro_pedido_material" class="form-control filtro-texto" placeholder="Filtrar pedido MP"></th>
                            <th><select id="filtro_codigo_mp" class="form-control filtro-select"><option value="">Todos</option></select></th>
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
<link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/plugins/datatables-fixedheader/css/fixedHeader.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_mp_index.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datatables-fixedheader/js/dataTables.fixedHeader.min.js') }}"></script>
<script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datatables-fixedheader/js/fixedHeader.bootstrap4.min.js') }}"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
function cargarResumenPedidoMp() {
    $.get("{{ route('pedido_cliente_mp.resumen') }}", function (data) {
        $('#total-pedido-mp').text(Number(data.total || 0).toLocaleString('es-AR'));
        $('#definidas-pedido-mp').text(Number(data.definidas || 0).toLocaleString('es-AR'));
        $('#eliminadas-pedido-mp').text(Number(data.eliminados || 0).toLocaleString('es-AR'));
    });
}

function deletePedidoMp(id) {
    SwalUtils.confirmDelete('La definicion de materia prima sera enviada a eliminados.').then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: `/pedido_cliente_mp/${id}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (response) {
                $('#tabla_pedido_cliente_mp').DataTable().ajax.reload(null, false);
                cargarResumenPedidoMp();
                SwalUtils.deleted(response.message || 'Definicion eliminada correctamente.');
            },
            error: function (xhr) {
                SwalUtils.error(xhr.responseJSON?.message || 'No se pudo eliminar la definicion de MP.');
            }
        });
    });
}

$(document).ready(function () {
    cargarResumenPedidoMp();

    const editGroupBaseUrl = @json(route('pedido_cliente_mp.editGroup'));
    const addOfToGroupBaseUrl = @json(route('pedido_cliente_mp.createMassive'));
    const $editarGrupoPedidoMp = $('#editar_grupo_pedido_mp');
    const $agregarOfGrupoPedidoMp = $('#agregar_of_grupo_pedido_mp');

    const pedidoMpOrderStorageKey = 'pedidoClienteMpOrderDirection';
    let pedidoMpOrderDirection = localStorage.getItem(pedidoMpOrderStorageKey) === 'asc' ? 'asc' : 'desc';

    function updatePedidoMpOrderButton() {
        $('#toggle_pedido_mp_order').text(`Orden Pedido MP: ${pedidoMpOrderDirection === 'desc' ? 'Descendente' : 'Ascendente'}`);
    }
    function updateEditGroupButton() {
        const pedido = String($('#filtro_pedido_material').val() || '').trim();
        $editarGrupoPedidoMp.prop('disabled', pedido === '');
        $agregarOfGrupoPedidoMp.prop('disabled', pedido === '');
    }

    const table = $('#tabla_pedido_cliente_mp').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        autoWidth: false,
        scrollX: true,
        scrollY: '60vh',
        scrollCollapse: true,
        responsive: true,
        fixedHeader: true,
        orderCellsTop: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('pedido_cliente_mp.data') }}",
            data: function (d) {
                d.filtro_of = $('#filtro_of').val();
                d.filtro_producto = $('#filtro_producto').val();
                d.filtro_categoria = $('#filtro_categoria').val();
                d.filtro_estado = $('#filtro_estado').val();
                d.filtro_ingreso_mp = $('#filtro_ingreso_mp').val();
                d.filtro_codigo_mp = $('#filtro_codigo_mp').val();
                d.filtro_pedido_material = $('#filtro_pedido_material').val();
            }
        },
        columns: [
            { data: 'Nro_OF', name: 'p.Nro_OF', orderable: true, searchable: false },
            { data: 'Prod_Codigo', name: 'prod.Prod_Codigo', orderable: false, searchable: false },
            { data: 'Nombre_Categoria', name: 'cat.Nombre_Categoria', orderable: false, searchable: false },
            { data: 'Fecha_del_Pedido', name: 'p.Fecha_del_Pedido', orderable: false, searchable: false },
            { data: 'Cant_Fabricacion', name: 'p.Cant_Fabricacion', orderable: false, searchable: false },
            { data: 'Estado_MP', name: 'Estado_MP', orderable: false, searchable: false },
            { data: 'Nro_Ingreso_MP', name: 'pm.Nro_Ingreso_MP', orderable: false, searchable: false, defaultContent: '' },
            { data: 'Pedido_Material_Nro', name: 'pm.Pedido_Material_Nro', orderable: true, searchable: false, defaultContent: '' },
            { data: 'Codigo_MP', name: 'Codigo_MP', orderable: false, searchable: false, defaultContent: '' },
            { data: 'Cant_Barras_MP', name: 'Cant_Barras_MP', orderable: false, searchable: false },
            {
                data: 'Id_Pedido_MP',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (!data) {
                        return '<div class="acciones-grupo"><span class="text-muted">Pendiente</span></div>';
                    }

                    return `
                        <div class="acciones-grupo">
                            <a href="/pedido_cliente_mp/${data}" class="btn btn-info btn-sm">Ver</a>
                            <a href="/pedido_cliente_mp/${data}/edit-massive" class="btn btn-primary btn-sm">Editar</a>
                            <button type="button" onclick="deletePedidoMp(${data})" class="btn btn-danger btn-sm">Eliminar</button>
                        </div>
                    `;
                }
            }
        ],
        order: [[7, pedidoMpOrderDirection], [0, 'asc']],
        language: {
            url: "{{ asset('Spanish.json') }}"
        }
    });

    table.on('xhr.dt', function () {
        const json = table.ajax.json();
        if (!json || !json.data) return;

        const categorias = new Set();
        const codigos = new Set();
        const estados = new Map();

        json.data.forEach(function (item) {
            if (item.Nombre_Categoria) categorias.add(item.Nombre_Categoria);
            if (item.Codigo_MP) codigos.add(item.Codigo_MP);
            if (item.Estado_Plani_Id && item.Estado_MP) estados.set(String(item.Estado_Plani_Id), item.Estado_MP);
        });

        const categoriaActual = $('#filtro_categoria').val();
        const codigoActual = $('#filtro_codigo_mp').val();
        const estadoActual = $('#filtro_estado').val();

        $('#filtro_categoria').empty().append('<option value="">Todos</option>');
        categorias.forEach((item) => $('#filtro_categoria').append(`<option value="${item}">${item}</option>`));

        $('#filtro_codigo_mp').empty().append('<option value="">Todos</option>');
        Array.from(codigos).sort((a, b) => a.localeCompare(b)).forEach((item) => $('#filtro_codigo_mp').append(`<option value="${item}">${item}</option>`));

        $('#filtro_estado').empty().append('<option value="">Todos</option>');
        estados.forEach((nombre, id) => $('#filtro_estado').append(`<option value="${id}">${nombre}</option>`));

        if (categoriaActual) $('#filtro_categoria').val(categoriaActual);
        if (codigoActual) $('#filtro_codigo_mp').val(codigoActual);
        if (estadoActual) $('#filtro_estado').val(estadoActual);
        updateEditGroupButton();
    });

    $('#filtro_of, #filtro_producto, #filtro_ingreso_mp, #filtro_pedido_material').on('keyup change', function () {
        updateEditGroupButton();
        table.ajax.reload(null, false);
    });

    $('#filtro_categoria, #filtro_estado, #filtro_codigo_mp').on('change', function () {
        table.ajax.reload(null, false);
    });

    updatePedidoMpOrderButton();
    updateEditGroupButton();

    $('#toggle_pedido_mp_order').on('click', function () {
        pedidoMpOrderDirection = pedidoMpOrderDirection === 'desc' ? 'asc' : 'desc';
        localStorage.setItem(pedidoMpOrderStorageKey, pedidoMpOrderDirection);
        updatePedidoMpOrderButton();
        updateEditGroupButton();
        table.order([[7, pedidoMpOrderDirection], [0, 'asc']]).draw();
    });

    $editarGrupoPedidoMp.on('click', function () {
        const pedido = String($('#filtro_pedido_material').val() || '').trim();
        if (!pedido) {
            return;
        }

        window.location.href = `${editGroupBaseUrl}?pedido_material=${encodeURIComponent(pedido)}`;
    });

    $agregarOfGrupoPedidoMp.on('click', function () {
        const pedido = String($('#filtro_pedido_material').val() || '').trim();
        if (!pedido) {
            return;
        }

        window.location.href = `${addOfToGroupBaseUrl}?pedido_material=${encodeURIComponent(pedido)}`;
    });

    $(window).on('resize', function () {
        table.columns.adjust();
        if (table.fixedHeader && typeof table.fixedHeader.adjust === 'function') {
            table.fixedHeader.adjust();
        }
    });

    $('#clearFilters').on('click', function () {
        $('.filtro-texto').val('');
        $('.filtro-select').val('');
        updateEditGroupButton();
        table.ajax.reload(null, false);
    });
});
</script>
@stop



