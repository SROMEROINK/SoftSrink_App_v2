@extends('adminlte::page')

@section('title', 'Carga Masiva de Egreso de MP')

@section('content_header')
    <h1>Carga Masiva de Egreso de Materia Prima</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_egreso_mass_create.css') }}">
@stop

@section('content')
<form method="POST"
      action="{{ route('mp_egresos.storeMassive') }}"
      data-ajax="true"
      data-redirect-url="{{ route('mp_egresos.index') }}">
    @csrf

    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap pedido-egreso-mass-alert">
        <div>
            <strong>OF pendientes de salida:</strong>
            <span>{{ number_format($pendingCount ?? 0, 0, ',', '.') }}</span>
        </div>
        <div>
            <strong>Rango sugerido:</strong>
            <span>
                @if(!empty($pendingMinNroOf) && !empty($pendingMaxNroOf))
                    OF #{{ number_format($pendingMinNroOf, 0, ',', '.') }} a #{{ number_format($pendingMaxNroOf, 0, ',', '.') }}
                @else
                    Sin pendientes
                @endif
            </span>
        </div>
    </div>

    <datalist id="of-egreso-catalogo-list">
        @foreach ($pedidosCatalogo as $pedido)
            <option value="{{ $pedido['Nro_OF'] }}">OF #{{ $pedido['Nro_OF'] }} - {{ $pedido['Prod_Codigo'] }}</option>
        @endforeach
    </datalist>

    <table class="table table-bordered custom-font centered-form pedido-egreso-mass-table" id="tablaListadoOF">
        <thead>
            <tr>
                <th class="col-fila">Nro Fila</th>
                <th class="col-of">Nro OF</th>
                <th class="col-producto">Producto</th>
                <th class="col-ingreso">Ingreso MP</th>
                <th class="col-codigo">Codigo MP</th>
                <th class="col-maquina">Maquina</th>
                <th class="col-barras">Barras Requeridas</th>
                <th class="col-fecha">Fecha Entrega</th>
                <th class="col-barras-ent">Barras Entregadas</th>
                <th class="col-estado">Estado</th>
                <th class="col-acciones">Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="btn-der">
        <button type="button" class="btn btn-info" id="autocompletarCorrelativas">Cargar OF Correlativas</button>
        <button type="button" class="btn btn-success" id="agregarFila">Agregar Fila</button>
        <button type="button" class="btn btn-secondary" id="agregarDiezFilas">Agregar 10 Filas</button>
        <input type="submit" class="btn btn-primary" value="Guardar Carga Masiva">
    </div>
</form>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
<script>
$(document).ready(function () {
    let filaCounter = 1;
    const pedidosCatalogo = @json($pedidosCatalogo);
    const pendingCount = {{ (int) ($pendingCount ?? 0) }};

    function getSelectedOfs(excludeRow = null) {
        const selected = new Set();

        $('#tablaListadoOF tbody tr').each(function () {
            const $currentRow = $(this);
            if (excludeRow && $currentRow.is(excludeRow)) {
                return;
            }

            const nroOf = String($currentRow.find('.input-of').val() || '').trim();
            if (nroOf !== '') {
                selected.add(nroOf);
            }
        });

        return selected;
    }

    function refreshOfCatalog(excludeRow = null) {
        const selectedOfs = getSelectedOfs(excludeRow);
        const currentValue = excludeRow ? String(excludeRow.find('.input-of').val() || '').trim() : '';
        const options = pedidosCatalogo
            .filter((pedido) => !selectedOfs.has(String(pedido.Nro_OF)) || String(pedido.Nro_OF) === currentValue)
            .map((pedido) => `<option value="${pedido.Nro_OF}">OF #${pedido.Nro_OF} - ${pedido.Prod_Codigo}</option>`)
            .join('');

        $('#of-egreso-catalogo-list').html(options);
    }

    function findPedidoByOf(nroOf) {
        return pedidosCatalogo.find((pedido) => String(pedido.Nro_OF) === String(nroOf).trim());
    }

    function getNextAvailablePedidos(limit = null) {
        const selectedOfs = getSelectedOfs();
        const disponibles = pedidosCatalogo.filter((pedido) => !selectedOfs.has(String(pedido.Nro_OF)));
        return limit === null ? disponibles : disponibles.slice(0, limit);
    }

    function countEmptyRows() {
        let total = 0;

        $('#tablaListadoOF tbody tr').each(function () {
            const idPedido = String($(this).find('.hidden-id-pedido-mp').val() || '').trim();
            if (idPedido === '') {
                total += 1;
            }
        });

        return total;
    }

    function getRowsCanBeAdded(requested) {
        const emptyRows = countEmptyRows();
        const availablePedidos = getNextAvailablePedidos().length;
        return Math.max(0, Math.min(requested, availablePedidos - emptyRows));
    }

    function generarFila(numeroFila) {
        return `<tr>
            <td class="cell-index">${numeroFila}</td>
            <td>
                <input type="text" class="form-control input-of" list="of-egreso-catalogo-list" placeholder="Buscar OF">
                <input type="hidden" name="Id_Pedido_MP[]" class="hidden-id-pedido-mp">
                <input type="hidden" name="reg_Status[]" value="1">
            </td>
            <td><input type="text" class="form-control producto-readonly" readonly></td>
            <td><input type="text" class="form-control ingreso-readonly" readonly></td>
            <td><input type="text" class="form-control codigo-readonly" readonly></td>
            <td><input type="text" class="form-control maquina-readonly" readonly></td>
            <td><input type="text" class="form-control barras-readonly" readonly></td>
            <td><input type="date" name="Fecha_de_Entrega_Pedido_Calidad[]" class="form-control input-fecha-entrega"></td>
            <td><input type="number" name="Cantidad_Unidades_MP_Preparadas[]" class="form-control input-barras-entregadas" min="0" step="1" value="0"></td>
            <td><input type="text" class="form-control estado-readonly" value="PENDIENTE CARGA" readonly></td>
            <td><button type="button" class="btn btn-danger eliminarFila">Eliminar</button></td>
        </tr>`;
    }

    function renumerarFilas() {
        $('#tablaListadoOF tbody tr').each(function (index) {
            $(this).find('.cell-index').text(index + 1);
        });
    }

    function limpiarFila($fila) {
        $fila.find('.hidden-id-pedido-mp').val('');
        $fila.find('.producto-readonly').val('');
        $fila.find('.ingreso-readonly').val('');
        $fila.find('.codigo-readonly').val('');
        $fila.find('.maquina-readonly').val('');
        $fila.find('.barras-readonly').val('');
        $fila.find('.estado-readonly').val('PENDIENTE CARGA');
        refreshOfCatalog($fila);
    }

    function completarFilaConPedido($fila, pedido) {
        if (!pedido) {
            limpiarFila($fila);
            return;
        }

        $fila.find('.input-of').val(pedido.Nro_OF);
        $fila.find('.hidden-id-pedido-mp').val(pedido.Id_Pedido_MP);
        $fila.find('.producto-readonly').val(pedido.Prod_Codigo || '');
        $fila.find('.ingreso-readonly').val(pedido.Nro_Ingreso_MP || '');
        $fila.find('.codigo-readonly').val(pedido.Codigo_MP || '');
        $fila.find('.maquina-readonly').val(pedido.Nro_Maquina || '');
        $fila.find('.barras-readonly').val(pedido.Cant_Barras_MP || 0);
    }

    function filaCompleta($fila) {
        return Boolean(
            $fila.find('.hidden-id-pedido-mp').val() &&
            ($fila.find('.input-fecha-entrega').val() || '').trim() !== '' &&
            ($fila.find('.input-barras-entregadas').val() || '').trim() !== ''
        );
    }

    function ofEstaDuplicada($fila) {
        const idPedido = String($fila.find('.hidden-id-pedido-mp').val() || '').trim();
        if (!idPedido) {
            return false;
        }

        let repeticiones = 0;
        $('#tablaListadoOF tbody tr').each(function () {
            const actual = String($(this).find('.hidden-id-pedido-mp').val() || '').trim();
            if (actual && actual === idPedido) {
                repeticiones += 1;
            }
        });

        return repeticiones > 1;
    }

    function actualizarEstadoFila($fila) {
        const ofIngresada = ($fila.find('.input-of').val() || '').trim();
        const idPedido = $fila.find('.hidden-id-pedido-mp').val();
        const cantidad = Number($fila.find('.input-barras-entregadas').val() || 0);
        const ofInvalida = ofIngresada !== '' && !idPedido;
        const ofDuplicada = ofEstaDuplicada($fila);
        const cantidadInvalida = cantidad < 0;

        $fila.removeClass('row-complete row-incomplete row-invalid');
        $fila.find('.input-of, .input-barras-entregadas, .input-fecha-entrega').removeClass('input-invalid');

        if (ofInvalida || ofDuplicada || cantidadInvalida) {
            $fila.addClass('row-invalid');
            if (ofInvalida || ofDuplicada) $fila.find('.input-of').addClass('input-invalid');
            if (cantidadInvalida) $fila.find('.input-barras-entregadas').addClass('input-invalid');
            $fila.find('.estado-readonly').val('INVALIDA');
            return;
        }

        if (filaCompleta($fila)) {
            $fila.addClass('row-complete');
            $fila.find('.estado-readonly').val('LISTA');
            return;
        }

        $fila.addClass('row-incomplete');
        $fila.find('.estado-readonly').val('PENDIENTE CARGA');
    }

    function agregarFilas(cantidad) {
        const filasAAgregar = getRowsCanBeAdded(cantidad);

        if (filasAAgregar <= 0) {
            SwalUtils.error('No quedan OF pendientes para agregar nuevas filas en la carga masiva de egresos.');
            return;
        }

        for (let i = 0; i < filasAAgregar; i += 1) {
            $('#tablaListadoOF tbody').append($(generarFila(filaCounter)));
            filaCounter += 1;
        }
        renumerarFilas();
        $('#tablaListadoOF tbody tr').each(function () {
            actualizarEstadoFila($(this));
        });
        refreshOfCatalog();
    }

    function autocompletarOfCorrelativas() {
        const $filasVacias = $('#tablaListadoOF tbody tr').filter(function () {
            return String($(this).find('.hidden-id-pedido-mp').val() || '').trim() === '';
        });

        if ($filasVacias.length === 0) {
            SwalUtils.error('No hay filas vacias para completar. Agrega mas filas para seguir cargando egresos correlativos.');
            return;
        }

        const disponibles = getNextAvailablePedidos($filasVacias.length);
        if (!disponibles.length) {
            SwalUtils.error('No quedan OF pendientes para autocompletar en la carga masiva de egresos.');
            return;
        }

        $filasVacias.each(function (index) {
            const pedido = disponibles[index];
            if (!pedido) {
                return false;
            }

            const $fila = $(this);
            completarFilaConPedido($fila, pedido);
            actualizarEstadoFila($fila);
        });

        refreshOfCatalog();
    }

    $(document).on('focusin', '.input-of', function () {
        refreshOfCatalog($(this).closest('tr'));
    });

    $(document).on('change blur', '.input-of', function () {
        const $fila = $(this).closest('tr');
        const pedido = findPedidoByOf($(this).val());

        if (!pedido && ($(this).val() || '').trim() !== '') {
            limpiarFila($fila);
            $(this).val($(this).val().trim()).addClass('input-invalid');
            actualizarEstadoFila($fila);
            refreshOfCatalog($fila);
            return;
        }

        $(this).removeClass('input-invalid');
        completarFilaConPedido($fila, pedido);

        if (ofEstaDuplicada($fila)) {
            limpiarFila($fila);
            $fila.find('.input-of').val('').addClass('input-invalid').trigger('focus');
            actualizarEstadoFila($fila);
            refreshOfCatalog($fila);
            SwalUtils.error('No puedes repetir la misma OF en la carga masiva. Cada orden debe tener un solo egreso.');
            return;
        }

        actualizarEstadoFila($fila);
        refreshOfCatalog($fila);
    });

    $(document).on('keydown', '.input-of', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            $(this).trigger('change');
            $(this).closest('tr').find('.input-fecha-entrega').trigger('focus');
        }
    });

    $(document).on('input change', '.input-fecha-entrega, .input-barras-entregadas', function () {
        actualizarEstadoFila($(this).closest('tr'));
    });

    $(document).on('click', '.eliminarFila', function () {
        $(this).closest('tr').remove();
        renumerarFilas();
        refreshOfCatalog();
    });

    $(document).on('focusin', '#tablaListadoOF input, #tablaListadoOF button', function () {
        $('#tablaListadoOF tbody tr').removeClass('row-active');
        const $fila = $(this).closest('tr');
        $fila.addClass('row-active');
        actualizarEstadoFila($fila);
    });

    $('form[data-ajax="true"]').on('submit', function (event) {
        let hayFilasInvalidas = false;
        let hayOfDuplicadas = false;

        $('#tablaListadoOF tbody tr').each(function () {
            const $fila = $(this);
            actualizarEstadoFila($fila);
            if (ofEstaDuplicada($fila)) {
                hayOfDuplicadas = true;
            }
            if ($fila.hasClass('row-invalid')) {
                hayFilasInvalidas = true;
            }
        });

        if (hayOfDuplicadas) {
            event.preventDefault();
            SwalUtils.error('No puedes repetir la misma OF en la carga masiva. Cada orden debe tener un solo egreso.');
            return;
        }

        if (hayFilasInvalidas) {
            event.preventDefault();
            SwalUtils.error('Hay filas invalidas. Corrigelas antes de guardar.');
        }
    });

    $('#autocompletarCorrelativas').on('click', autocompletarOfCorrelativas);
    $('#agregarFila').on('click', function () { agregarFilas(1); });
    $('#agregarDiezFilas').on('click', function () { agregarFilas(10); });

    refreshOfCatalog();
    if (pendingCount > 0) {
        agregarFilas(Math.min(10, pendingCount));
        autocompletarOfCorrelativas();
    }
});
</script>
@stop
