@extends('adminlte::page')

@section('title', 'Grupos de Conjuntos')

@section('content_header')
<x-header-card
    title="Grupos de Conjuntos"
    buttonRoute="{{ route('producto_grupo_conjuntos.create') }}"
    buttonText="Crear Grupo"
    deletedRouteUrl="{{ route('producto_grupo_conjuntos.deleted') }}"
    deletedButtonText="Ver eliminados"
/>
@stop

@section('content')
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-grupos">{{ $totalGrupos }}</h3>
                    <p>Total de grupos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activos-grupos">0</h3>
                    <p>Grupos activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="eliminados-grupos">0</h3>
                    <p>Grupos eliminados</p>
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
                <table id="tabla_grupo_conjuntos" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Subcategoria</th>
                            <th>Nombre Grupo</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                        <tr>
                            <th>
                                <select id="filtro_subcategoria" class="form-control form-control-sm filtro-select">
                                    <option value="">Todas</option>
                                </select>
                            </th>
                            <th>
                                <select id="filtro_nombre" class="form-control form-control-sm filtro-select">
                                    <option value="">Todos</option>
                                </select>
                            </th>
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
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/grupos_conjuntos/index.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
    $(document).ready(function () {
        function cargarResumen() {
            $.get("{{ route('producto_grupo_conjuntos.resumen') }}", function (data) {
                $('#total-grupos').text(data.total);
                $('#activos-grupos').text(data.activos);
                $('#eliminados-grupos').text(data.eliminados);
            });
        }

        function cargarFiltros() {
            $.get("{{ route('producto_grupo_conjuntos.filters') }}", function (data) {
                const selectSubcategoria = $('#filtro_subcategoria');
                const selectNombre = $('#filtro_nombre');
                const selectStatus = $('#filtro_status');

                selectSubcategoria.empty().append('<option value="">Todas</option>');
                selectNombre.empty().append('<option value="">Todos</option>');
                selectStatus.empty().append('<option value="">Todos</option>');

                data.subcategorias.forEach(function (item) {
                    selectSubcategoria.append(`<option value="${item}">${item}</option>`);
                });

                data.nombres.forEach(function (item) {
                    selectNombre.append(`<option value="${item}">${item}</option>`);
                });

                data.status.forEach(function (item) {
                    selectStatus.append(`<option value="${item}">${item}</option>`);
                });
            });
        }

        const table = $('#tabla_grupo_conjuntos').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('producto_grupo_conjuntos.data') }}",
                data: function (d) {
                    d.filtro_subcategoria = $('#filtro_subcategoria').val();
                    d.filtro_nombre = $('#filtro_nombre').val();
                    d.filtro_status = $('#filtro_status').val();
                }
            },
            columns: [
                { data: 'Nombre_SubCategoria', name: 'Nombre_SubCategoria', orderable: false, searchable: false, defaultContent: '' },
                { data: 'Nombre_GrupoConjuntos', name: 'Nombre_GrupoConjuntos' },
                { data: 'Status_Texto', name: 'Status_Texto', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' },
                {
                    data: 'Id_GrupoConjuntos',
                    name: 'acciones',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return `
                            <a href="{{ url('producto_grupo_conjuntos') }}/${data}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ url('producto_grupo_conjuntos') }}/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                            <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="${data}">Eliminar</button>
                        `;
                    }
                }
            ],
            order: [[1, 'asc']],
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

        $('#filtro_nombre').on('change', function () {
            table.draw();
        });

        $('#filtro_subcategoria').on('change', function () {
            table.draw();
        });

        $('#filtro_status').on('change', function () {
            table.draw();
        });

        $('#clearFilters').on('click', function () {
            $('#filtro_subcategoria').val('');
            $('#filtro_nombre').val('');
            $('#filtro_status').val('');
            table.draw();
        });

        $(document).on('click', '.btn-eliminar', function () {
            const id = $(this).data('id');

            SwalUtils.confirmDelete('El grupo sera enviado a eliminados.').then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('producto_grupo_conjuntos') }}/${id}`,
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
