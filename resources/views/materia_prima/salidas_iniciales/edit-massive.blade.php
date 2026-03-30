@extends('adminlte::page')

@section('title', 'Edicion Masiva - Salidas Iniciales MP')

@section('content_header')
    <div class="show-header">
        <h1>Edicion Masiva de Salidas Iniciales</h1>
        <p>Modifica rapidamente devoluciones al proveedor y diferencia de stock para varios ingresos a la vez.</p>
    </div>
@stop

@section('content')
    @include('components.swal-session')

    <div class="container-fluid">
        <form action="{{ route('mp_salidas_iniciales.updateMassive') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_to" value="{{ $returnTo ?? 'salidas_iniciales' }}">

            <div class="card">
                <div class="card-body p-3 table-responsive table-massive-wrapper">
                    <table class="table table-bordered table-striped table-hover" id="tabla_edicion_masiva_salidas">
                        <thead>
                            <tr>
                                <th>Nro Ingreso MP</th>
                                <th>Cant. Unid.</th>
                                <th>Longitud x Un.(MP)</th>
                                <th>Stock Inicial</th>
                                <th>Devoluciones al Proveedor</th>
                                <th>Diferencia de Stock</th>
                                <th>Salidas Final</th>
                                <th>Mts. Totales</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salidas as $salidaInicial)
                                @php
                                    $ingreso = $salidaInicial->ingresoMp;
                                    $highlight = (int) ($selectedId ?? 0) === (int) $salidaInicial->Id_Ingreso_MP;
                                @endphp
                                <tr @class(['table-warning' => $highlight]) data-row-id="{{ $salidaInicial->Id_Ingreso_MP }}">
                                    <td>
                                        {{ $ingreso->Nro_Ingreso_MP ?? '-' }}
                                        <input type="hidden" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][Stock_Inicial]" value="{{ (int) ($salidaInicial->Stock_Inicial ?? 0) }}" class="js-stock-inicial-hidden">
                                        <input type="hidden" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][reg_Status]" value="{{ (int) ($salidaInicial->reg_Status ?? 1) }}">
                                    </td>
                                    <td>{{ number_format((int) ($salidaInicial->Unidades_Ingresadas ?? 0), 0, ',', '.') }}</td>
                                    <td class="js-longitud" data-longitud="{{ (float) ($salidaInicial->Longitud_Unidad_Calculada ?? 0) }}">{{ number_format((float) ($salidaInicial->Longitud_Unidad_Calculada ?? 0), 2, ',', '.') }}</td>
                                    <td class="js-stock-inicial-text">{{ number_format((int) ($salidaInicial->Stock_Inicial_Calculado ?? 0), 0, ',', '.') }}</td>
                                    <td>
                                        <input type="number" class="form-control text-center js-devolucion" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][Devoluciones_Proveedor]" value="{{ (int) ($salidaInicial->Devoluciones_Proveedor ?? 0) }}" min="0" step="1">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control text-center js-ajuste" name="rows[{{ $salidaInicial->Id_Ingreso_MP }}][Ajuste_Stock]" value="{{ (int) ($salidaInicial->Ajuste_Stock ?? 0) }}" step="1">
                                    </td>
                                    <td class="js-total-salidas">{{ number_format((int) ($salidaInicial->Total_Salidas_Calculadas ?? 0), 0, ',', '.') }}</td>
                                    <td class="js-total-mts">{{ number_format((float) ($salidaInicial->Total_mm_Utilizados_Calculados ?? 0), 2, ',', '.') }}</td>
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
    </style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
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

    function recalculateRow(row) {
        const stockInicial = parseNumber(row.querySelector('.js-stock-inicial-hidden')?.value || 0);
        const devolucion = parseNumber(row.querySelector('.js-devolucion')?.value || 0);
        const ajuste = parseNumber(row.querySelector('.js-ajuste')?.value || 0);
        const longitud = parseNumber(row.querySelector('.js-longitud')?.dataset.longitud || 0);
        const totalSalidas = stockInicial - devolucion + ajuste;
        const totalMts = totalSalidas * longitud;

        const totalSalidasNode = row.querySelector('.js-total-salidas');
        const totalMtsNode = row.querySelector('.js-total-mts');

        if (totalSalidasNode) totalSalidasNode.textContent = formatInt(totalSalidas);
        if (totalMtsNode) totalMtsNode.textContent = formatDecimal(totalMts);
    }

    document.querySelectorAll('#tabla_edicion_masiva_salidas tbody tr').forEach(function (row) {
        row.querySelectorAll('.js-devolucion, .js-ajuste').forEach(function (input) {
            input.addEventListener('input', function () {
                recalculateRow(row);
            });
        });
        recalculateRow(row);
    });
});
</script>
@stop