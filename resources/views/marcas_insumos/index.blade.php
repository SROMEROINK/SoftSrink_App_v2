{{-- resources\views\marcas_insumos\index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Insumos - Marcas')

@section('content_header')
<x-header-card 
    title="Marcas de Insumos" 
    quantityTitle="Total de Marcas:" 
    quantity="{{ $totalMarcas }}" 
    buttonRoute="{{ route('marcas_insumos.create') }}" 
    buttonText="Crear Marca" 
    deletedRouteUrl="{{ route('marcas_insumos.deleted') }}"
    deletedButtonText="Ver Marcas Eliminadas" 
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table id="marcas_insumos" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre de Marca</th>
                            <th>Proveedor</th>
                            <th>Estado</th>
                            <th>Fecha de Creación</th>
                            <th>Última Actualización</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_nombre_marca" class="form-control filtro-texto" placeholder="Buscar Marca"></th>
                            <th><select id="filtro_proveedor" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_estado" class="form-control filtro-select">
                                <option value="">Todos</option>
                                <option value="1">Habilitado</option>
                                <option value="0">Deshabilitado</option>
                            </select></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/marcas_insumos_index.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Función para eliminar una marca
function deleteMarca(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarlo'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/marcas_insumos/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: 'La marca de insumo ha sido eliminada.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#marcas_insumos').DataTable().ajax.reload(null, false);
                },
                error: function() {
                    Swal.fire('¡Error!', 'Ha ocurrido un error al intentar eliminar.', 'error');
                }
            });
        }
    });
}

$(document).ready(function () {
    var filtersLoaded = false;

    // Inicialización de la tabla DataTables
    var table = $('#marcas_insumos').DataTable({
        processing: true,
        serverSide: true,
        
        ajax: {
            url: "{{ route('marcas_insumos.data') }}",
            type: 'GET',
            data: function (d) {
                d.filtro_nombre_marca = $('#filtro_nombre_marca').val();
                d.filtro_proveedor = $('#filtro_proveedor').val();
                d.filtro_estado = $('#filtro_estado').val();
            }
        },
        columns: [
            { data: 'Nombre_marca' },
            { data: 'Proveedor' },
            { data: 'reg_Status', render: function(data) { return data == 1 ? 'Habilitado' : 'Deshabilitado'; } },
            { 
                data: 'created_at', 
                render: function(data) {
                    if (data) {
                        const date = new Date(data);
                        return date.toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    }
                    return ''; // No mostrar nada si no hay fecha
                }
            },
            { 
                data: 'updated_at', 
                render: function(data) {
                    if (data) {
                        const date = new Date(data);
                        return date.toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    }
                    return ''; // No mostrar nada si no hay fecha
                }
            },
            {
                data: 'Id_Marca',
                render: function (data) {
                    return `
                        <a href="/marcas_insumos/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                        <button onclick="deleteMarca(${data})" class="btn btn-danger btn-sm">Eliminar</button>
                    `;
                },
                orderable: false,
                searchable: false
            }
        ],
        scrollX: true,
        scrollY: '60vh',
        scrollCollapse: true,
        searching: false,
        paging: true,
        fixedHeader: true,
        responsive: true,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            lengthMenu: "Mostrar _MENU_ registros por página",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando página _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search: "Buscar:",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        },
    
    // Dibujar los filtros únicos en la tabla
        drawCallback: function () {
            if (!filtersLoaded) {
                loadUniqueFilters(); // Cargar los filtros únicos solo una vez
                filtersLoaded = true;
            }
        }
    });

    // Función para cargar los filtros únicos desde el servidor
    function loadUniqueFilters() {
        $.ajax({
            url: "{{ route('marcas_insumos.getUniqueFilters') }}",
            type: 'GET',
            success: function (data) {
                fillSelect('#filtro_proveedor', data.proveedores);
            }
        });
    }

    // Función para rellenar los selectores con datos únicos
    function fillSelect(selector, data) {
        var select = $(selector);
        select.empty();
        select.append('<option value="">Todos</option>');
        data.forEach(function (value) {
            select.append('<option value="' + value + '">' + value + '</option>');
        });
    }

    // Recargar la tabla al cambiar los selectores o campos de texto
    $('.filtro-select, .filtro-texto').on('change keyup', function () {
        table.ajax.reload(null, false);
    });

    // Limpiar los filtros al hacer clic en el botón
    $('#clearFilters').click(function () {
        $('.filtro-select').val('');
        $('.filtro-texto').val('');
        table.ajax.reload();
    });

    // Actualizar el contador de marcas base
    table.on('xhr', function () {
        var json = table.ajax.json();
        var totalMarcas = json.recordsTotal;
        $('#totalCantMarcas').text(totalMarcas);
    });
});
</script>
@stop
