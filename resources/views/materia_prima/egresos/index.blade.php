{{-- resources/views/materia_prima/egresos/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Materia Prima - Salidas')

@section('content_header')
<x-header-card 
    title="Salidas de Materia Prima" 
    quantityTitle="Total de Unidades Salidas:" 
    quantity="{{ $totalEgresos }}" 
    buttonRoute="{{ route('mp_egresos.create') }}" 
    buttonText="Crear Salida" 
    deletedRouteUrl="{{ route('mp_egresos.deleted') }}"
    deletedButtonText="Ver Salidas Eliminadas" 
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table id="salidas_materia_prima" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Id_Ingreso_MP</th>
                            <th>Id_OF_Salidas_MP</th>
                            <th>Cantidad_Unidades_MP</th>
                            <th>Cantidad_Unidades_MP_Preparadas</th>
                            <th>Cantidad_MP_Adicionales</th>
                            <th>Cant_Devoluciones</th>
                            <th>Total_Salidas_MP</th>
                            <th>Total_Mtros_Utilizados</th>
                            <th>Fecha_del_Pedido_Produccion</th>
                            <th>Responsable_Pedido_Produccion</th>
                            <th>Nro_Pedido_MP</th>
                            <th>Fecha_de_Entrega_Pedido_Calidad</th>
                            <th>Responsable_de_entrega_Calidad</th>
                            <th>created_at</th>
                            <th>updated_at</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_id_ingreso" class="form-control filtro-texto" placeholder="Buscar Id_Ingreso_MP"></th>
                            <th><input type="text" id="filtro_id_of_salidas" class="form-control filtro-texto" placeholder="Filtrar OF"></th>
                            <th><input type="text" id="filtro_cantidad_unidades" class="form-control filtro-texto" placeholder="Filtrar Cantidad"></th>
                            <th><input type="text" id="filtro_unidades_preparadas" class="form-control filtro-texto" placeholder="Filtrar Preparadas"></th>
                            <th><input type="text" id="filtro_adicionales" class="form-control filtro-texto" placeholder="Filtrar Adicionales"></th>
                            <th><input type="text" id="filtro_devoluciones" class="form-control filtro-texto" placeholder="Filtrar Devoluciones"></th>
                            <th><input type="text" id="filtro_total_salidas" class="form-control filtro-texto" placeholder="Filtrar Salidas"></th>
                            <th><input type="text" id="filtro_metros_utilizados" class="form-control filtro-texto" placeholder="Filtrar Metros"></th>
                            <th><input type="text" id="filtro_fecha_pedido" class="form-control filtro-texto" placeholder="Filtrar Fecha Pedido"></th>
                            <th><input type="text" id="filtro_responsable_pedido" class="form-control filtro-texto" placeholder="Filtrar Responsable"></th>
                            <th><input type="text" id="filtro_nro_pedido" class="form-control filtro-texto" placeholder="Filtrar Nro Pedido"></th>
                            <th><input type="text" id="filtro_fecha_entrega" class="form-control filtro-texto" placeholder="Filtrar Fecha Entrega"></th>
                            <th><input type="text  id="filtro_responsable_entrega" class="form-control filtro-texto" placeholder="Filtrar Responsable Entrega"></th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_egreso_index.css') }}">
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
    // Función para eliminar un egreso
    function deleteEgreso(id) {
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
                    url: `/mp_egresos/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: 'El egreso de materia prima ha sido eliminado.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#salidas_materia_prima').DataTable().ajax.reload(null, false);
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

        var table = $('#salidas_materia_prima').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('mp_egresos.data') }}",
                type: 'GET',
                data: function (d) {
                    d.filtro_id_ingreso = $('#filtro_id_ingreso').val();
                    d.filtro_id_of_salidas = $('#filtro_id_of_salidas').val();
                    d.filtro_cantidad_unidades = $('#filtro_cantidad_unidades').val();
                    d.filtro_unidades_preparadas = $('#filtro_unidades_preparadas').val();
                    d.filtro_adicionales = $('#filtro_adicionales').val();
                    d.filtro_devoluciones = $('#filtro_devoluciones').val();
                    d.filtro_total_salidas = $('#filtro_total_salidas').val();
                    d.filtro_metros_utilizados = $('#filtro_metros_utilizados').val();
                    d.filtro_fecha_pedido = $('#filtro_fecha_pedido').val();
                    d.filtro_responsable_pedido = $('#filtro_responsable_pedido').val();
                    d.filtro_nro_pedido = $('#filtro_nro_pedido').val();
                    d.filtro_fecha_entrega = $('#filtro_fecha_entrega').val();
                    d.filtro_responsable_entrega = $('#filtro_responsable_entrega').val();
                }
            },
            columns: [
                { data: 'Id_Ingreso_MP' },
                { data: 'Id_OF_Salidas_MP' },
                { data: 'Cantidad_Unidades_MP' },
                { data: 'Cantidad_Unidades_MP_Preparadas' },
                { data: 'Cantidad_MP_Adicionales' },
                { data: 'Cant_Devoluciones' },
                { data: 'Total_Salidas_MP' },
                { data: 'Total_Mtros_Utilizados' },
                { data: 'Fecha_del_Pedido_Produccion' },
                { data: 'Responsable_Pedido_Produccion' },
                { data: 'Nro_Pedido_MP' },
                { data: 'Fecha_de_Entrega_Pedido_Calidad' },
                { data: 'Responsable_de_entrega_Calidad' },
                { data: 'created_at' },
                { data: 'updated_at' },
                {
                    data: 'Id_Ingreso_MP',
                    render: function (data) {
                        return `
                            <a href="/mp_egresos/${data}" class="btn btn-info btn-sm">Ver</a>
                            <a href="/mp_egresos/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                            <button onclick="deleteEgreso(${data})" class="btn btn-danger btn-sm">Eliminar</button>
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
            drawCallback: function () {
                if (!filtersLoaded) {
                    loadUniqueFilters();
                    filtersLoaded = true;
                }
            }
        });

        function loadUniqueFilters() {
            $.ajax({
                url: "{{ route('mp_egresos.getUniqueFilters') }}",
                type: 'GET',
                success: function (data) {
                    fillSelect('#filtro_proveedor', data.proveedores);
                }
            });
        }

        function fillSelect(selector, data) {
            var select = $(selector);
            select.empty();
            select.append('<option value="">Todos</option>');
            data.forEach(function (value) {
                select.append('<option value="' + value + '">' + value + '</option>');
            });
        }

        $('.filtro-select, .filtro-texto').on('change keyup', function () {
            table.ajax.reload(null, false);
        });

        $('#clearFilters').click(function () {
            $('.filtro-select').val('');
            $('.filtro-texto').val('');
            table.ajax.reload();
        });

        table.on('xhr', function () {
            var json = table.ajax.json();
            var totalEgresos = json.recordsTotal;
            $('#totalCantBarras').text(totalEgresos);
        });
    });
</script>
@stop
