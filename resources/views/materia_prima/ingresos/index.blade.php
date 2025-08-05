{{-- resources/views/materia_prima/ingresos/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Materia Prima - Ingresos')

@section('content_header')
<x-header-card 
    title="Ingresos de Materia Prima" 
    quantityTitle="Total de Unidades Ingresadas:" 
    quantity="{{ $totalIngresos }}" 
    buttonRoute="{{ route('mp_ingresos.create') }}" 
    buttonText="Crear Ingreso" 
    deletedRouteUrl="{{ route('mp_ingresos.deleted') }}"
    deletedButtonText="Ver Ingresos Eliminados" 
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="total-ingresos">-</h3>
                <p>Total de Ingresos</p>
            </div>
            <div class="icon">
                <i class="fas fa-cubes"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="activos-ingresos">-</h3>
                <p>Ingresos Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 id="eliminados-ingresos">-</h3>
                <p>Ingresos Eliminados</p>
            </div>
            <div class="icon">
                <i class="fas fa-trash-alt"></i>
            </div>
        </div>
    </div>
</div>

    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table id="ingresos_materia_prima" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                       
                            <th>Nro_Ingreso_MP</th>
                            <th>Fecha_Ingreso</th>
                            <th>Nro_OC</th>
                            <th>Proveedor</th>
                            <th>Materia Prima</th> <!-- Campo actualizado -->
                            <th>Diámetro MP</th> <!-- Campo actualizado -->
                            <th>Código MP</th>
                            <th>Nro_Certificado_MP</th>
                            <th>Detalle_Origen_MP</th>
                            <th>Unidades_MP</th>
                            <th>Longitud_Unidad_MP</th>
                            <th>Mts_Totales</th>
                            <th>Kilos_Totales</th>
                            <th>created_at</th>
                            <th>updated_at</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            
                            <th><input type="text" id="filtro_nro_ingreso" class="form-control filtro-texto" placeholder="Buscar Nro_Ingreso_MP"></th>
                            <th><input type="text" id="filtro_fecha_ingreso" class="form-control filtro-texto" placeholder="Filtrar Fecha"></th>
                            <th><input type="text" id="filtro_nro_oc" class="form-control filtro-texto" placeholder="Filtrar Nro_OC"></th>
                            <th><select id="filtro_proveedor" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_materia_prima" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_diametro" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_codigo_mp" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_certificado" class="form-control filtro-texto" placeholder="Filtrar Certificado"></th>
                            <th><input type="text" id="filtro_detalle_origen" class="form-control filtro-texto" placeholder="Filtrar Detalle_Origen"></th>
                            <th><input type="text" id="filtro_unidades" class="form-control filtro-texto" placeholder="Filtrar Unidades"></th>
                            <th><input type="text" id="filtro_longitud" class="form-control filtro-texto" placeholder="Filtrar Longitud"></th>
                            <th><input type="text" id="filtro_mts_totales" class="form-control filtro-texto" placeholder="Filtrar Mts_Totales"></th>
                            <th><input type="text" id="filtro_kilos_totales" class="form-control filtro-texto" placeholder="Filtrar Kilos"></th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_ingreso_index.css') }}">
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

    // Cargar resumen de ingresos
$.get("{{ route('mp_ingresos.resumen') }}", function (data) {
    $('#total-ingresos').text(data.total);
    $('#activos-ingresos').text(data.activos);
    $('#eliminados-ingresos').text(data.eliminados);
});

     // Función para eliminar un ingreso
     function deleteIngreso(id) {
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
                url: `/mp_ingresos/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: 'El ingreso de materia prima ha sido eliminado.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    // Recargar la tabla sin recargar toda la página
                    $('#ingresos_materia_prima').DataTable().ajax.reload(null, false);
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
    var table = $('#ingresos_materia_prima').DataTable({
        processing: true,
        serverSide: true,
        
        ajax: {
            url: "{{ route('mp_ingresos.data') }}",
            type: 'GET',
            data: function (d) {

                d.filtro_nro_ingreso = $('#filtro_nro_ingreso').val();
                d.filtro_fecha_ingreso = $('#filtro_fecha_ingreso').val();
                d.filtro_nro_oc = $('#filtro_nro_oc').val();
                d.filtro_proveedor = $('#filtro_proveedor').val();
                d.filtro_materia_prima = $('#filtro_materia_prima').val();
                d.filtro_diametro = $('#filtro_diametro').val();
                d.filtro_codigo_mp = $('#filtro_codigo_mp').val();
                d.filtro_certificado = $('#filtro_certificado').val();
                d.filtro_detalle_origen = $('#filtro_detalle_origen').val();
                d.filtro_unidades = $('#filtro_unidades').val();
                d.filtro_longitud = $('#filtro_longitud').val();
                d.filtro_mts_totales = $('#filtro_mts_totales').val();
                d.filtro_kilos_totales = $('#filtro_kilos_totales').val();
            }
        },
        columns: [
            { data: 'Nro_Ingreso_MP' },
            { data: 'Fecha_Ingreso' },
            { data: 'Nro_OC' },
            { data: 'Proveedor' },
            { data: 'Materia_Prima' },
            { data: 'Diametro_MP' },
            { data: 'Codigo_MP' },
            { data: 'Nro_Certificado_MP' },
            { data: 'Detalle_Origen_MP' },
            { data: 'Unidades_MP' },
            { data: 'Longitud_Unidad_MP' },
            { data: 'Mts_Totales' },
            { data: 'Kilos_Totales' },
            { data: 'mp_ingreso_created_at' },
            { data: 'mp_ingreso_updated_at' },
            {
                data: 'Id_MP',
                render: function (data) {
                    return `
                            <a href="/mp_ingresos/${data}" class="btn btn-info btn-sm">Ver</a>
                            <a href="/mp_ingresos/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                            <button onclick="deleteIngreso(${data})" class="btn btn-danger btn-sm">Eliminar</button>
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
        pageLength: 10,
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
            url: "{{ route('mp_ingresos.filters') }}",
            type: 'GET',
            success: function (data) {
                fillSelect('#filtro_proveedor', data.proveedores);
                fillSelect('#filtro_materia_prima', data.materias_primas);
                fillSelect('#filtro_diametro', data.diametros);
                fillSelect('#filtro_codigo_mp', data.codigos);
            }
        });
    };



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

    
    // Actualizar el contador de materias base
    table.on('xhr', function () {
        var json = table.ajax.json();
        var totalIngresos = json.recordsTotal;
        $('#totalCantPiezas').text(totalIngresos);
    });

});
</script>
@stop
