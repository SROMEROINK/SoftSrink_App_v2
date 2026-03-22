@extends('adminlte::page')

@section('title', 'Egreso de Materia Prima')

@section('content_header')
<h1 class="text-center">Egreso de Materia Prima</h1>
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
            @if($csvHistoricoDisponible ?? false)
                @if($puedeImportarHistorico ?? false)
                    <form method="POST" action="{{ route('mp_egresos.importHistoricCsv') }}" class="d-inline-block" onsubmit="return confirm('Se importara el CSV historico de pedidos y entregas de MP. Deseas continuar?');">
                        @csrf
                        <button type="submit" class="btn btn-warning">Importar CSV historico</button>
                    </form>
                @else
                    <button type="button" class="btn btn-warning" disabled>CSV historico importado</button>
                @endif
            @endif
            <a href="{{ route('mp_egresos.create') }}" class="btn btn-success">Registrar salida</a>
            <a href="{{ route('mp_egresos.createMassive') }}" class="btn btn-primary">Carga masiva MP</a>
            <a href="{{ route('mp_egresos.deleted') }}" class="btn btn-secondary">Ver eliminados</a>
        </div>
    </div>

    @include('partials.navigation')

    <div class="alert alert-info">
        <strong>Egreso de materia prima:</strong>
        esta vista registra la entrega operativa de calidad hacia produccion para cada OF definida en pedido de materia prima.
        @if($historicoImportado ?? false)
            <br><strong>CSV historico:</strong> la importacion ya fue ejecutada y el boton quedo bloqueado para evitar duplicados.
        @endif
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalSalidas, 0, ',', '.') }}</h3>
                    <p>Total de salidas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($salidasActivas, 0, ',', '.') }}</h3>
                    <p>Salidas activas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($salidasPendientes, 0, ',', '.') }}</h3>
                    <p>OF pendientes de salida</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla_mp_egresos" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nro OF</th>
                            <th>Producto</th>
                            <th>Ingreso MP</th>
                            <th>Codigo MP</th>
                            <th>Maquina</th>
                            <th>Barras Req.</th>
                            <th>Barras Sol.</th>
                            <th>Barras Prep.</th>
                            <th>Total Salidas</th>
                            <th>Mts Utilizados</th>
                            <th>Fecha Planif.</th>
                            <th>Resp. Planif.</th>
                            <th>Pedido MP Nro</th>
                            <th>Fecha Entrega</th>
                            <th>Alerta</th>
                            <th>Resp. Entrega</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_of" class="form-control filtro-texto" placeholder="Filtrar OF"></th>
                            <th><input type="text" id="filtro_producto" class="form-control filtro-texto" placeholder="Filtrar producto"></th>
                            <th><input type="text" id="filtro_ingreso_mp" class="form-control filtro-texto" placeholder="Filtrar ingreso"></th>
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
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <select id="filtro_estado_salida" class="form-control filtro-select">
                                    <option value="">Todos</option>
                                    <option value="PENDIENTE SALIDA">Pendiente</option>
                                    <option value="SALIDA CARGADA">Cargada</option>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_egreso_index.css') }}">
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.1/js/dataTables.fixedHeader.min.js"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const navEntries = window.performance && window.performance.getEntriesByType ? window.performance.getEntriesByType('navigation') : [];
    const isBackForward = navEntries.length > 0 && navEntries[0].type === 'back_forward';

    if (!isBackForward) {
        @if(session('success'))
            @php($flashSuccess = session('success'))
            @if(\Illuminate\Support\Str::contains(strtolower($flashSuccess), 'actualizada'))
                SwalUtils.updated(@json($flashSuccess));
            @elseif(\Illuminate\Support\Str::contains(strtolower($flashSuccess), 'restaurada'))
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
    } else {
        document.querySelectorAll('.flash-alert').forEach(function (element) {
            element.remove();
        });
    }

    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) {
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.close();
        }

        document.querySelectorAll('.flash-alert').forEach(function (element) {
            element.remove();
        });
    });

    const table = $('#tabla_mp_egresos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('mp_egresos.data') }}",
            data: function (d) {
                d.filtro_of = $('#filtro_of').val();
                d.filtro_producto = $('#filtro_producto').val();
                d.filtro_ingreso_mp = $('#filtro_ingreso_mp').val();
                d.filtro_estado_salida = $('#filtro_estado_salida').val();
            }
        },
        order: [[0, 'asc']],
        fixedHeader: true,
        pageLength: 25,
        scrollX: true,
        columns: [
            { data: 'Nro_OF', name: 'p.Nro_OF' },
            { data: 'Prod_Codigo', name: 'prod.Prod_Codigo' },
            { data: 'Nro_Ingreso_MP', name: 'pm.Nro_Ingreso_MP' },
            { data: 'Codigo_MP', name: 'pm.Codigo_MP' },
            { data: 'Nro_Maquina', name: 'pm.Nro_Maquina' },
            { data: 'Cant_Barras_Requeridas', name: 'pm.Cant_Barras_MP' },
            { data: 'Cantidad_Unidades_MP', name: 's.Cantidad_Unidades_MP', defaultContent: '' },
            { data: 'Cantidad_Unidades_MP_Preparadas', name: 's.Cantidad_Unidades_MP_Preparadas', defaultContent: '' },
            { data: 'Total_Salidas_MP', name: 's.Total_Salidas_MP', defaultContent: '' },
            { data: 'Total_Mtros_Utilizados', name: 's.Total_Mtros_Utilizados', defaultContent: '' },
            { data: 'Fecha_del_Pedido_Produccion', name: 'pm.Fecha_Planificacion', defaultContent: '' },
            { data: 'Responsable_Pedido_Produccion', name: 'pm.Responsable_Planificacion', defaultContent: '' },
            { data: 'Nro_Pedido_MP', name: 'pm.Pedido_Material_Nro', defaultContent: '' },
            { data: 'Fecha_de_Entrega_Pedido_Calidad', name: 's.Fecha_de_Entrega_Pedido_Calidad', defaultContent: '' },
            { data: 'Alerta_Calidad', name: 'Alerta_Calidad', orderable: false, searchable: false, defaultContent: '' },
            { data: 'Responsable_de_entrega_Calidad', name: 's.Responsable_de_entrega_Calidad', defaultContent: '' },
            { data: 'Estado_Salida', name: 'Estado_Salida', orderable: false },
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

    $('.filtro-texto').on('keyup change', function () {
        table.ajax.reload(null, false);
    });

    $('.filtro-select').on('change', function () {
        table.ajax.reload(null, false);
    });

    $(document).on('click', '.btn-delete-egreso', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Eliminar salida de materia prima',
            text: 'La salida quedara en eliminados y podras restaurarla.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ url('mp_egresos') }}/" + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function () {
                    SwalUtils.deleted('La salida de materia prima fue eliminada correctamente.');
                    table.ajax.reload(null, false);
                },
                error: function () {
                    SwalUtils.error('No se pudo eliminar la salida de materia prima.');
                }
            });
        });
    });
});
</script>
@stop


