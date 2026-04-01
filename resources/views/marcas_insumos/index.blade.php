@extends('adminlte::page')
{{--resources\views\marcas_insumos\index.blade.php --}}
@section('title', 'Marcas de Insumos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Marcas de Insumos</h1>
        <div>
            <a href="{{ route('marcas_insumos.create') }}" class="btn btn-success">
                Crear Marca
            </a>
            <a href="{{ route('marcas_insumos.deleted') }}" class="btn btn-danger">
                Ver eliminados
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('partials.navigation')

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-marcas">0</h3>
                    <p>Total de marcas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activas-marcas">0</h3>
                    <p>Marcas activas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="eliminadas-marcas">0</h3>
                    <p>Marcas eliminadas</p>
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
                <table id="tabla_marcas_insumos" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nombre de Marca</th>
                            <th>Proveedor</th>
                            <th>Estado</th>
                            <th>Fecha de Creación</th>
                            <th>Última Actualización</th>
                            <th>Acciones</th>
                        </tr>
                        <tr>
                            <th>
                                <input type="text" id="filtro_nombre_marca" class="form-control form-control-sm filtro-texto" placeholder="Buscar Marca">
                            </th>
                            <th>
                                <select id="filtro_proveedor" class="form-control form-control-sm filtro-select">
                                    <option value="">Todos</option>
                                </select>
                            </th>
                            <th>
                                <select id="filtro_estado" class="form-control form-control-sm filtro-select">
                                    <option value="">Todos</option>
                                    <option value="1">Habilitado</option>
                                    <option value="0">Deshabilitado</option>
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
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_index.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/marcas_insumos_index.css') }}">
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
            $.get("{{ route('marcas_insumos.resumen') }}", function (data) {
                $('#total-marcas').text(data.total);
                $('#activas-marcas').text(data.activos);
                $('#eliminadas-marcas').text(data.eliminados);
            });
        }

        function cargarFiltrosEstado() {
    $.ajax({
        url: "{{ route('marcas_insumos.filters') }}",
        type: "GET",
        success: function (data) {
            const selectEstado = $('#filtro_estado');
            selectEstado.empty().append('<option value="">Todos</option>');

            data.status.forEach(function(item) {
                selectEstado.append(`<option value="${item}">${item}</option>`);
            });
        }
    });
}

function cargarFiltros() {
    $.ajax({
        url: "{{ route('marcas_insumos.filters') }}",
        type: "GET",
        success: function (data) {
            const selectProveedor = $('#filtro_proveedor');
            selectProveedor.empty().append('<option value="">Todos</option>');

            data.proveedores.forEach(function(item) {
                selectProveedor.append(`<option value="${item}">${item}</option>`);
            });
        },
        error: function(xhr) {
            console.error('Error al cargar filtros:', xhr.responseText);
        }
    });
}

        function deleteMarca(id) {
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
                        url: `/marcas_insumos/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $('#tabla_marcas_insumos').DataTable().ajax.reload(null, false);
                            cargarResumen();

                            Swal.fire(
                                '¡Eliminado!',
                                response.message || 'La marca de insumo ha sido eliminada.',
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
            cargarResumen();

            const table = $('#tabla_marcas_insumos').DataTable({
                processing: true,
                serverSide: true,
                scrollY: '60vh',
                scrollCollapse: true,
                ajax: {
                    url: "{{ route('marcas_insumos.data') }}",
                    data: function (d) {
                        d.filtro_nombre_marca = $('#filtro_nombre_marca').val();
                        d.filtro_proveedor = $('#filtro_proveedor').val();
                        d.filtro_estado = $('#filtro_estado').val();
                    }
                },
                columns: [
                    { data: 'Nombre_marca', name: 'marcas_insumos.Nombre_marca' },
                    { data: 'Proveedor', name: 'proveedores.Prov_Nombre', orderable: false, searchable: false },
                    { data: 'Estado_Texto', name: 'Estado_Texto', orderable: false, searchable: false },
                    { data: 'created_at', name: 'marcas_insumos.created_at' },
                    { data: 'updated_at', name: 'marcas_insumos.updated_at' },
                    {
                        data: 'Id_Marca',
                        name: 'acciones',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return `
                                <a href="/marcas_insumos/${data}" class="btn btn-info btn-sm">Ver</a>
                                <a href="/marcas_insumos/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                                <button onclick="deleteMarca(${data})" class="btn btn-danger btn-sm">Eliminar</button>
                            `;
                        }
                    }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
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

            $('#filtro_nombre_marca').on('keyup change', function () {
                table.draw();
            });

            $('#filtro_proveedor, #filtro_estado').on('change', function () {
                table.draw();
            });

            $('#clearFilters').click(function () {
                $('#filtro_nombre_marca').val('');
                $('#filtro_proveedor').val('');
                $('#filtro_estado').val('');
                table.draw();
            });

            cargarFiltros();
        });
    </script>
@stop

