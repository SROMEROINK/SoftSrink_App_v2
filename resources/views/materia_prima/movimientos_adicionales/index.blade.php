@extends('adminlte::page')

@section('title', 'Movimientos Adicionales de MP')

@section('content_header')
    <h1 class="text-center">Movimientos Adicionales de Materia Prima</h1>
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

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-end gap-2 flex-wrap">
            <form action="{{ route('mp_movimientos_adicionales.importCsv') }}" method="POST" class="d-inline-block mr-2">
                @csrf
                <button type="submit" class="btn btn-warning" {{ $puedeImportarHistorico ? '' : 'disabled' }}>Importar CSV historico</button>
            </form>
            <a href="{{ route('mp_movimientos_adicionales.create') }}" class="btn btn-success">Registrar movimiento</a>
            <a href="{{ route('mp_movimientos_adicionales.deleted') }}" class="btn btn-secondary">Ver eliminados</a>
        </div>
    </div>

    @include('partials.navigation')

    <div class="alert alert-info movimiento-stock-alert">
        <strong>Movimientos adicionales y devoluciones de MP:</strong>
        esta vista concentra los movimientos historicos y futuros que ajustan el stock mas alla de las salidas iniciales y las entregas operativas de calidad.
        @if($csvDisponible)
            @if($puedeImportarHistorico)
                El CSV historico esta disponible para importacion inicial.
            @else
                La importacion historica ya fue ejecutada y el boton queda bloqueado para evitar duplicados.
            @endif
        @else
            No se detecto el CSV historico en la ruta configurada.
        @endif
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalMovimientos, 0, ',', '.') }}</h3>
                    <p>Total de movimientos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($movimientosActivos, 0, ',', '.') }}</h3>
                    <p>Movimientos activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($netoMetros, 2, ',', '.') }}</h3>
                    <p>Metros netos acumulados</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla_movimientos_adicionales" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Nro Ingreso MP</th>
                            <th>Nro OF</th>
                            <th>Codigo Producto</th>
                            <th>Nro Maquina</th>
                            <th>Tipo</th>
                            <th>Adicionales</th>
                            <th>Devoluciones</th>
                            <th>Longitud x Un.</th>
                            <th>Metros Netos</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th></th>
                            <th><input type="text" id="filtro_ingreso" class="form-control filtro-texto" placeholder="Filtrar ingreso"></th>
                            <th><input type="text" id="filtro_of" class="form-control filtro-texto" placeholder="Filtrar OF"></th>
                            <th><input type="text" id="filtro_producto" class="form-control filtro-texto" placeholder="Filtrar producto"></th>
                            <th><input type="text" id="filtro_maquina" class="form-control filtro-texto" placeholder="Filtrar maquina"></th>
                            <th>
                                <select id="filtro_tipo" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                    <option value="ADICIONAL">Adicional</option>
                                    <option value="DEVOLUCION">Devolucion</option>
                                </select>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="mp-movimientos-totals-row">
                            <th>Totales filtrados</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
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
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_movimiento_adicional_index.css') }}">
    <style>
        #tabla_movimientos_adicionales tfoot th {
            position: sticky;
            bottom: 0;
            z-index: 2;
            background-color: #e8f4ff;
            color: #12344d;
            font-weight: 700;
            border-top: 2px solid #9fc5e8;
            white-space: nowrap;
            text-align: center;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.4.1/js/dataTables.fixedHeader.min.js"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script>
        const shouldReplayFlash = !window.performance || !window.performance.getEntriesByType || !window.performance.getEntriesByType('navigation').length || window.performance.getEntriesByType('navigation')[0].type !== 'back_forward';

        if (shouldReplayFlash) {
            @if(session('success'))
                @php($flashSuccess = session('success'))
                @if(\Illuminate\Support\Str::contains(strtolower($flashSuccess), 'actualizado'))
                    SwalUtils.updated(@json($flashSuccess));
                @elseif(\Illuminate\Support\Str::contains(strtolower($flashSuccess), 'restaurado'))
                    SwalUtils.restored(@json($flashSuccess));
                @else
                    SwalUtils.created(@json($flashSuccess));
                @endif
            @endif

            @if(session('warning'))
                SwalUtils.noChanges(@json(session('warning')));
            @endif

            @if(session('error'))
                SwalUtils.error(@json(session('error')));
            @endif
        }

        window.addEventListener('pageshow', function (event) {
            const fromHistory = event.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation').length && window.performance.getEntriesByType('navigation')[0].type === 'back_forward');

            if (!fromHistory) {
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.close();
            }

            document.querySelectorAll('.flash-alert').forEach(function (element) {
                element.remove();
            });
        });
    </script>
    <script>
        $(function () {
            const table = $('#tabla_movimientos_adicionales').DataTable({
                processing: true,
                serverSide: true,
                scrollY: '60vh',
                scrollCollapse: true,
                ajax: {
                    url: "{{ route('mp_movimientos_adicionales.data') }}",
                    data: function (d) {
                        d.filtro_ingreso = $('#filtro_ingreso').val();
                        d.filtro_of = $('#filtro_of').val();
                        d.filtro_producto = $('#filtro_producto').val();
                        d.filtro_maquina = $('#filtro_maquina').val();
                        d.filtro_tipo = $('#filtro_tipo').val();
                    }
                },
                order: [[0, 'desc'], [1, 'asc']],
                fixedHeader: true,
                pageLength: 25,
                scrollX: true,
                footerCallback: function () {
                    const api = this.api();
                    const totals = api.ajax.json()?.totals_filtered || {};
                    const renderCentered = function (value, decimals = 0) {
                        return '<div class="text-center w-100">' + new Intl.NumberFormat('es-AR', {
                            minimumFractionDigits: decimals,
                            maximumFractionDigits: decimals
                        }).format(value || 0) + '</div>';
                    };

                    $(api.column(6).footer()).html(renderCentered(totals.Cantidad_Adicionales, 0));
                    $(api.column(7).footer()).html(renderCentered(totals.Cantidad_Devoluciones, 0));
                    $(api.column(9).footer()).html(renderCentered(totals.Total_Mtros_Movimiento, 2));
                },
                columns: [
                    { data: 'Fecha_Movimiento', name: 'Fecha_Movimiento' },
                    { data: 'Nro_Ingreso_MP', name: 'Nro_Ingreso_MP' },
                    { data: 'Nro_OF', name: 'Nro_OF' },
                    { data: 'Codigo_Producto', name: 'Codigo_Producto' },
                    { data: 'Nro_Maquina', name: 'Nro_Maquina' },
                    { data: 'Tipo_Movimiento', name: 'Tipo_Movimiento', orderable: false, searchable: false },
                    { data: 'Cantidad_Adicionales', name: 'Cantidad_Adicionales', searchable: false },
                    { data: 'Cantidad_Devoluciones', name: 'Cantidad_Devoluciones', searchable: false },
                    { data: 'Longitud_Unidad_Mts', name: 'Longitud_Unidad_Mts', searchable: false },
                    { data: 'Total_Mtros_Movimiento', name: 'Total_Mtros_Movimiento', searchable: false },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
                ],
                language: {
                    lengthMenu: 'Mostrar _MENU_ registros',
                    zeroRecords: 'No se encontraron resultados',
                    info: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
                    infoEmpty: 'No hay registros disponibles',
                    infoFiltered: '(filtrado de _MAX_ registros totales)',
                    search: 'Buscar:',
                    paginate: { first: 'Primero', last: 'Ultimo', next: 'Siguiente', previous: 'Anterior' }
                }
            });

            $('.filtro-texto').on('keyup change', function () { table.ajax.reload(null, false); });
            $('.filtro-select').on('change', function () { table.ajax.reload(null, false); });

            $(document).on('click', '.btn-delete-mov-adicional', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Eliminar movimiento adicional',
                    text: 'El movimiento quedara en eliminados y podras restaurarlo.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ url('mp_movimientos_adicionales') }}/" + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function () {
                            SwalUtils.deleted('El movimiento adicional fue eliminado correctamente.');
                            table.ajax.reload(null, false);
                        },
                        error: function () {
                            SwalUtils.error('No se pudo eliminar el movimiento adicional.');
                        }
                    });
                });
            });
        });
    </script>
@stop

