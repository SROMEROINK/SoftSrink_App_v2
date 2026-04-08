@extends('adminlte::page')

@section('title', 'Fabricacion - Registro_De_Fabricacion')

@section('content_header')
<x-header-card
    title="Registro de Fabricacion"
    buttonRoute="{{ route('fabricacion.create') }}"
    buttonText="Crear registro"
/>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show flash-alert" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show flash-alert" role="alert">
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show flash-alert" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif


    <div class="alert alert-info">
        <strong>Registro de fabricacion:</strong>
        la importacion historica compara por <strong>Nro_OF_Parcial</strong> e inserta solo filas faltantes.
        <br><strong>CSV historico:</strong> no quedan filas pendientes y el boton quedo bloqueado para evitar duplicados.
    </div>

    <div class="fabricacion-secondary-toolbar">
        <a href="{{ route('fabricacion.resumenMensual') }}" class="btn btn-outline-primary btn-sm">
            Resumen OF fabricadas
        </a>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-registros">0</h3>
                    <p>Registros cargados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-industry"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="total-piezas-filtradas">0</h3>
                    <p>Piezas fabricadas segun filtro</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="total-of-filtradas">0</h3>
                    <p>OF con fabricacion segun filtro</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-fold-toolbar mb-3">
                        <div class="table-fold-toolbar__label">
                            Pliegues de columnas
                        </div>
                        <button type="button" id="toggle-pliegue-auditoria" class="btn btn-outline-secondary btn-sm table-fold-toggle">
                            <i class="fas fa-expand-alt mr-1"></i>
                            Mostrar auditoria
                        </button>
                    </div>

                    <div class="table-responsive table-responsive-fabricacion">
                        <table id="registro_de_fabricacion" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Id_OF</th>
                                    <th class="column-nro-of">Nro_OF</th>
                                    <th>Prod_Codigo</th>
                                    <th>Prod_Descripcion</th>
                                    <th>Nombre_Categoria</th>
                                    <th>Nro_Maquina</th>
                                    <th>Familia_Maquinas</th>
                                    <th>Fecha_Fabricacion</th>
                                    <th>Nro_Parcial</th>
                                    <th>Cant_Piezas</th>
                                    <th>Horario</th>
                                    <th>Nombre_Operario</th>
                                    <th>Turno</th>
                                    <th>Cant_Horas_Extras</th>
                                    <th>created_at</th>
                                    <th>creator</th>
                                    <th>updated_at</th>
                                    <th>updater</th>
                                    <th class="columna-acciones">Acciones</th>
                                </tr>
                                <tr class="filter-row">
                                    <th><input type="text" id="filtro_id_of" class="form-control filtro-texto" placeholder="Buscar Id_OF"></th>
                                    <th><input type="text" id="filtro_nro_of" class="form-control filtro-texto" placeholder="Buscar Nro_OF"></th>
                                    <th><input type="text" id="filtro_prod_codigo" class="form-control filtro-texto" placeholder="Filtrar Prod_Codigo"></th>
                                    <th><input type="text" id="filtro_prod_descripcion" class="form-control filtro-texto" placeholder="Filtrar Prod_Descripcion"></th>
                                    <th><select id="filtro_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                                    <th><select id="filtro_maquina" class="form-control filtro-select"><option value="">Todos</option></select></th>
                                    <th><select id="filtro_familia" class="form-control filtro-select"><option value="">Todos</option></select></th>
                                    <th><input type="date" id="filtro_fecha_fabricacion" class="form-control filtro-texto"></th>
                                    <th><input type="text" id="filtro_nro_parcial" class="form-control filtro-texto" placeholder="Filtrar Nro_Parcial"></th>
                                    <th><input type="text" id="filtro_cant_piezas" class="form-control filtro-texto" placeholder="Filtrar Cant_Piezas"></th>
                                    <th><input type="text" id="filtro_horario" class="form-control filtro-texto" placeholder="Filtrar Horario"></th>
                                    <th><input type="text" id="filtro_nombre_operario" class="form-control filtro-texto" placeholder="Filtrar Nombre_Operario"></th>
                                    <th><input type="text" id="filtro_turno" class="form-control filtro-texto" placeholder="Filtrar Turno"></th>
                                    <th><input type="text" id="filtro_cant_horas_extras" class="form-control filtro-texto" placeholder="Filtrar Cant_Horas_Extras"></th>
                                    <th class="columna-acciones"></th>
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
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/datatables.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/summary-boxes.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/Productos_Index.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_index.css') }}">
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
<script>
function formatearNumeroEntero(valor) {
    const numero = Number(valor || 0);
    return numero.toLocaleString('es-AR');
}

$(document).ready(function () {
    const columnasPliegueAuditoria = [14, 15, 16, 17];
    const ordenDefault = [[0, 'desc']];
    const ordenPorParcial = [[8, 'asc']];

    function filtrosActuales() {
        return {
            filtro_id_of: $('#filtro_id_of').val(),
            filtro_nro_of: $('#filtro_nro_of').val(),
            filtro_prod_codigo: $('#filtro_prod_codigo').val(),
            filtro_prod_descripcion: $('#filtro_prod_descripcion').val(),
            filtro_categoria: $('#filtro_categoria').val(),
            filtro_maquina: $('#filtro_maquina').val(),
            filtro_familia: $('#filtro_familia').val(),
            filtro_fecha_fabricacion: $('#filtro_fecha_fabricacion').val(),
            filtro_nro_parcial: $('#filtro_nro_parcial').val(),
            filtro_cant_piezas: $('#filtro_cant_piezas').val(),
            filtro_horario: $('#filtro_horario').val(),
            filtro_nombre_operario: $('#filtro_nombre_operario').val(),
            filtro_turno: $('#filtro_turno').val(),
            filtro_cant_horas_extras: $('#filtro_cant_horas_extras').val()
        };
    }

    function cargarResumenFabricacion() {
        $.get("{{ route('fabricacion.resumen') }}", filtrosActuales(), function (data) {
            $('#total-registros').text(formatearNumeroEntero(data.total_registros));
            $('#total-piezas-filtradas').text(formatearNumeroEntero(data.total_piezas));
            $('#total-of-filtradas').text(formatearNumeroEntero(data.total_of));
        });
    }

    function aplicarOrdenSegunFiltroOf() {
        const tieneFiltroOf = $('#filtro_nro_of').val().trim() !== '';
        const ordenActual = table.order();
        const ordenEsperado = tieneFiltroOf ? ordenPorParcial : ordenDefault;

        if (JSON.stringify(ordenActual) !== JSON.stringify(ordenEsperado)) {
            table.order(ordenEsperado);
        }
    }

    function recalcularTabla() {
        setTimeout(function () {
            const tablaNode = $('#registro_de_fabricacion');
            const wrapper = tablaNode.closest('.dataTables_wrapper');
            const contenedor = $('.table-responsive-fabricacion');

            tablaNode.css('width', 'auto');
            wrapper.find('.dataTables_scrollHeadInner, .dataTables_scrollHeadInner table, .dataTables_scrollBody table').css('width', 'auto');

            contenedor.toggleClass('tabla-plegada', !table.column(columnasPliegueAuditoria[0]).visible());

            table.columns.adjust();

            if (table.responsive && typeof table.responsive.recalc === 'function') {
                table.responsive.recalc();
            }

            if (table.fixedHeader && typeof table.fixedHeader.adjust === 'function') {
                table.fixedHeader.adjust();
            }
        }, 50);
    }

    function actualizarBotonPliegueAuditoria(visible) {
        const boton = $('#toggle-pliegue-auditoria');

        boton.toggleClass('active', visible);
        boton.html(visible
            ? '<i class="fas fa-compress-alt mr-1"></i> Ocultar auditoria'
            : '<i class="fas fa-expand-alt mr-1"></i> Mostrar auditoria'
        );
    }

    var table = $('#registro_de_fabricacion').DataTable({
        processing: true,
        serverSide: true,
        order: ordenDefault,
        ajax: {
            url: "{{ route('fabricacion.data') }}",
            type: 'GET',
            data: function (d) {
                Object.assign(d, filtrosActuales());
            }
        },
        columns: [
            { data: 'Id_OF', name: 'Id_OF', className: 'columna-id text-nowrap' },
            { data: 'Nro_OF_Visual', name: 'Nro_OF_Visual', className: 'columna-of text-nowrap' },
            { data: 'Prod_Codigo', className: 'columna-codigo text-nowrap' },
            { data: 'Prod_Descripcion', className: 'columna-descripcion' },
            { data: 'Nombre_Categoria', className: 'columna-categoria text-nowrap' },
            { data: 'Nro_Maquina', className: 'columna-maquina text-nowrap' },
            { data: 'Familia_Maquinas', className: 'columna-familia text-nowrap' },
            { data: 'Fecha_Fabricacion', className: 'columna-fecha text-nowrap' },
            { data: 'Nro_Parcial', className: 'columna-parcial text-nowrap' },
            { data: 'Cant_Piezas', className: 'columna-cantidad text-nowrap' },
            { data: 'Horario', className: 'columna-horario text-nowrap' },
            { data: 'Nombre_Operario', className: 'columna-operario' },
            { data: 'Turno', className: 'columna-turno text-nowrap' },
            { data: 'Cant_Horas_Extras', className: 'columna-horas text-nowrap' },
            { data: 'created_at', className: 'text-nowrap' },
            { data: 'creator', className: 'text-nowrap' },
            { data: 'updated_at', className: 'text-nowrap' },
            { data: 'updater', className: 'text-nowrap' },
            {
                data: 'Nro_OF',
                className: 'columna-acciones',
                render: function (data) {
                    return `<a href="/fabricacion/show/${data}" class="btn btn-info btn-sm">Ver Parciales</a>`;
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
        autoWidth: false,
        fixedHeader: true,
        responsive: false,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        language: {
            lengthMenu: 'Mostrar _MENU_ registros por pagina',
            zeroRecords: 'No se encontraron resultados',
            info: 'Mostrando pagina _PAGE_ de _PAGES_',
            infoEmpty: 'No hay registros disponibles',
            infoFiltered: '(filtrado de _MAX_ registros totales)',
            search: 'Buscar:',
            paginate: {
                first: 'Primero',
                last: 'Ultimo',
                next: 'Siguiente',
                previous: 'Anterior'
            }
        }
    });

    columnasPliegueAuditoria.forEach(function (indice) {
        table.column(indice).visible(false, false);
    });
    table.draw(false);
    actualizarBotonPliegueAuditoria(false);
    recalcularTabla();

    table.on('xhr', function () {
        var json = table.ajax.json();
        var categorias = new Set();
        var maquinas = new Set();
        var familias = new Set();

        $.each(json.data, function (index, item) {
            if (item.Nombre_Categoria) categorias.add(item.Nombre_Categoria);
            if (item.Nro_Maquina) maquinas.add(item.Nro_Maquina);
            if (item.Familia_Maquinas) familias.add(item.Familia_Maquinas);
        });

        rellenarSelect('#filtro_categoria', categorias);
        rellenarSelect('#filtro_maquina', maquinas);
        rellenarSelect('#filtro_familia', familias);
        cargarResumenFabricacion();
    });

    function rellenarSelect(selector, data) {
        var select = $(selector);
        var valorActual = select.val();
        select.empty();
        select.append('<option value="">Todos</option>');
        data.forEach(function (value) {
            select.append('<option value="' + value + '">' + value + '</option>');
        });
        if (valorActual && select.find(`option[value="${valorActual}"]`).length) {
            select.val(valorActual);
        }
    }

    $('#toggle-pliegue-auditoria').on('click', function () {
        const visibleActual = table.column(columnasPliegueAuditoria[0]).visible();
        const nuevoEstado = !visibleActual;

        columnasPliegueAuditoria.forEach(function (indice) {
            table.column(indice).visible(nuevoEstado, false);
        });

        table.draw(false);
        actualizarBotonPliegueAuditoria(nuevoEstado);
        recalcularTabla();
    });

    $('.filtro-select').on('change', function () {
        table.ajax.reload(null, false);
    });

    $('.filtro-texto').on('keyup change', function () {
        aplicarOrdenSegunFiltroOf();
        table.ajax.reload(null, false);
    });

    $('#clearFilters').click(function () {
        $('.filtro-select').val('');
        $('.filtro-texto').val('');
        table.order(ordenDefault);
        table.ajax.reload(null, false);
        cargarResumenFabricacion();
    });

    cargarResumenFabricacion();

    table.on('draw.dt column-visibility.dt responsive-resize.dt', function () {
        recalcularTabla();
    });

    $(window).on('resize', function () {
        recalcularTabla();
    });
});
</script>
@stop

