@extends('adminlte::page')

@section('title', 'Listado de Diametros')

@section('content_header')
<x-header-card
    title="Listado de Diametros"
    buttonRoute="{{ route('mp_diametro.create') }}"
    buttonText="Crear Diametro"
    deletedRouteUrl="{{ route('mp_diametro.deleted') }}"
    deletedButtonText="Ver Diametros Eliminados"
/>
@stop

@section('content')
    @include('components.swal-session')
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-diametros">0</h3>
                    <p>Total de diametros</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ruler-horizontal"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activos-diametros">0</h3>
                    <p>Diametros activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="eliminados-diametros">0</h3>
                    <p>Diametros eliminados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trash-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="diametros-table" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Valor Diametro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        <tr>
                            <th><input type="text" id="filtro_diametro" placeholder="Filtrar por Diametro" class="form-control filtro-texto"></th>
                            <th>
                                <select id="filtro_estado" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_index.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_diametro_index.css') }}">
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>

<script>
function cargarResumen() {
    $.get("{{ route('mp_diametro.resumen') }}", function (data) {
        $('#total-diametros').text(data.total);
        $('#activos-diametros').text(data.activos);
        $('#eliminados-diametros').text(data.eliminados);
    });
}

function deleteDiametro(id) {
    SwalUtils.confirmDelete('El diametro sera enviado a eliminados.').then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/mp_diametro/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function () {
                    $('#diametros-table').DataTable().ajax.reload(null, false);
                    cargarResumen();
                    SwalUtils.deleted('El diametro ha sido eliminado.');
                },
                error: function () {
                    SwalUtils.error('Ha ocurrido un error al intentar eliminar.');
                }
            });
        }
    });
}

$(document).ready(function() {
    cargarResumen();

    var table = $('#diametros-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        autoWidth: false,
        scrollX: true,
        scrollY: '60vh',
        scrollCollapse: true,
        responsive: false,
        orderCellsTop: true,
        searching: false,
        paging: true,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        ajax: {
            url: "{{ route('mp_diametro.data') }}",
            data: function (d) {
                d.filtro_diametro = $('#filtro_diametro').val();
                d.filtro_estado = $('#filtro_estado').val();
            }
        },
        columns: [
            { data: 'Valor_Diametro', name: 'Valor_Diametro' },
            {
                data: 'reg_Status',
                name: 'reg_Status',
                render: function (data) {
                    return data === 1 ? 'Activo' : 'Inactivo';
                }
            },
            {
                data: 'Id_Diametro',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `
                        <a href="/mp_diametro/${data}" class="btn btn-info btn-sm">Ver</a>
                        <a href="/mp_diametro/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                        <button onclick="deleteDiametro(${data})" class="btn btn-danger btn-sm">Eliminar</button>
                    `;
                }
            }
        ],
        language: {
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search: "Buscar:",
            paginate: {
                first: "Primero",
                last: "Ultimo",
                next: "Siguiente",
                previous: "Anterior"
            },
            processing: "Procesando..."
        }
    });

    $('#filtro_diametro, #filtro_estado').on('keyup change', function() {
        table.ajax.reload(null, false);
    });

    $('#clearFilters').click(function() {
        $('.filtro-select, .filtro-texto').val('');
        table.ajax.reload(null, false);
    });
});
</script>
@stop

