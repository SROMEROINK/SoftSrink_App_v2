@extends('adminlte::page')

@section('title', 'Programacion de la Produccion - Pedido del Cliente')

@section('content_header')
<x-header-card
    title="Programacion de la Produccion - Pedido del Cliente"
    buttonRoute="{{ route('pedido_cliente.create') }}"
    buttonText="Crear Pedido"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-pedidos">0</h3>
                    <p>Total de pedidos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="total-piezas">0</h3>
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
                    <h3 id="eliminados-pedidos">0</h3>
                    <p>Pedidos eliminados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trash"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla_pedido_cliente" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nro OF</th>
                            <th>Producto</th>
                            <th>Descripcion</th>
                            <th>Categoria</th>
                            <th>Fecha del Pedido</th>
                            <th>Cant. Fabricacion</th>
                            <th>Estado Pedido</th>
                            <th>Estado MP</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_nro_of" class="form-control filtro-texto" placeholder="Filtrar OF"></th>
                            <th><input type="text" id="filtro_producto" class="form-control filtro-texto" placeholder="Filtrar Producto"></th>
                            <th><input type="text" id="filtro_descripcion" class="form-control filtro-texto" placeholder="Filtrar Descripcion"></th>
                            <th><select id="filtro_nombre_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="date" id="filtro_fecha_pedido" class="form-control filtro-texto"></th>
                            <th><input type="text" id="filtro_cant_fabricacion" class="form-control filtro-texto" placeholder="Filtrar Cantidad"></th>
                            <th>
                                <select id="filtro_estado_pedido" class="form-control filtro-select"><option value="">Todos</option></select>
                            </th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_index.css') }}">
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
    const numero = Number(valor || 0);
    return numero.toLocaleString('es-AR');
}

function cargarResumenPedidos() {
    $.get("{{ route('pedido_cliente.resumen') }}", function (data) {
        $('#total-pedidos').text(formatearNumeroEntero(data.total));
        $('#total-piezas').text(formatearNumeroEntero(data.piezas));
        $('#eliminados-pedidos').text(formatearNumeroEntero(data.eliminados));
    });
}

function deletePedido(id) {
    SwalUtils.confirmDelete('El pedido sera enviado a eliminados si no tiene fabricacion asociada.').then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/pedido_cliente/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#tabla_pedido_cliente').DataTable().ajax.reload(null, false);
                    cargarResumenPedidos();
                    SwalUtils.deleted(response.message || 'Pedido eliminado correctamente.');
                },
                error: function (xhr) {
                    SwalUtils.error(xhr.responseJSON?.message || 'No se pudo eliminar el pedido.');
                }
            });
        }
    });
}

$(document).ready(function () {
    cargarResumenPedidos();

    const table = $('#tabla_pedido_cliente').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        autoWidth: false,
        scrollX: true,
        responsive: false,
        orderCellsTop: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('pedido_cliente.data') }}",
            data: function (d) {
                d.filtro_nro_of = $('#filtro_nro_of').val();
                d.filtro_producto = $('#filtro_producto').val();
                d.filtro_descripcion = $('#filtro_descripcion').val();
                d.filtro_nombre_categoria = $('#filtro_nombre_categoria').val();
                d.filtro_fecha_pedido = $('#filtro_fecha_pedido').val();
                d.filtro_cant_fabricacion = $('#filtro_cant_fabricacion').val();
                d.filtro_estado_pedido = $('#filtro_estado_pedido').val();
            }
        },
        columns: [
            { data: 'Nro_OF', name: 'Nro_OF' },
            { data: 'Producto_Nombre', name: 'Producto_Nombre', orderable: false, searchable: false },
            { data: 'Descripcion', name: 'Descripcion', orderable: false, searchable: false },
            { data: 'Nombre_Categoria', name: 'Nombre_Categoria', orderable: false, searchable: false },
            { data: 'Fecha_del_Pedido', name: 'Fecha_del_Pedido' },
            {
                data: 'Cant_Fabricacion',
                name: 'Cant_Fabricacion',
                render: function (data, type) {
                    if (type === 'display' || type === 'filter') {
                        return formatearNumeroEntero(data);
                    }

                    return data;
                }
            },
            { data: 'Estado_Pedido', name: 'Estado_Pedido', orderable: false, searchable: false },
            { data: 'Estado_MP', name: 'Estado_MP', orderable: false, searchable: false },
            {
                data: 'Id_OF',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    const mpAction = row.Id_Pedido_MP
                        ? `<a href="/pedido_cliente_mp/${row.Id_Pedido_MP}/edit-massive" class="btn btn-success btn-sm">Cargar MP</a>`
                        : `<a href="/pedido_cliente_mp/create-massive?of=${data}" class="btn btn-success btn-sm">Cargar MP</a>`;

                    return `
                        ${mpAction}
                        <a href="/pedido_cliente/${data}" class="btn btn-info btn-sm">Ver</a>
                        <a href="/pedido_cliente/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                        <button type="button" onclick="deletePedido(${data})" class="btn btn-danger btn-sm">Eliminar</button>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            url: "{{ asset('Spanish.json') }}"
        }
    });

    table.on('xhr.dt', function () {
        const json = table.ajax.json();
        const categorias = new Set();
        const estadosPedido = new Map();

        if (!json || !json.data) {
            return;
        }

        json.data.forEach(function (item) {
            if (item.Nombre_Categoria) {
                categorias.add(item.Nombre_Categoria);
            }
            if (item.Estado_Plani_Id && item.Estado_Pedido) {
                estadosPedido.set(String(item.Estado_Plani_Id), item.Estado_Pedido);
            }
        });

        const selectCategoria = $('#filtro_nombre_categoria');
        const selectEstadoPedido = $('#filtro_estado_pedido');
        const actual = selectCategoria.val();
        const actualEstadoPedido = selectEstadoPedido.val();

        selectCategoria.empty().append('<option value="">Todos</option>');
        categorias.forEach(function (categoria) {
            selectCategoria.append(`<option value="${categoria}">${categoria}</option>`);
        });

        selectEstadoPedido.empty().append('<option value="">Todos</option>');
        estadosPedido.forEach(function (nombre, id) {
            selectEstadoPedido.append(`<option value="${id}">${nombre}</option>`);
        });

        if (actual && selectCategoria.find(`option[value="${actual}"]`).length) {
            selectCategoria.val(actual);
        }
        if (actualEstadoPedido && selectEstadoPedido.find(`option[value="${actualEstadoPedido}"]`).length) {
            selectEstadoPedido.val(actualEstadoPedido);
        }
    });

    $('#filtro_nro_of, #filtro_producto, #filtro_descripcion, #filtro_fecha_pedido, #filtro_cant_fabricacion').on('keyup change', function () {
        table.ajax.reload(null, false);
    });

    $('#filtro_nombre_categoria, #filtro_estado_pedido').on('change', function () {
        table.ajax.reload(null, false);
    });

    $('#clearFilters').on('click', function () {
        $('.filtro-texto').val('');
        $('.filtro-select').val('');
        table.ajax.reload(null, false);
    });
});
</script>
@stop
