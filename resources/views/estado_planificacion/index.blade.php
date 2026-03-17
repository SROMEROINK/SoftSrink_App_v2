@extends('adminlte::page')

@section('title', 'Estados de Planificación')

@section('content_header')
<x-header-card 
    title="Estados de Planificación"
    quantityTitle="Cantidad de estados:"
    quantity="{{ $totalEstados }}"
    buttonRoute="{{ route('estado_planificacion.create') }}"
    buttonText="Crear registro"
    deletedRouteUrl="{{ route('estado_planificacion.deleted') }}"
    deletedButtonText="Ver eliminados"
/>
@stop

@section('content')
<div class="row mt-3">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="total-estados">0</h3>
                <p>Total de estados</p>
            </div>
            <div class="icon">
                <i class="fas fa-list"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="activos-estados">0</h3>
                <p>Estados activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 id="eliminados-estados">0</h3>
                <p>Estados eliminados</p>
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
            <table id="tabla_estados_planificacion" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Estado</th>
                        <th>Status</th>
                        <th>Creado</th>
                        <th>Actualizado</th>
                        <th>Acciones</th>
                    </tr>
                    <tr class="filter-row">
                        <th>
                            <input type="text" id="filtro_id" class="form-control filtro-texto" placeholder="Filtrar ID">
                        </th>
                        <th>
                            <input type="text" id="filtro_nombre" class="form-control filtro-texto" placeholder="Filtrar Estado">
                        </th>
                        <th>
                            <select id="filtro_status" class="form-control filtro-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
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
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/datatables.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/summary-boxes.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/estado_planificacion_index.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function cargarResumenEstados() {
    $.get("{{ route('estado_planificacion.resumen') }}", function (data) {
        $('#total-estados').text(data.total);
        $('#activos-estados').text(data.activos);
        $('#eliminados-estados').text(data.eliminados);
    });
}

function cargarFiltrosEstado() {
    $.ajax({
        url: "{{ route('estado_planificacion.filters') }}",
        type: "GET",
        success: function (data) {
            const selectStatus = $('#filtro_status');
            selectStatus.empty().append('<option value="">Todos</option>');

            data.status.forEach(function(item) {
                selectStatus.append(`<option value="${item}">${item}</option>`);
            });
        }
    });
}

function deleteEstado(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¡No podrás revertir esto!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarlo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/estado_planificacion/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#tabla_estados_planificacion').DataTable().ajax.reload(null, false);
                    cargarResumenEstados();

                    Swal.fire(
                        '¡Eliminado!',
                        response.message || 'El estado de planificación ha sido eliminado.',
                        'success'
                    );
                },
                error: function(xhr) {
                    Swal.fire(
                        '¡Error!',
                        xhr.responseJSON?.message || xhr.responseJSON?.error || 'Ha ocurrido un error al intentar eliminar.',
                        'error'
                    );
                }
            });
        }
    });
}

$(document).ready(function () {
    cargarResumenEstados();
    cargarFiltrosEstado();

    const table = $('#tabla_estados_planificacion').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        autoWidth: false,
        scrollX: true,
        responsive: false,
        orderCellsTop: true,
        pageLength: 10,
        ajax: {
            url: "{{ route('estado_planificacion.data') }}",
            data: function (d) {
                d.filtro_id = $('#filtro_id').val();
                d.filtro_nombre = $('#filtro_nombre').val();
                d.filtro_status = $('#filtro_status').val();
            }
        },
        columns: [
            { data: 'Estado_Plani_Id', name: 'Estado_Plani_Id' },
            { data: 'Nombre_Estado', name: 'Nombre_Estado' },
            { data: 'Estado_Texto', name: 'Estado_Texto', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' },
            {
                data: 'Estado_Plani_Id',
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `
                        <a href="/estado_planificacion/${data}" class="btn btn-info btn-sm">Ver</a>
                        <a href="/estado_planificacion/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                        <button type="button" onclick="deleteEstado(${data})" class="btn btn-danger btn-sm">Eliminar</button>
                    `;
                }
            }
        ],
        order: [[0, 'asc']],
        language: {
            url: "{{ asset('Spanish.json') }}"
        }
    });

    $('#filtro_id, #filtro_nombre, #filtro_status').on('keyup change', function () {
        table.ajax.reload(null, false);
    });

    $('#clearFilters').click(function () {
        $('.filtro-texto').val('');
        $('.filtro-select').val('');
        table.ajax.reload(null, false);
    });

    $(window).on('resize', function () {
        table.columns.adjust();
    });
});
</script>
@stop