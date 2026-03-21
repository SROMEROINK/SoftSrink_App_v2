@extends('adminlte::page')

@section('title', 'Listado de Materias Base')

@section('content_header')
<x-header-card
    title="Listado de Materias Base"
    buttonRoute="{{ route('mp_materia_prima.create') }}"
    buttonText="Crear Materia Base"
    deletedRouteUrl="{{ route('mp_materias_primas.deleted') }}"
    deletedButtonText="Ver Materias Eliminadas"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-materias-base">0</h3>
                    <p>Total de materias base</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activas-materias-base">0</h3>
                    <p>Materias base activas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="eliminadas-materias-base">0</h3>
                    <p>Materias base eliminadas</p>
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
                <table id="materias-table" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nombre de Materia</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        <tr>
                            <th><input type="text" id="filtro_nombre" placeholder="Filtrar por Nombre de Materia" class="form-control filtro-texto"></th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_base_index.css') }}">
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
    $.get("{{ route('mp_materia_prima.resumen') }}", function (data) {
        $('#total-materias-base').text(data.total);
        $('#activas-materias-base').text(data.activos);
        $('#eliminadas-materias-base').text(data.eliminados);
    });
}

function deleteMateria(id) {
    SwalUtils.confirmDelete('La materia base sera enviada a eliminados.').then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/mp_materia_prima/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function () {
                    $('#materias-table').DataTable().ajax.reload(null, false);
                    cargarResumen();
                    SwalUtils.deleted('La materia base ha sido eliminada.');
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

    var table = $('#materias-table').DataTable({
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
            url: "{{ route('mp_materia_prima.data') }}",
            data: function (d) {
                d.filtro_nombre = $('#filtro_nombre').val();
                d.filtro_estado = $('#filtro_estado').val();
            }
        },
        columns: [
            { data: 'Nombre_Materia', name: 'Nombre_Materia' },
            {
                data: 'reg_Status',
                name: 'reg_Status',
                render: function (data) {
                    return data === 1 ? 'Activo' : 'Inactivo';
                }
            },
            {
                data: 'Id_Materia_Prima',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `
                        <a href="/mp_materia_prima/${data}" class="btn btn-info btn-sm">Ver</a>
                        <a href="/mp_materia_prima/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                        <button onclick="deleteMateria(${data})" class="btn btn-danger btn-sm">Eliminar</button>
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

    $('#filtro_nombre, #filtro_estado').on('keyup change', function() {
        table.ajax.reload(null, false);
    });

    $('#clearFilters').click(function() {
        $('.filtro-select, .filtro-texto').val('');
        table.ajax.reload(null, false);
    });
});
</script>
@stop
