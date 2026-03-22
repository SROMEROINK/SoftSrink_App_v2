@extends('adminlte::page')

@section('title', 'Salidas Iniciales de Materia Prima')

@section('content_header')
    <h1 class="text-center">Salidas Iniciales de Materia Prima</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-end gap-2">
            <a href="{{ route('mp_salidas_iniciales.create') }}" class="btn btn-success">Registrar ajuste</a>
            <a href="{{ route('mp_salidas_iniciales.deleted') }}" class="btn btn-secondary">Ver eliminados</a>
        </div>
    </div>

    <div class="alert alert-info ajuste-stock-alert">
        <strong>Ajuste historico de stock por ingreso MP:</strong>
        esta vista sirve para corregir diferencias previas a la trazabilidad completa del sistema.
        Cada ingreso de materia prima puede tener un unico ajuste inicial.
        @if($ajustesPendientes > 0 && $ultimoIngresoPendiente)
            Quedan <strong>{{ number_format($ajustesPendientes, 0, ',', '.') }}</strong> ingresos pendientes, comenzando desde el ingreso MP <strong>#{{ number_format($ultimoIngresoPendiente, 0, ',', '.') }}</strong>.
        @endif
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalAjustes, 0, ',', '.') }}</h3>
                    <p>Total de ajustes</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($ajustesActivos, 0, ',', '.') }}</h3>
                    <p>Ajustes activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($ajustesPendientes, 0, ',', '.') }}</h3>
                    <p>Ingresos pendientes</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla_salidas_iniciales" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nro Ingreso MP</th>
                            <th>Codigo MP</th>
                            <th>Materia Prima</th>
                            <th>Diametro MP</th>
                            <th>Unid. Ingreso</th>
                            <th>Preparadas</th>
                            <th>Adicionales</th>
                            <th>Devoluciones</th>
                            <th>Total Salidas</th>
                            <th>Stock Ajustado</th>
                            <th>Total Utilizado</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_ingreso" class="form-control filtro-texto" placeholder="Filtrar ingreso"></th>
                            <th><input type="text" id="filtro_codigo" class="form-control filtro-texto" placeholder="Filtrar codigo"></th>
                            <th>
                                <select id="filtro_materia" class="form-control filtro-select">
                                    <option value="">Todas</option>
                                </select>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <select id="filtro_estado" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                    <option value="PENDIENTE AJUSTE">Pendiente ajuste</option>
                                    <option value="AJUSTE CARGADO">Ajuste cargado</option>
                                </select>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.1/css/fixedHeader.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_salida_inicial_index.css') }}">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.4.1/js/dataTables.fixedHeader.min.js"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script>
        $(function () {
            const table = $('#tabla_salidas_iniciales').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('mp_salidas_iniciales.data') }}",
                    data: function (d) {
                        d.filtro_ingreso = $('#filtro_ingreso').val();
                        d.filtro_codigo = $('#filtro_codigo').val();
                        d.filtro_materia = $('#filtro_materia').val();
                        d.filtro_estado = $('#filtro_estado').val();
                    }
                },
                order: [[0, 'asc']],
                fixedHeader: true,
                pageLength: 25,
                scrollX: true,
                columns: [
                    { data: 'Nro_Ingreso_MP', name: 'i.Nro_Ingreso_MP' },
                    { data: 'Codigo_MP', name: 'i.Codigo_MP' },
                    { data: 'Materia_Prima', name: 'm.Nombre_Materia' },
                    { data: 'Diametro_MP', name: 'd.Valor_Diametro' },
                    { data: 'Unidades_Ingresadas', name: 'i.Unidades_MP', searchable: false },
                    { data: 'Cantidad_Unidades_MP_Preparadas', name: 'si.Cantidad_Unidades_MP_Preparadas', searchable: false },
                    { data: 'Cantidad_MP_Adicionales', name: 'si.Cantidad_MP_Adicionales', searchable: false },
                    { data: 'Devoluciones_Unidades_MP', name: 'si.Devoluciones_Unidades_MP', searchable: false },
                    { data: 'Total_Salidas_MP', name: 'si.Total_Salidas_MP', searchable: false },
                    { data: 'Total_Unidades', name: 'si.Total_Unidades', searchable: false },
                    { data: 'Total_mm_Utilizados', name: 'si.Total_mm_Utilizados', searchable: false },
                    { data: 'Estado_Ajuste', name: 'Estado_Ajuste', orderable: false },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
                ],
                language: {
                    lengthMenu: "Mostrar _MENU_ registros",
                    zeroRecords: "No se encontraron resultados",
                    info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    infoEmpty: "No hay registros disponibles",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    search: "Buscar:",
                    paginate: {
                        first: "Primero",
                        last: "Ultimo",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                },
                initComplete: function () {
                    const api = this.api();
                    const materias = new Set();
                    api.column(2).data().each(function (value) {
                        if (value) materias.add(value);
                    });
                    Array.from(materias).sort().forEach(function (materia) {
                        $('#filtro_materia').append(`<option value="${materia}">${materia}</option>`);
                    });
                }
            });

            $('.filtro-texto').on('keyup change', function () {
                table.ajax.reload(null, false);
            });

            $('.filtro-select').on('change', function () {
                table.ajax.reload(null, false);
            });

            $(document).on('click', '.btn-delete-salida-inicial', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Eliminar ajuste inicial',
                    text: 'El ajuste quedara en eliminados y podras restaurarlo.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ url('mp_salidas_iniciales') }}/" + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function () {
                            SwalBase.success('Eliminado', 'El ajuste inicial fue eliminado correctamente.');
                            table.ajax.reload(null, false);
                        },
                        error: function () {
                            SwalBase.error('Error', 'No se pudo eliminar el ajuste inicial.');
                        }
                    });
                });
            });
        });
    </script>
@stop
