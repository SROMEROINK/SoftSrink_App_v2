@extends('adminlte::page')

@section('title', 'Listado de Proveedores')

@section('content_header')
<x-header-card 
    title="Listado de Proveedores" 
    buttonRoute="{{ route('proveedores.create') }}" 
    buttonText="Crear Proveedor" 
    deletedRouteUrl="{{ route('proveedores.deleted') }}"
    deletedButtonText="Ver Proveedores Eliminados" 
/>
@stop

@section('content')
<div class="container-fluid">

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-proveedores">0</h3>
                    <p>Total de proveedores</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck-loading"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activos-proveedores">0</h3>
                    <p>Proveedores activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="eliminados-proveedores">0</h3>
                    <p>Proveedores eliminados</p>
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
                <table id="tabla_proveedores" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Id_Proveedor</th>
                            <th>Nombre</th>
                            <th>Detalle</th>
                            <th>Proveedor MP</th>
                            <th>Proveedor Herramientas</th>
                            <th>Nombre Contacto</th>
                            <th>Nro Telefono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><input type="text" id="filtro_nombre" placeholder="Filtrar por Nombre" class="form-control filtro-texto"></th>
                            <th><input type="text" id="filtro_detalle" placeholder="Filtrar por Detalle" class="form-control filtro-texto"></th>
                            <th>
                                <select id="filtro_proveedor_mp" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                    <option value="1">proveedor_mp</option>
                                    <option value="0">''</option>
                                </select>
                            </th>
                            <th>
                                <select id="filtro_proveedor_herramientas" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                    <option value="1">proveedor_herramientas</option>
                                    <option value="0">''</option>
                                </select>
                            </th>
                            <th></th>
                            <th></th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/proveedores_index.css') }}">
@stop

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

<script>
    function cargarResumen() {
        $.get("{{ route('proveedores.resumen') }}", function (data) {
            $('#total-proveedores').text(data.total);
            $('#activos-proveedores').text(data.activos);
            $('#eliminados-proveedores').text(data.eliminados);
        });
    }

    function deleteProveedor(id) {
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
                    url: `/proveedores/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#tabla_proveedores').DataTable().ajax.reload(null, false);
                        cargarResumen();

                        Swal.fire(
                            '¡Eliminado!',
                            response.message || 'El proveedor ha sido eliminado.',
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            '¡Error!',
                            xhr.responseJSON?.message || 'No se pudo eliminar el proveedor.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        cargarResumen();

        const table = $('#tabla_proveedores').DataTable({
            processing: true,
            serverSide: true,
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
                url: "{{ route('proveedores.data') }}",
                data: function (d) {
                    d.filtro_nombre = $('#filtro_nombre').val();
                    d.filtro_detalle = $('#filtro_detalle').val();
                    d.filtro_proveedor_mp = $('#filtro_proveedor_mp').val();
                    d.filtro_proveedor_herramientas = $('#filtro_proveedor_herramientas').val();
                    d.filtro_estado = $('#filtro_estado').val();
                }
            },
            columns: [
                { data: 'Prov_Id', name: 'Prov_Id' },
                { data: 'Prov_Nombre', name: 'Prov_Nombre' },
                { data: 'Prov_Detalle', name: 'Prov_Detalle' },
                { data: 'ProveedorMPTexto', name: 'Es_Proveedor_MP', orderable: false, searchable: false },
                { data: 'ProveedorHerramientasTexto', name: 'Es_Proveedor_Herramientas', orderable: false, searchable: false },
                { data: 'Nombre_Contacto', name: 'Nombre_Contacto' },
                { data: 'Nro_Telefono', name: 'Nro_Telefono' },
                { data: 'EstadoTexto', name: 'reg_Status', orderable: false, searchable: false },
                {
                    data: 'Prov_Id',
                    name: 'acciones',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `
                            <a href="/proveedores/${data}" class="btn btn-info btn-sm">Ver</a>                        
                            <a href="/proveedores/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                            <button onclick="deleteProveedor(${data})" class="btn btn-danger btn-sm">Eliminar</button>
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
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                },
                processing: "Procesando..."
            }
        });

        $('#filtro_nombre, #filtro_detalle').on('keyup input', function() {
            table.draw();
        });

        $('#filtro_proveedor_mp, #filtro_proveedor_herramientas, #filtro_estado').on('change', function() {
            table.draw();
        });

        $('#clearFilters').click(function() {
            $('#filtro_nombre').val('');
            $('#filtro_detalle').val('');
            $('#filtro_proveedor_mp').val('');
            $('#filtro_proveedor_herramientas').val('');
            $('#filtro_estado').val('');
            table.draw();
        });

    });
</script>
@stop