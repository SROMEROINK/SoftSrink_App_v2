@extends('adminlte::page')

@section('title', 'Carga Masiva Fechas OF')

@section('content_header')
    <h1>Hoja masiva de Fechas OF</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="alert alert-secondary">
        Completa o corrige los tiempos de produccion por OF. El campo <strong>Cant.Seg x Pieza</strong> se calcula con la misma logica de Excel:
        <code>(INT(Tiempo de Pieza) * 60) + ((Tiempo de Pieza - INT(Tiempo de Pieza)) * 100)</code>
    </div>

    <div class="card mb-3">
        <div class="card-body fechas-of-toolbar">
            <div class="fechas-of-toolbar__group">
                <label for="filtro_of_masivo" class="mb-0">Filtrar OF</label>
                <input type="text" id="filtro_of_masivo" class="form-control" placeholder="Ej: 1396">
            </div>
            <div class="fechas-of-toolbar__group fechas-of-toolbar__group--counter">
                <span class="text-muted small">OF visibles</span>
                <strong id="contador_of_visibles">{{ $rows->count() }}</strong>
            </div>
            <div class="fechas-of-toolbar__actions">
                <button type="button" class="btn btn-outline-secondary" id="limpiar_filtro_of">Limpiar filtro</button>
            </div>
        </div>
    </div>

    <form method="POST"
          action="{{ route('fechas_of.store') }}"
          data-ajax="true"
          data-redirect-url="{{ route('fechas_of.index') }}">
        @csrf

        <div class="table-responsive">
            <table class="table table-bordered table-striped fechas-of-sheet" id="tablaListadoOF">
                <thead>
                    <tr>
                        <th>Fila</th>
                        <th>Nro OF</th>
                        <th>Codigo</th>
                        <th>Descripcion</th>
                        <th>Categoria</th>
                        <th>Maquina</th>
                        <th>Nro Programa H1</th>
                        <th>Nro Programa H2</th>
                        <th>Inicio P.A.P</th>
                        <th>Hora Inicio P.A.P</th>
                        <th>Fin P.A.P</th>
                        <th>Hora Fin P.A.P</th>
                        <th>Inicio Produccion</th>
                        <th>Fin Produccion</th>
                        <th>Tiempo de Pieza</th>
                        <th>Cant.Seg x Pieza</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $index => $row)
                        <tr data-nro-of="{{ $row['Nro_OF'] }}">
                            <td class="text-center js-row-number">{{ $index + 1 }}</td>
                            <td>
                                <input type="hidden" name="Id_OF[]" value="{{ $row['Id_OF'] }}">
                                <input type="number" class="form-control" name="Nro_OF_fechas[]" value="{{ $row['Nro_OF'] }}" readonly>
                            </td>
                            <td><input type="text" class="form-control" value="{{ $row['Prod_Codigo'] }}" readonly></td>
                            <td><input type="text" class="form-control" value="{{ $row['Prod_Descripcion'] }}" readonly></td>
                            <td><input type="text" class="form-control" value="{{ $row['Nombre_Categoria'] }}" readonly></td>
                            <td><input type="text" class="form-control" value="{{ $row['Nro_Maquina'] }}" readonly></td>
                            <td><input type="text" class="form-control" name="Nro_Programa_H1[]" value="{{ $row['Nro_Programa_H1'] }}"></td>
                            <td><input type="text" class="form-control" name="Nro_Programa_H2[]" value="{{ $row['Nro_Programa_H2'] }}"></td>
                            <td><input type="date" class="form-control" name="Inicio_PAP[]" value="{{ $row['Inicio_PAP'] }}"></td>
                            <td><input type="time" class="form-control" name="Hora_Inicio_PAP[]" value="{{ $row['Hora_Inicio_PAP'] }}"></td>
                            <td><input type="date" class="form-control" name="Fin_PAP[]" value="{{ $row['Fin_PAP'] }}"></td>
                            <td><input type="time" class="form-control" name="Hora_Fin_PAP[]" value="{{ $row['Hora_Fin_PAP'] }}"></td>
                            <td><input type="date" class="form-control" name="Inicio_OF[]" value="{{ $row['Inicio_OF'] }}"></td>
                            <td><input type="date" class="form-control" name="Finalizacion_OF[]" value="{{ $row['Finalizacion_OF'] }}"></td>
                            <td>
                                <input type="text"
                                       class="form-control js-tiempo-pieza"
                                       name="Tiempo_Pieza[]"
                                       inputmode="decimal"
                                       placeholder="Ej: 2.49"
                                       value="{{ $row['Tiempo_Pieza'] }}">
                            </td>
                            <td>
                                <input type="text"
                                       class="form-control js-tiempo-seg"
                                       value="{{ number_format($row['Tiempo_Seg'], 0, ',', '.') }}"
                                       readonly>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="btn-der">
            <button type="submit" class="btn btn-primary">Guardar hoja</button>
            <a href="{{ route('fechas_of.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </form>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fechas_of_create.css') }}">
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
<script>
$(document).ready(function () {
    function calcularTiempoSeg(valor) {
        const raw = String(valor || '').trim().replace(',', '.');

        if (raw === '') {
            return 0;
        }

        if (!/^\d+(\.\d{1,2})?$/.test(raw)) {
            return null;
        }

        const partes = raw.split('.');
        const minutos = parseInt(partes[0], 10) || 0;
        const segundos = partes.length > 1 ? parseInt((partes[1] + '00').slice(0, 2), 10) : 0;

        if (segundos > 59) {
            return null;
        }

        return (minutos * 60) + segundos;
    }

    function actualizarFila($fila) {
        const $tiempo = $fila.find('.js-tiempo-pieza');
        const $seg = $fila.find('.js-tiempo-seg');
        const totalSeg = calcularTiempoSeg($tiempo.val());

        $fila.removeClass('row-invalid');
        $tiempo.removeClass('is-invalid');

        if (totalSeg === null) {
            $seg.val('Formato invalido');
            $fila.addClass('row-invalid');
            $tiempo.addClass('is-invalid');
            return;
        }

        $seg.val(totalSeg.toLocaleString('es-AR'));
    }

    function renumerarFilasVisibles() {
        let visibleIndex = 1;

        $('#tablaListadoOF tbody tr:visible').each(function () {
            $(this).find('.js-row-number').text(visibleIndex);
            visibleIndex += 1;
        });

        $('#contador_of_visibles').text(visibleIndex - 1);
    }

    function actualizarEstadoInputs($fila, habilitar) {
        $fila.find('input, select, textarea, button').each(function () {
            const $campo = $(this);

            if ($campo.is('.js-tiempo-seg')) {
                return;
            }

            if ($campo.attr('type') === 'hidden') {
                $campo.prop('disabled', !habilitar);
                return;
            }

            if ($campo.is('[readonly]')) {
                $campo.prop('disabled', !habilitar);
                return;
            }

            $campo.prop('disabled', !habilitar);
        });
    }

    function aplicarFiltroOf() {
        const filtro = ($('#filtro_of_masivo').val() || '').trim().toLowerCase();

        $('#tablaListadoOF tbody tr').each(function () {
            const $fila = $(this);
            const nroOf = String($fila.data('nro-of') || '').toLowerCase();
            const visible = filtro === '' || nroOf.includes(filtro);

            $fila.toggle(visible);
            actualizarEstadoInputs($fila, visible);
        });

        renumerarFilasVisibles();
    }

    $('#tablaListadoOF tbody tr').each(function () {
        actualizarFila($(this));
    });

    aplicarFiltroOf();

    $(document).on('input change', '.js-tiempo-pieza', function () {
        actualizarFila($(this).closest('tr'));
    });

    $('#filtro_of_masivo').on('input change', function () {
        aplicarFiltroOf();
    });

    $('#limpiar_filtro_of').on('click', function () {
        $('#filtro_of_masivo').val('');
        aplicarFiltroOf();
        $('#filtro_of_masivo').trigger('focus');
    });

    $('form[data-ajax="true"]').on('submit', function (event) {
        const $filasInvalidas = $('#tablaListadoOF tbody tr:visible.row-invalid');

        if ($filasInvalidas.length > 0) {
            event.preventDefault();
            event.stopImmediatePropagation();
            SwalUtils.error('Hay filas con Tiempo de Pieza invalido. Usa el formato mm.ss y segundos entre 00 y 59.');
            $filasInvalidas.first().find('.js-tiempo-pieza').trigger('focus').select();
            return false;
        }
    });
});
</script>
@stop
