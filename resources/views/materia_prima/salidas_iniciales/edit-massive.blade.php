@extends('adminlte::page')

@section('title', 'Edicion Masiva - Salidas Iniciales MP')

@section('content_header')
    <div class="show-header">
        <h1>Edicion Masiva de Salidas Iniciales</h1>
        <p>Modifica rapidamente devoluciones al proveedor y el tratamiento historico o manual del stock segun el tipo de ingreso.</p>
        @if (($scope ?? 'without_movements') === 'without_movements')
            <p class="text-muted mb-0">Barras iniciales historicas = Stock Inicial - Devoluciones al Proveedor.</p>
        @else
            <p class="text-muted mb-0">Barras disponibles = Stock Inicial - Total OF + Ajuste de Stock. Los metros disponibles se recalculan segun ese saldo.</p>
        @endif
        @if (($scope ?? 'without_movements') === 'without_movements')
            <p class="text-muted mb-0">Vista filtrada: solo ingresos activos sin movimientos registrados en egresos OF.</p>
        @elseif (($scope ?? 'without_movements') === 'adjustments')
            <p class="text-muted mb-0">Vista filtrada: ingresos activos con movimientos operativos, para ajuste manual de stock.</p>
        @endif
    </div>
@stop

@section('content')
    @include('components.swal-session')

    <div class="container-fluid">
        @if (($scope ?? 'without_movements') === 'without_movements')
            <div class="alert alert-info">
                <strong>Filtro aplicado:</strong> se muestran <strong>{{ number_format((int) ($filteredCount ?? 0), 0, ',', '.') }}</strong> ingresos activos que nunca tuvieron movimientos en <code>mp_salidas</code>. Aqui se conserva solo el consumo historico inicial y no se usa <code>Ajuste_Stock</code>.
            </div>
        @elseif (($scope ?? 'without_movements') === 'adjustments')
            <div class="alert alert-warning">
                <strong>Modo ajuste:</strong> se muestran <strong>{{ number_format((int) ($filteredCount ?? 0), 0, ',', '.') }}</strong> ingresos activos con movimientos en <code>mp_salidas</code>. Aqui el foco es corregir <code>Ajuste_Stock</code> viendo el impacto directo en <code>Barras Disponibles</code> y <code>Metros Disponibles</code>.
            </div>
        @endif

        @if (($scope ?? 'without_movements') === 'adjustments' && !empty($selectedSalida))
            <div class="card mb-3">
                <div class="card-header py-2">
                    <strong class="js-summary-title">Resumen del ingreso {{ number_format((int) ($selectedSalida->ingresoMp->Nro_Ingreso_MP ?? 0), 0, ',', '.') }}</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 col-6 mb-3">
                            <div class="small-box bg-info mb-0">
                                <div class="inner">
                                    <h3 class="js-summary-solicitadas">{{ number_format((int) ($selectedSalida->Planner_Barras_Solicitadas_OF ?? 0), 0, ',', '.') }}</h3>
                                    <p>Barras solicitadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="small-box bg-primary mb-0">
                                <div class="inner">
                                    <h3 class="js-summary-preparadas">{{ number_format((int) ($selectedSalida->Planner_Barras_Preparadas_OF ?? 0), 0, ',', '.') }}</h3>
                                    <p>Barras preparadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="small-box bg-warning mb-0">
                                <div class="inner">
                                    <h3 class="js-summary-adicionales">{{ number_format((int) ($selectedSalida->Planner_Cantidad_Adicionales_Stock ?? 0), 0, ',', '.') }}</h3>
                                    <p>Adicionales</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="small-box bg-secondary mb-0">
                                <div class="inner">
                                    <h3 class="js-summary-devoluciones">{{ number_format((int) ($selectedSalida->Planner_Cantidad_Devoluciones_Stock ?? 0), 0, ',', '.') }}</h3>
                                    <p>Devoluciones</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="small-box bg-success mb-0">
                                <div class="inner">
                                    <h3 class="js-summary-barras-disponibles">{{ number_format((int) ($selectedSalida->Planner_Barras_Disponibles ?? 0), 0, ',', '.') }}</h3>
                                    <p>Barras disponibles</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="small-box bg-teal mb-0">
                                <div class="inner">
                                    <h3 class="js-summary-mts-disponibles">{{ number_format((float) ($selectedSalida->Planner_Mts_Disponibles ?? 0), 2, ',', '.') }}</h3>
                                    <p>Metros disponibles</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted mb-0">
                        Consumo total OF: <strong class="js-summary-total-of">{{ number_format((int) ($selectedSalida->Planner_Cantidad_Total_OF ?? 0), 0, ',', '.') }}</strong>
                    </p>
                </div>
            </div>
        @endif

        <form action="{{ route('mp_salidas_iniciales.updateMassive') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_to" value="{{ $returnTo ?? 'salidas_iniciales' }}">
            <input type="hidden" name="return_selected" value="{{ $returnSelected ?? $selectedId ?? '' }}">
            <input type="hidden" name="scope" value="{{ $scope ?? 'without_movements' }}">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                    <span class="font-weight-bold">Ajuste masivo</span>
                    <div class="d-flex flex-wrap" style="gap:8px;">
                        @if (($scope ?? 'without_movements') === 'without_movements')
                            <a href="{{ route('mp_salidas_iniciales.editMassive', ['return_to' => $returnTo ?? 'salidas_iniciales', 'return_selected' => $returnSelected ?? $selectedId ?? null, 'scope' => 'adjustments']) }}" class="btn btn-outline-warning">Ver ajustes</a>
                            <a href="{{ route('mp_salidas_iniciales.editMassive', ['return_to' => $returnTo ?? 'salidas_iniciales', 'return_selected' => $returnSelected ?? $selectedId ?? null, 'scope' => 'all']) }}" class="btn btn-outline-secondary">Ver todos</a>
                        @elseif (($scope ?? 'without_movements') === 'adjustments')
                            <a href="{{ route('mp_salidas_iniciales.editMassive', ['return_to' => $returnTo ?? 'salidas_iniciales', 'return_selected' => $returnSelected ?? $selectedId ?? null, 'scope' => 'without_movements']) }}" class="btn btn-outline-info">Ver historicos</a>
                            <a href="{{ route('mp_salidas_iniciales.editMassive', ['return_to' => $returnTo ?? 'salidas_iniciales', 'return_selected' => $returnSelected ?? $selectedId ?? null, 'scope' => 'all']) }}" class="btn btn-outline-secondary">Ver todos</a>
                        @else
                            <a href="{{ route('mp_salidas_iniciales.editMassive', ['return_to' => $returnTo ?? 'salidas_iniciales', 'return_selected' => $returnSelected ?? $selectedId ?? null, 'scope' => 'without_movements']) }}" class="btn btn-outline-info">Ver solo sin movimientos</a>
                            <a href="{{ route('mp_salidas_iniciales.editMassive', ['return_to' => $returnTo ?? 'salidas_iniciales', 'return_selected' => $returnSelected ?? $selectedId ?? null, 'scope' => 'adjustments']) }}" class="btn btn-outline-warning">Ver ajustes</a>
                        @endif
                        <button type="submit" class="btn btn-primary">Guardar cambios masivos</button>
                    </div>
                </div>
                <div class="card-body p-3 table-responsive table-massive-wrapper">
                    <table class="table table-bordered table-striped table-hover" id="tabla_edicion_masiva_salidas">
                        <thead>
                            <tr>
                                <th>Nro Ingreso MP</th>
                                <th>Cant. Unid.</th>
                                <th>Longitud x Un.(MP)</th>
                                <th>Stock Inicial</th>
                                @if (($scope ?? 'without_movements') === 'without_movements')
                                    <th>Devoluciones al Proveedor</th>
                                @endif
                                <th>{{ ($scope ?? 'without_movements') === 'without_movements' ? 'Barras Iniciales Historicas' : 'Ajuste de Stock' }}</th>
                                <th>{{ ($scope ?? 'without_movements') === 'without_movements' ? 'Consumo Historico' : 'Barras Disponibles' }}</th>
                                <th>{{ ($scope ?? 'without_movements') === 'without_movements' ? 'Mts. Historicos' : 'Metros Disponibles' }}</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salidas as $salidaInicial)
                                @php
                                    $ingreso = $salidaInicial->ingresoMp;
                                @endphp
                                <tr
                                    data-row-id="{{ $salidaInicial->Id_Ingreso_MP }}"
                                    data-nro-ingreso="{{ (int) ($ingreso->Nro_Ingreso_MP ?? 0) }}"
                                    data-solicitadas="{{ (int) ($salidaInicial->Planner_Barras_Solicitadas_OF ?? 0) }}"
                                    data-preparadas="{{ (int) ($salidaInicial->Planner_Barras_Preparadas_OF ?? 0) }}"
                                    data-adicionales="{{ (int) ($salidaInicial->Planner_Cantidad_Adicionales_Stock ?? 0) }}"
                                    data-devoluciones="{{ (int) ($salidaInicial->Planner_Cantidad_Devoluciones_Stock ?? 0) }}"
                                    data-total-of="{{ (int) ($salidaInicial->Planner_Cantidad_Total_OF ?? 0) }}"
                                    data-barras-iniciales="{{ (int) ($salidaInicial->Planner_Barras_Ingresos_Iniciales ?? 0) }}"
                                >
                                    <td>
                                        {{ $ingreso->Nro_Ingreso_MP ?? '-' }}
                                        <input type="hidden" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][Stock_Inicial]" value="{{ (int) ($salidaInicial->Stock_Inicial ?? 0) }}" class="js-stock-inicial-hidden">
                                        <input type="hidden" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][reg_Status]" value="{{ (int) ($salidaInicial->reg_Status ?? 1) }}">
                                    </td>
                                    <td>{{ number_format((int) ($salidaInicial->Unidades_Ingresadas ?? 0), 0, ',', '.') }}</td>
                                    <td class="js-longitud" data-longitud="{{ (float) ($salidaInicial->Longitud_Unidad_Calculada ?? 0) }}">{{ number_format((float) ($salidaInicial->Longitud_Unidad_Calculada ?? 0), 2, ',', '.') }}</td>
                                    <td class="js-stock-inicial-text">{{ number_format((int) ($salidaInicial->Stock_Inicial_Calculado ?? 0), 0, ',', '.') }}</td>
                                    @if (($scope ?? 'without_movements') === 'without_movements')
                                        <td>
                                            <input type="number" class="form-control text-center js-devolucion" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][Devoluciones_Proveedor]" value="{{ (int) ($salidaInicial->Devoluciones_Proveedor ?? 0) }}" min="0" step="1">
                                        </td>
                                    @else
                                        <input type="hidden" class="js-devolucion" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][Devoluciones_Proveedor]" value="{{ (int) ($salidaInicial->Devoluciones_Proveedor ?? 0) }}">
                                    @endif
                                    <td>
                                        @if (($scope ?? 'without_movements') === 'without_movements')
                                            <input type="hidden" class="js-ajuste" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][Ajuste_Stock]" value="0">
                                            <span class="js-ajuste-historico">{{ number_format((int) ($salidaInicial->Total_Salidas_Calculadas ?? 0), 0, ',', '.') }}</span>
                                        @else
                                            <input type="number" class="form-control text-center js-ajuste" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][Ajuste_Stock]" value="{{ (int) ($salidaInicial->Ajuste_Stock ?? 0) }}" step="1">
                                        @endif
                                    </td>
                                    <td class="js-total-salidas">{{ number_format((float) (($scope ?? 'without_movements') === 'without_movements' ? ($salidaInicial->Total_Salidas_Calculadas ?? 0) : ($salidaInicial->Planner_Barras_Disponibles ?? 0)), 0, ',', '.') }}</td>
                                    <td class="js-total-mts">{{ number_format((float) (($scope ?? 'without_movements') === 'without_movements' ? ($salidaInicial->Total_mm_Utilizados_Calculados ?? 0) : ($salidaInicial->Planner_Mts_Disponibles ?? 0)), 2, ',', '.') }}</td>
                                    <td>{{ (int) ($salidaInicial->reg_Status ?? 1) === 1 ? 'Activo' : 'Inactivo' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ ($returnTo ?? 'salidas_iniciales') === 'stock_mp' ? route('mp_stock.index') : route('mp_salidas_iniciales.index') }}" class="btn btn-secondary">Volver</a>
                    <button type="submit" class="btn btn-primary">Guardar cambios masivos</button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <style>
        .table-massive-wrapper {
            max-height: calc(100vh - 250px);
            overflow: auto;
        }

        #tabla_edicion_masiva_salidas {
            margin-bottom: 0;
        }

        #tabla_edicion_masiva_salidas td,
        #tabla_edicion_masiva_salidas th {
            vertical-align: middle;
            white-space: nowrap;
            text-align: center;
        }

        #tabla_edicion_masiva_salidas thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #fff;
            box-shadow: inset 0 -1px 0 #dee2e6;
        }

        #tabla_edicion_masiva_salidas td .form-control {
            text-align: center;
        }

        #tabla_edicion_masiva_salidas tbody tr.is-active-row td {
            background-color: #fff3cd !important;
            color: #856404;
            font-weight: 700;
        }
    </style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let activeRowId = @json((int) ($selectedId ?? 0));
    const currentScope = @json($scope ?? 'without_movements');
    const tableWrapper = document.querySelector('.table-massive-wrapper');
    const returnSelectedInput = document.querySelector('input[name="return_selected"]');

    function parseNumber(value) {
        const normalized = String(value ?? '').replace(/\./g, '').replace(',', '.');
        const parsed = Number.parseFloat(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function formatInt(value) {
        return new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 }).format(value);
    }

    function formatDecimal(value) {
        return new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
    }

    function updateSummaryFromRow(row) {
        if (currentScope !== 'adjustments' || !row) {
            return;
        }

        const titleNode = document.querySelector('.js-summary-title');
        const solicitadasNode = document.querySelector('.js-summary-solicitadas');
        const preparadasNode = document.querySelector('.js-summary-preparadas');
        const adicionalesNode = document.querySelector('.js-summary-adicionales');
        const devolucionesNode = document.querySelector('.js-summary-devoluciones');
        const totalOfNode = document.querySelector('.js-summary-total-of');
        const barrasResumen = document.querySelector('.js-summary-barras-disponibles');
        const mtsResumen = document.querySelector('.js-summary-mts-disponibles');
        const barrasRowValue = row.querySelector('.js-total-salidas')?.textContent ?? '0';
        const mtsRowValue = row.querySelector('.js-total-mts')?.textContent ?? '0,00';

        if (titleNode) titleNode.textContent = 'Resumen del ingreso ' + formatInt(Number(row.dataset.nroIngreso || 0));
        if (solicitadasNode) solicitadasNode.textContent = formatInt(Number(row.dataset.solicitadas || 0));
        if (preparadasNode) preparadasNode.textContent = formatInt(Number(row.dataset.preparadas || 0));
        if (adicionalesNode) adicionalesNode.textContent = formatInt(Number(row.dataset.adicionales || 0));
        if (devolucionesNode) devolucionesNode.textContent = formatInt(Number(row.dataset.devoluciones || 0));
        if (totalOfNode) totalOfNode.textContent = formatInt(Number(row.dataset.totalOf || 0));
        if (barrasResumen) barrasResumen.textContent = barrasRowValue;
        if (mtsResumen) mtsResumen.textContent = mtsRowValue;
    }

    function setActiveRow(row) {
        if (!row) {
            return;
        }

        document.querySelectorAll('#tabla_edicion_masiva_salidas tbody tr').forEach(function (candidate) {
            candidate.classList.toggle('is-active-row', candidate === row);
        });

        activeRowId = Number(row.dataset.rowId || 0);

        if (returnSelectedInput && activeRowId > 0) {
            returnSelectedInput.value = activeRowId;
        }

        updateSummaryFromRow(row);
    }

    function recalculateRow(row) {
        const stockInicial = parseNumber(row.querySelector('.js-stock-inicial-hidden')?.value || 0);
        const devolucion = parseNumber(row.querySelector('.js-devolucion')?.value || 0);
        const ajusteInput = row.querySelector('.js-ajuste');
        const longitud = parseNumber(row.querySelector('.js-longitud')?.dataset.longitud || 0);
        const totalOf = parseNumber(row.dataset.totalOf || 0);
        const barrasIniciales = parseNumber(row.dataset.barrasIniciales || 0);
        let ajuste = parseNumber(ajusteInput?.value || 0);
        let totalSalidas = 0;

        if (currentScope === 'without_movements') {
            totalSalidas = Math.max(0, stockInicial - devolucion);
            ajuste = 0;
            if (ajusteInput) {
                ajusteInput.value = 0;
            }
        } else {
            totalSalidas = stockInicial - devolucion - barrasIniciales - totalOf + ajuste;
        }

        const totalMts = totalSalidas * longitud;

        const totalSalidasNode = row.querySelector('.js-total-salidas');
        const totalMtsNode = row.querySelector('.js-total-mts');
        const ajusteHistoricoNode = row.querySelector('.js-ajuste-historico');

        if (totalSalidasNode) totalSalidasNode.textContent = formatInt(totalSalidas);
        if (totalMtsNode) totalMtsNode.textContent = formatDecimal(totalMts);
        if (ajusteHistoricoNode) ajusteHistoricoNode.textContent = formatInt(totalSalidas);

        if (currentScope === 'adjustments' && Number(row.dataset.rowId || 0) === activeRowId) {
            updateSummaryFromRow(row);
        }
    }

    document.querySelectorAll('#tabla_edicion_masiva_salidas tbody tr').forEach(function (row) {
        row.querySelectorAll('.js-devolucion, .js-ajuste').forEach(function (input) {
            input.addEventListener('input', function () {
                setActiveRow(row);
                recalculateRow(row);
            });
            input.addEventListener('focus', function () {
                setActiveRow(row);
            });
        });
        row.addEventListener('click', function () {
            setActiveRow(row);
        });
        recalculateRow(row);
    });

    if (activeRowId > 0 && tableWrapper) {
        const selectedRow = document.querySelector(`#tabla_edicion_masiva_salidas tbody tr[data-row-id="${activeRowId}"]`);

        if (selectedRow) {
            setActiveRow(selectedRow);
            selectedRow.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            const firstEditableInput = selectedRow.querySelector('.js-ajuste, .js-devolucion');
            if (firstEditableInput) {
                window.setTimeout(function () {
                    firstEditableInput.focus();
                    firstEditableInput.select();
                }, 250);
            }
        }
    }
});
</script>
@stop
