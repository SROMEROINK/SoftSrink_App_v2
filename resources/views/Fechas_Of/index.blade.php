@extends('adminlte::page')

@section('title', 'Fechas OF')

@section('content_header')
<x-header-card
    title="Fechas de Produccion por OF"
    quantityTitle="OF cargadas:"
    quantity="{{ $totalOf }}"
    buttonRoute="{{ route('fechas_of.create') }}"
    buttonText="Abrir hoja masiva"
/>
@stop

@section('content')
<div class="container-fluid">
    @include('components.swal-session')

    <div class="alert alert-info mt-3" role="alert">
        Esta grilla toma las OF desde <strong>pedido_cliente</strong>. Cada pedido nuevo genera automaticamente su fila base en <strong>fechas_of</strong>.
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalOf, 0, ',', '.') }}</h3>
                    <p>Total de OF</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($ofConTiempo, 0, ',', '.') }}</h3>
                    <p>OF con tiempo cargado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($ofPendientes, 0, ',', '.') }}</h3>
                    <p>OF pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:8px;">
                <div class="text-muted small">
                    La columna <strong>Cant.Seg x Pieza</strong> corresponde al campo <strong>Tiempo_Seg</strong>.
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFilters">Limpiar filtros</button>
            </div>

            <div class="table-responsive">
                <table id="fechas_of_table" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nro OF</th>
                            <th>Codigo</th>
                            <th>Descripcion</th>
                            <th>Categoria</th>
                            <th>Maquina</th>
                            <th>Programa H1</th>
                            <th>Programa H2</th>
                            <th>Inicio P.A.P</th>
                            <th>Hora Inicio P.A.P</th>
                            <th>Fin P.A.P</th>
                            <th>Hora Fin P.A.P</th>
                            <th>Inicio Produccion</th>
                            <th>Fin Produccion</th>
                            <th>Tiempo de Pieza</th>
                            <th>Cant.Seg x Pieza</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_nro_of" class="form-control filtro-texto" placeholder="OF"></th>
                            <th><input type="text" id="filtro_producto" class="form-control filtro-texto" placeholder="Codigo o descripcion"></th>
                            <th></th>
                            <th>
                                <select id="filtro_categoria" class="form-control filtro-select">
                                    <option value="">Todas</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria }}">{{ $categoria }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th>
                                <select id="filtro_maquina" class="form-control filtro-select">
                                    <option value="">Todas</option>
                                    @foreach ($maquinas as $maquina)
                                        <option value="{{ $maquina }}">{{ $maquina }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th>
                                <select id="filtro_programa_h1" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                    @foreach ($programasH1 as $programa)
                                        <option value="{{ $programa }}">{{ $programa }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th>
                                <select id="filtro_programa_h2" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                    @foreach ($programasH2 as $programa)
                                        <option value="{{ $programa }}">{{ $programa }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><input type="text" id="filtro_tiempo_seg" class="form-control filtro-texto" placeholder="Seg"></th>
                            <th>
                                <select id="filtro_estado_carga" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                    <option value="completo">Completo</option>
                                    <option value="pendiente">Pendiente</option>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/summary-boxes.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fechas_of_index.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
$(document).ready(function () {
    const table = $('#fechas_of_table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        autoWidth: false,
        scrollX: true,
        scrollY: '60vh',
        scrollCollapse: true,
        pageLength: 25,
        orderCellsTop: true,
        ajax: {
            url: "{{ route('fechas_of.data') }}",
            data: function (d) {
                d.filtro_nro_of = $('#filtro_nro_of').val();
                d.filtro_producto = $('#filtro_producto').val();
                d.filtro_categoria = $('#filtro_categoria').val();
                d.filtro_maquina = $('#filtro_maquina').val();
                d.filtro_programa_h1 = $('#filtro_programa_h1').val();
                d.filtro_programa_h2 = $('#filtro_programa_h2').val();
                d.filtro_tiempo_seg = $('#filtro_tiempo_seg').val();
                d.filtro_estado_carga = $('#filtro_estado_carga').val();
            }
        },
        columns: [
            { data: 'Nro_OF', name: 'Nro_OF' },
            { data: 'Prod_Codigo', name: 'Prod_Codigo', orderable: false },
            { data: 'Prod_Descripcion', name: 'Prod_Descripcion', orderable: false },
            { data: 'Nombre_Categoria', name: 'Nombre_Categoria', orderable: false },
            { data: 'Nro_Maquina', name: 'Nro_Maquina', orderable: false },
            { data: 'Nro_Programa_H1', name: 'Nro_Programa_H1', orderable: false },
            { data: 'Nro_Programa_H2', name: 'Nro_Programa_H2', orderable: false },
            { data: 'Inicio_PAP', name: 'Inicio_PAP', orderable: false },
            { data: 'Hora_Inicio_PAP', name: 'Hora_Inicio_PAP', orderable: false },
            { data: 'Fin_PAP', name: 'Fin_PAP', orderable: false },
            { data: 'Hora_Fin_PAP', name: 'Hora_Fin_PAP', orderable: false },
            { data: 'Inicio_OF', name: 'Inicio_OF', orderable: false },
            { data: 'Finalizacion_OF', name: 'Finalizacion_OF', orderable: false },
            { data: 'Tiempo_Pieza', name: 'Tiempo_Pieza', orderable: false },
            { data: 'Tiempo_Seg', name: 'Tiempo_Seg', orderable: false },
            { data: 'Estado_Carga', name: 'Estado_Carga', orderable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        language: {
            url: "{{ asset('Spanish.json') }}"
        }
    });

    $('#filtro_nro_of, #filtro_producto, #filtro_tiempo_seg').on('keyup change', function () {
        table.ajax.reload(null, false);
    });

    $('#filtro_categoria, #filtro_maquina, #filtro_programa_h1, #filtro_programa_h2, #filtro_estado_carga').on('change', function () {
        table.ajax.reload(null, false);
    });

    $('#clearFilters').on('click', function () {
        $('.filtro-texto').val('');
        $('.filtro-select').val('');
        table.ajax.reload(null, false);
    });

    $(document).on('click', '.js-limpiar-registro', function () {
        const id = $(this).data('id');

        SwalUtils.confirmDelete('Se limpiaran los tiempos y fechas cargados para esta OF.').then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/fechas_of/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    table.ajax.reload(null, false);
                    SwalUtils.deleted(response.message || 'Datos limpiados correctamente.');
                },
                error: function (xhr) {
                    SwalUtils.error(xhr.responseJSON?.message || 'No se pudieron limpiar los datos de la OF.');
                }
            });
        });
    });
});
</script>
@stop
