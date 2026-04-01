@extends('adminlte::page')

@section('title', 'Categorias de Producto')

@section('content_header')
<x-header-card
    title="Categorias de Producto"
    buttonRoute="{{ route('producto_categoria.create') }}"
    buttonText="Crear Categoria"
    deletedRouteUrl="{{ route('producto_categoria.deleted') }}"
    deletedButtonText="Ver eliminados"
/>
@stop

@section('content')
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-categorias">{{ $totalCategorias }}</h3>
                    <p>Total de categorias</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activas-categorias">0</h3>
                    <p>Categorias activas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="eliminadas-categorias">0</h3>
                    <p>Categorias eliminadas</p>
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
                <table id="tabla_producto_categoria" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nombre Categoria</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                        <tr>
                            <th><input type="text" id="filtro_nombre" class="form-control form-control-sm filtro-texto" placeholder="Filtrar Categoria"></th>
                            <th>
                                <select id="filtro_status" class="form-control form-control-sm filtro-select">
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
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_index.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/categorias/index.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
    $(document).ready(function () {
        function cargarResumen() {
            $.get("{{ route('producto_categoria.resumen') }}", function (data) {
                $('#total-categorias').text(data.total);
                $('#activas-categorias').text(data.activos);
                $('#eliminadas-categorias').text(data.eliminados);
            });
        }

        function cargarFiltros() {
            $.get("{{ route('producto_categoria.filters') }}", function (data) {
                const selectStatus = $('#filtro_status');
                selectStatus.empty().append('<option value="">Todos</option>');

                data.status.forEach(function (item) {
                    selectStatus.append(`<option value="${item}">${item}</option>`);
                });
            });
        }

        const table = $('#tabla_producto_categoria').DataTable({
            processing: true,
            serverSide: true,
            scrollY: '60vh',
            scrollCollapse: true,
            ajax: {
                url: "{{ route('producto_categoria.data') }}",
                data: function (d) {
                    d.filtro_nombre = $('#filtro_nombre').val();
                    d.filtro_status = $('#filtro_status').val();
                }
            },
            columns: [
                { data: 'Nombre_Categoria', name: 'Nombre_Categoria' },
                { data: 'Status_Texto', name: 'Status_Texto', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' },
                {
                    data: 'Id_Categoria',
                    name: 'acciones',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return `
                            <a href="{{ url('producto_categoria') }}/${data}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ url('producto_categoria') }}/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                            <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="${data}">Eliminar</button>
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
                    last: "Ultimo",
                    next: "Siguiente",
                    previous: "Anterior"
                },
                processing: "Procesando..."
            }
        });

        $('#filtro_nombre').on('keyup change', function () {
            table.draw();
        });

        $('#filtro_status').on('change', function () {
            table.draw();
        });

        $('#clearFilters').on('click', function () {
            $('#filtro_nombre').val('');
            $('#filtro_status').val('');
            table.draw();
        });

        $(document).on('click', '.btn-eliminar', function () {
            const id = $(this).data('id');

            SwalUtils.confirmDelete('La categoria sera enviada a eliminados.').then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('producto_categoria') }}/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            SwalUtils.deleted(response.message);

                            table.draw(false);
                            cargarResumen();
                        },
                        error: function (xhr) {
                            SwalUtils.error(xhr.responseJSON?.message || 'No se pudo eliminar el registro.');
                        }
                    });
                }
            });
        });

        cargarResumen();
        cargarFiltros();
    });
</script>
@stop

