@extends('adminlte::page')

@section('title', 'Modulo Multifila Reutilizable')

@section('content_header')
<x-header-card
    title="Modulo Multifila Reutilizable"
    quantityTitle="Total de registros:"
    quantity="{{ $totalRegistros }}"
    buttonRoute="{{ route('modulo.create') }}"
    buttonText="Crear Registro"
    deletedRouteUrl="{{ route('modulo.deleted') }}"
    deletedButtonText="Ver Eliminados"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-registros">-</h3>
                    <p>Total de Registros</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activos-registros">-</h3>
                    <p>Registros Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="eliminados-registros">-</h3>
                    <p>Registros Eliminados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trash-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tabla_modulo_multifila" class="table table-striped table-bordered w-100">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Campo 1</th>
                    <th>Campo 2</th>
                    <th>Campo 3</th>
                    <th>Estado</th>
                    <th>created_at</th>
                    <th>updated_at</th>
                    <th>Acciones</th>
                </tr>
                <tr class="filter-row">
                    <th></th>
                    <th><input type="text" id="filtro_campo_1" class="form-control filtro-texto" placeholder="Filtrar Campo 1"></th>
                    <th><input type="text" id="filtro_campo_2" class="form-control filtro-texto" placeholder="Filtrar Campo 2"></th>
                    <th><input type="text" id="filtro_campo_3" class="form-control filtro-texto" placeholder="Filtrar Campo 3"></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/datatables.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_multifila_reutilizable/modulo_index.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>

<script>
$.get("{{ route('modulo.resumen') }}", function (data) {
    $('#total-registros').text(data.total);
    $('#activos-registros').text(data.activos);
    $('#eliminados-registros').text(data.eliminados);
});

function deleteRegistro(id) {
    SwalUtils.confirmDelete('El registro sera enviado a eliminados.').then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/modulo/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    SwalUtils.deleted(response.message || 'Registro eliminado correctamente.');
                    $('#tabla_modulo_multifila').DataTable().ajax.reload(null, false);
                },
                error: function (xhr) {
                    SwalUtils.error(xhr.responseJSON?.message || 'No se pudo eliminar.');
                }
            });
        }
    });
}

$(document).ready(function () {
    const table = $('#tabla_modulo_multifila').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('modulo.data') }}",
            type: 'GET',
            data: function (d) {
                d.filtro_campo_1 = $('#filtro_campo_1').val();
                d.filtro_campo_2 = $('#filtro_campo_2').val();
                d.filtro_campo_3 = $('#filtro_campo_3').val();
            }
        },
        columns: [
            { data: 'Id_Modulo' },
            { data: 'Campo_1' },
            { data: 'Campo_2' },
            { data: 'Campo_3' },
            { data: 'Estado_Texto' },
            { data: 'created_at' },
            { data: 'updated_at' },
            {
                data: 'Id_Modulo',
                render: function (data) {
                    return `
                        <a href="/modulo/${data}" class="btn btn-info btn-sm">Ver</a>
                        <a href="/modulo/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                        <button onclick="deleteRegistro(${data})" class="btn btn-danger btn-sm">Eliminar</button>
                    `;
                },
                orderable: false,
                searchable: false
            }
        ],
        searching: false,
        pageLength: 10,
        language: {
            lengthMenu: "Mostrar _MENU_ registros por pagina",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando pagina _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                first: "Primero",
                last: "Ultimo",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });

    $('.filtro-texto').on('keyup change', function () {
        table.ajax.reload(null, false);
    });
});
</script>
@stop
