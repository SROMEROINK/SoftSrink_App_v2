@extends('adminlte::page')

@section('title', 'Listado de Productos')

@section('content_header')
<x-header-card
    title="Listado de Productos"
    buttonRoute="{{ route('productos.create') }}"
    buttonText="Crear registro"
    deletedRouteUrl="{{ route('productos.deleted') }}"
    deletedButtonText="Ver eliminados"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-productos">0</h3>
                    <p>Total de productos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activos-productos">0</h3>
                    <p>Productos activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="eliminados-productos">0</h3>
                    <p>Productos eliminados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trash"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-fold-toolbar mb-3">
                <div class="table-fold-toolbar__label">
                    Pliegues de columnas
                </div>
                <button type="button" id="toggle-pliegue-comercial" class="btn btn-outline-secondary btn-sm table-fold-toggle active">
                    <i class="fas fa-compress-alt mr-1"></i>
                    Ocultar Grupo Conjuntos + Cliente
                </button>
            </div>

            <div class="table-responsive table-responsive-productos">
                <table id="listado_productos" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><span class="table-header-label">Codigo</span></th>
                            <th><span class="table-header-label">Descripcion</span></th>
                            <th><span class="table-header-label">Tipo</span></th>
                            <th><span class="table-header-label">Categoria</span></th>
                            <th><span class="table-header-label">Subcategoria</span></th>
                            <th><span class="table-header-label">Grupo Subcategoria</span></th>
                            <th><span class="table-header-label">Grupo Conjuntos</span></th>
                            <th><span class="table-header-label">Cliente</span></th>
                            <th><span class="table-header-label">Nro Plano</span></th>
                            <th><span class="table-header-label">Ult. Revision Plano</span></th>
                            <th><span class="table-header-label">Material MP</span></th>
                            <th><span class="table-header-label">Diametro MP</span></th>
                            <th><span class="table-header-label">Codigo MP</span></th>
                            <th><span class="table-header-label">Longitud de Pieza</span></th>
                            <th><span class="table-header-label">Estado</span></th>
                            <th><span class="table-header-label">Acciones</span></th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_codigo" class="form-control filtro-texto" placeholder="Filtrar Codigo"></th>
                            <th><input type="text" id="filtro_descripcion" class="form-control filtro-texto" placeholder="Filtrar Descripcion"></th>
                            <th><input type="text" id="filtro_tipo" class="form-control filtro-texto" placeholder="Filtrar Tipo"></th>
                            <th>
                                <select id="filtro_familia" class="form-control filtro-select">
                                    <option value="">Todas</option>
                                </select>
                            </th>
                            <th>
                                <select id="filtro_sub_categoria" class="form-control filtro-select">
                                    <option value="">Todas</option>
                                </select>
                            </th>
                            <th>
                                <select id="filtro_grupo_sub_categoria" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                </select>
                            </th>
                            <th><input type="text" id="filtro_codigo_conjunto" class="form-control filtro-texto" placeholder="Filtrar Conjunto"></th>
                            <th><input type="text" id="filtro_cliente" class="form-control filtro-texto" placeholder="Filtrar Cliente"></th>
                            <th><input type="text" id="filtro_plano" class="form-control filtro-texto" placeholder="Filtrar Nro Plano"></th>
                            <th><input type="text" id="filtro_revision_plano" class="form-control filtro-texto" placeholder="Filtrar Revision"></th>
                            <th>
                                <select id="filtro_material_mp" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                </select>
                            </th>
                            <th>
                                <select id="filtro_diametro_mp" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                </select>
                            </th>
                            <th>
                                <select id="filtro_codigo_mp" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                </select>
                            </th>
                            <th><input type="text" id="filtro_longitud_pieza" class="form-control filtro-texto" placeholder="Filtrar Longitud"></th>
                            <th></th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/summary-boxes.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/Productos_Index.css') }}">
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
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
$(document).ready(function () {
    const defaultPageLength = 10;

    function hayFiltrosSelectActivos() {
        let activo = false;

        $('.filtro-select').each(function () {
            if ($(this).val() !== '') {
                activo = true;
                return false;
            }
        });

        return activo;
    }

    function aplicarPaginadoSegunFiltros(table) {
        const nuevoLargo = hayFiltrosSelectActivos() ? -1 : defaultPageLength;

        if (table.page.len() !== nuevoLargo) {
            table.page.len(nuevoLargo);
        }
    }

    function cargarFiltrosDependientes() {
        $.get("{{ route('productos.dependentFilters') }}", {
            categoria: $('#filtro_familia').val(),
            subcategoria: $('#filtro_sub_categoria').val(),
            material_mp: $('#filtro_material_mp').val(),
            diametro_mp: $('#filtro_diametro_mp').val()
        }, function (data) {
            const subcategorias = $('#filtro_sub_categoria');
            const grupos = $('#filtro_grupo_sub_categoria');
            const diametros = $('#filtro_diametro_mp');
            const codigosMp = $('#filtro_codigo_mp');
            const subcategoriaActual = subcategorias.val();
            const grupoActual = grupos.val();
            const diametroActual = diametros.val();
            const codigoMpActual = codigosMp.val();

            subcategorias.empty().append('<option value="">Todas</option>');
            grupos.empty().append('<option value="">Todos</option>');
            diametros.empty().append('<option value="">Todos</option>');
            codigosMp.empty().append('<option value="">Todos</option>');

            data.subcategorias.forEach(function (item) {
                subcategorias.append(`<option value="${item.Nombre_SubCategoria}">${item.Nombre_SubCategoria}</option>`);
            });

            data.gruposSubcategoria.forEach(function (item) {
                grupos.append(`<option value="${item.Nombre_GrupoSubCategoria}">${item.Nombre_GrupoSubCategoria}</option>`);
            });

            data.diametrosMP.forEach(function (item) {
                diametros.append(`<option value="${item.Prod_Diametro_de_MP}">${item.Prod_Diametro_de_MP}</option>`);
            });

            data.codigosMP.forEach(function (item) {
                codigosMp.append(`<option value="${item.Prod_Codigo_MP}">${item.Prod_Codigo_MP}</option>`);
            });

            if (subcategoriaActual && subcategorias.find(`option[value="${subcategoriaActual}"]`).length) {
                subcategorias.val(subcategoriaActual);
            }

            if (grupoActual && grupos.find(`option[value="${grupoActual}"]`).length) {
                grupos.val(grupoActual);
            }

            if (diametroActual && diametros.find(`option[value="${diametroActual}"]`).length) {
                diametros.val(diametroActual);
            }

            if (codigoMpActual && codigosMp.find(`option[value="${codigoMpActual}"]`).length) {
                codigosMp.val(codigoMpActual);
            }
        });
    }

    function cargarResumen() {
        $.get("{{ route('productos.resumen') }}", function (data) {
            $('#total-productos').text(data.total);
            $('#activos-productos').text(data.activos);
            $('#eliminados-productos').text(data.eliminados);
        });
    }

    function cargarFiltros() {
        $.get("{{ route('productos.getUniqueFilters') }}", function (data) {
            const familias = $('#filtro_familia');
            const subcategorias = $('#filtro_sub_categoria');
            const grupos = $('#filtro_grupo_sub_categoria');
            const materiales = $('#filtro_material_mp');
            const diametros = $('#filtro_diametro_mp');
            const codigosMp = $('#filtro_codigo_mp');

            familias.empty().append('<option value="">Todas</option>');
            subcategorias.empty().append('<option value="">Todas</option>');
            grupos.empty().append('<option value="">Todos</option>');
            materiales.empty().append('<option value="">Todos</option>');
            diametros.empty().append('<option value="">Todos</option>');
            codigosMp.empty().append('<option value="">Todos</option>');

            data.familias.forEach(function (item) {
                familias.append(`<option value="${item.Nombre_Categoria}">${item.Nombre_Categoria}</option>`);
            });

            data.materialesMP.forEach(function (item) {
                materiales.append(`<option value="${item.Prod_Material_MP}">${item.Prod_Material_MP}</option>`);
            });

            data.diametrosMP.forEach(function (item) {
                diametros.append(`<option value="${item.Prod_Diametro_de_MP}">${item.Prod_Diametro_de_MP}</option>`);
            });

            data.codigosMP.forEach(function (item) {
                codigosMp.append(`<option value="${item.Prod_Codigo_MP}">${item.Prod_Codigo_MP}</option>`);
            });

            cargarFiltrosDependientes();
        });
    }

    const table = $('#listado_productos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('productos.data') }}",
            type: 'GET',
            data: function (d) {
                d.filtro_codigo = $('#filtro_codigo').val();
                d.filtro_descripcion = $('#filtro_descripcion').val();
                d.filtro_tipo = $('#filtro_tipo').val();
                d.filtro_familia = $('#filtro_familia').val();
                d.filtro_sub_familia = $('#filtro_sub_categoria').val();
                d.filtro_grupo_sub_categoria = $('#filtro_grupo_sub_categoria').val();
                d.filtro_codigo_conjunto = $('#filtro_codigo_conjunto').val();
                d.filtro_cliente = $('#filtro_cliente').val();
                d.filtro_plano = $('#filtro_plano').val();
                d.filtro_revision_plano = $('#filtro_revision_plano').val();
                d.filtro_material_mp = $('#filtro_material_mp').val();
                d.filtro_diametro_mp = $('#filtro_diametro_mp').val();
                d.filtro_codigo_mp = $('#filtro_codigo_mp').val();
                d.filtro_longitud_pieza = $('#filtro_longitud_pieza').val();
            }
        },
        columns: [
            { data: 'Prod_Codigo', name: 'Prod_Codigo' },
            { data: 'Prod_Descripcion', name: 'Prod_Descripcion' },
            { data: 'Nombre_Tipo', name: 'Nombre_Tipo', orderable: false, searchable: false },
            { data: 'Nombre_Categoria', name: 'Nombre_Categoria', orderable: false, searchable: false },
            { data: 'Nombre_SubCategoria', name: 'Nombre_SubCategoria', orderable: false, searchable: false },
            { data: 'Nombre_GrupoSubCategoria', name: 'Nombre_GrupoSubCategoria', orderable: false, searchable: false },
            { data: 'Nombre_GrupoConjuntos', name: 'Nombre_GrupoConjuntos', orderable: false, searchable: false },
            { data: 'Cli_Nombre', name: 'Cli_Nombre', orderable: false, searchable: false },
            { data: 'Prod_N_Plano', name: 'Prod_N_Plano' },
            { data: 'Prod_Plano_Ultima_Revision', name: 'Prod_Plano_Ultima_Revision' },
            { data: 'Prod_Material_MP', name: 'Prod_Material_MP' },
            { data: 'Prod_Diametro_de_MP', name: 'Prod_Diametro_de_MP' },
            { data: 'Prod_Codigo_MP', name: 'Prod_Codigo_MP' },
            { data: 'Prod_Longitud_de_Pieza', name: 'Prod_Longitud_de_Pieza' },
            { data: 'Estado_Texto', name: 'Estado_Texto', orderable: false, searchable: false },
            {
                data: 'Id_Producto',
                name: 'acciones',
                orderable: false,
                searchable: false,
                className: 'columna-acciones',
                render: function (data) {
                    return `
                        <div class="acciones-grupo">
                            <a href="{{ url('productos') }}/${data}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ url('productos') }}/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
                            <button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="${data}">Eliminar</button>
                        </div>
                    `;
                }
            }
        ],
        scrollY: '60vh',
        scrollCollapse: true,
        searching: false,
        paging: true,
        fixedHeader: true,
        responsive: true,
        orderCellsTop: true,
        pageLength: defaultPageLength,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        language: {
            url: "{{ asset('Spanish.json') }}"
        }
    });

    const columnasPliegueComercial = [6, 7];

    function recalcularTabla() {
        setTimeout(function () {
            const tablaNode = $('#listado_productos');
            const wrapper = tablaNode.closest('.dataTables_wrapper');
            const contenedor = $('.table-responsive-productos');

            tablaNode.css('width', 'auto');
            wrapper.find('.dataTables_scrollHeadInner, .dataTables_scrollHeadInner table, .dataTables_scrollBody table').css('width', 'auto');

            contenedor.toggleClass('tabla-plegada', !table.column(columnasPliegueComercial[0]).visible());

            table.columns.adjust();

            if (table.responsive && typeof table.responsive.recalc === 'function') {
                table.responsive.recalc();
            }

            if (table.fixedHeader && typeof table.fixedHeader.adjust === 'function') {
                table.fixedHeader.adjust();
            }
        }, 50);
    }

    function actualizarBotonPliegueComercial(visible) {
        const boton = $('#toggle-pliegue-comercial');

        boton.toggleClass('active', visible);
        boton.html(visible
            ? '<i class="fas fa-compress-alt mr-1"></i> Ocultar Grupo Conjuntos + Cliente'
            : '<i class="fas fa-expand-alt mr-1"></i> Mostrar Grupo Conjuntos + Cliente'
        );
    }

    $('#toggle-pliegue-comercial').on('click', function () {
        const visibleActual = table.column(columnasPliegueComercial[0]).visible();
        const nuevoEstado = !visibleActual;

        columnasPliegueComercial.forEach(function (indice) {
            table.column(indice).visible(nuevoEstado, false);
        });

        table.draw(false);
        actualizarBotonPliegueComercial(nuevoEstado);
        recalcularTabla();
    });

    $('.filtro-texto').on('change keyup', function () {
        table.ajax.reload(null, false);
    });

    $('#filtro_familia').on('change', function () {
        $('#filtro_sub_categoria').val('');
        $('#filtro_grupo_sub_categoria').val('');
        cargarFiltrosDependientes();
        aplicarPaginadoSegunFiltros(table);
        table.ajax.reload(null, false);
    });

    $('#filtro_sub_categoria').on('change', function () {
        $('#filtro_grupo_sub_categoria').val('');
        cargarFiltrosDependientes();
        aplicarPaginadoSegunFiltros(table);
        table.ajax.reload(null, false);
    });

    $('#filtro_material_mp').on('change', function () {
        $('#filtro_diametro_mp').val('');
        $('#filtro_codigo_mp').val('');
        cargarFiltrosDependientes();
        aplicarPaginadoSegunFiltros(table);
        table.ajax.reload(null, false);
    });

    $('#filtro_diametro_mp').on('change', function () {
        $('#filtro_codigo_mp').val('');
        cargarFiltrosDependientes();
        aplicarPaginadoSegunFiltros(table);
        table.ajax.reload(null, false);
    });

    $('.filtro-select').not('#filtro_familia, #filtro_sub_categoria, #filtro_material_mp, #filtro_diametro_mp').on('change', function () {
        aplicarPaginadoSegunFiltros(table);
        table.ajax.reload(null, false);
    });

    $('#clearFilters').on('click', function () {
        $('.filtro-select, .filtro-texto').val('');
        cargarFiltrosDependientes();
        aplicarPaginadoSegunFiltros(table);
        table.ajax.reload(null, false);
    });

    $(document).on('click', '.btn-eliminar', function () {
        const id = $(this).data('id');

        SwalUtils.confirmDelete('El producto sera enviado a eliminados.').then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('productos') }}/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        SwalUtils.deleted(response.message);

                        cargarResumen();
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        SwalUtils.error(xhr.responseJSON?.message || 'No se pudo eliminar el registro.');
                    }
                });
            }
        });
    });

    cargarFiltros();
    cargarResumen();
    aplicarPaginadoSegunFiltros(table);
    actualizarBotonPliegueComercial(true);
    recalcularTabla();

    table.on('draw.dt column-visibility.dt responsive-resize.dt', function () {
        recalcularTabla();
    });

    $(window).on('resize', function () {
        recalcularTabla();
    });
});
</script>
@stop
