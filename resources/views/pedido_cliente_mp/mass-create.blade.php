@extends('adminlte::page')

@section('title', 'Carga Masiva de MP por Pedido')

@section('content_header')
    <h1>Carga Masiva de MP por Pedido</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_mp_mass_create.css') }}">
@stop

@section('content')
<form method="POST"
      action="{{ route('pedido_cliente_mp.storeMassive') }}"
      data-ajax="true"
      data-redirect-url="{{ route('pedido_cliente_mp.index') }}">
    @csrf

    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap pedido-mp-mass-alert">
        <div>
            <strong>OF pendientes para definir MP:</strong>
            <span>{{ number_format($pendingOfCount ?? 0, 0, ',', '.') }}</span>
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

    <datalist id="of-catalogo-list">
        @foreach ($pedidosCatalogo as $pedido)
            <option value="{{ $pedido['Nro_OF'] }}">OF #{{ $pedido['Nro_OF'] }} - {{ $pedido['Prod_Codigo'] }}</option>
        @endforeach
    </datalist>

    <datalist id="ingreso-mp-catalogo-list">
        @foreach ($ingresosCatalogo as $ingreso)
            <option value="{{ $ingreso['Nro_Ingreso_MP'] }}">{{ $ingreso['Codigo_MP'] }}</option>
        @endforeach
    </datalist>

    <table class="table table-bordered custom-font centered-form pedido-mp-mass-table" id="tablaListadoOF">
        <thead>
            <tr>
                <th class="col-fila">Nro Fila</th>
                <th class="col-of">Nro OF</th>
                <th class="col-producto">Producto</th>
                <th class="col-maquina">Nro de Maquina</th>
                <th class="col-familia">Familia de Maquinas</th>
                <th class="col-codigo">Codigo MP</th>
                <th class="col-ingreso">Ingreso MP Seleccionado</th>
                <th class="col-pedido">Pedido Material</th>
                <th class="col-certificado">Certificado</th>
                <th class="col-barras">Cant. Barras MP</th>
                <th class="col-estado">Estado MP</th>
                <th class="col-acciones">Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="btn-der">
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
    const maquinasCatalogo = @json($maquinasCatalogo);
    const ingresosCatalogo = @json($ingresosCatalogo);
    const estadosPlanificacion = @json($estadosPlanificacion);

    const defaultsCalculo = {
        frenteado: 0.50,
        anchoCutOff: 1.00,
        sobrematerial: 0.50
    };

    const maquinasOptions = [
        '<option value="">Seleccione maquina</option>',
        ...maquinasCatalogo.map((maquina) => `<option value="${maquina.id_maquina}">${maquina.Nro_maquina}</option>`)
    ].join('');

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

        $('#of-catalogo-list').html(options);
    }

    const estadosOptions = [
        '<option value="">Seleccione estado</option>',
        ...estadosPlanificacion.map((estado) => {
            const selected = String(estado.Estado_Plani_Id) === '11' ? 'selected' : '';
            return `<option value="${estado.Estado_Plani_Id}" ${selected}>${estado.Nombre_Estado}</option>`;
        })
    ].join('');

    function parseNumber(value) {
        const normalized = String(value || '').replace(',', '.').trim();
        const number = parseFloat(normalized);
        return Number.isFinite(number) ? number : 0;
    }

    function formatInteger(value) {
        if (!Number.isFinite(value) || value <= 0) return '';
        return Math.ceil(value).toLocaleString('es-AR');
    }

    function normalizeCodigoMp(value) {
        return String(value || '')
            .normalize('NFKC')
            .replace(/\s+/g, '')
            .trim()
            .toUpperCase();
    }

    function findPedidoByOf(nroOf) {
        return pedidosCatalogo.find((pedido) => String(pedido.Nro_OF) === String(nroOf).trim());
    }

    function findMachineById(idMaquina) {
        return maquinasCatalogo.find((maquina) => String(maquina.id_maquina) === String(idMaquina));
    }

    function findIngresoByNumber(nroIngreso) {
        return ingresosCatalogo.find((ingreso) => String(ingreso.Nro_Ingreso_MP) === String(nroIngreso).trim());
    }

    function generarFila(numeroFila) {
        return `<tr>
            <td class="cell-index">${numeroFila}</td>
            <td>
                <input type="text" class="form-control input-of" list="of-catalogo-list" placeholder="Buscar OF">
                <input type="hidden" name="Id_OF[]" class="hidden-id-of">
                <input type="hidden" class="hidden-cantidad-fabricacion">
                <input type="hidden" class="hidden-largo-pieza">
                <input type="hidden" class="hidden-codigo-mp-producto">
                <input type="hidden" name="reg_Status[]" value="1">
            </td>
            <td><input type="text" class="form-control producto-readonly" readonly></td>
            <td>
                <select name="Id_Maquina[]" class="form-control filtro-select select-maquina" required>
                    ${maquinasOptions}
                </select>
            </td>
            <td><input type="text" class="form-control familia-maquina-readonly" readonly></td>
            <td><input type="text" class="form-control codigo-mp-readonly" readonly></td>
            <td><input type="text" name="Nro_Ingreso_MP[]" class="form-control input-ingreso-mp" list="ingreso-mp-catalogo-list" placeholder="Buscar ingreso"></td>
            <td><input type="text" class="form-control pedido-material-readonly" readonly></td>
            <td><input type="text" class="form-control certificado-readonly" readonly></td>
            <td><input type="text" class="form-control barras-readonly" readonly></td>
            <td>
                <select name="Estado_Plani_Id[]" class="form-control filtro-select" required>
                    ${estadosOptions}
                </select>
            </td>
            <td><button type="button" class="btn btn-danger eliminarFila">Eliminar</button></td>
        </tr>`;
    }

    function renumerarFilas() {
        $('#tablaListadoOF tbody tr').each(function (index) {
            $(this).find('.cell-index').text(index + 1);
        });
    }

    function calcularBarras(pedido, maquina, ingreso) {
        if (!pedido || !maquina || !ingreso) {
            return '';
        }

        const largoPieza = parseNumber(pedido.Largo_Pieza);
        const largoTotal = largoPieza + defaultsCalculo.frenteado + defaultsCalculo.anchoCutOff + defaultsCalculo.sobrematerial;
        const mmTotales = parseNumber(pedido.Cant_Fabricacion) * largoTotal;
        const longitudBarraSinScrap = (parseNumber(ingreso.Longitud_Unidad_MP) * 1000) - parseNumber(maquina.scrap_maquina);

        if (mmTotales <= 0 || longitudBarraSinScrap <= 0) {
            return '';
        }

        return formatInteger(mmTotales / longitudBarraSinScrap);
    }

    function limpiarFila($fila) {
        $fila.find('.hidden-id-of').val('');
        $fila.find('.hidden-cantidad-fabricacion').val('');
        $fila.find('.hidden-largo-pieza').val('');
        $fila.find('.hidden-codigo-mp-producto').val('');
        $fila.find('.producto-readonly').val('');
        $fila.find('.codigo-mp-readonly').val('');
        $fila.find('.input-ingreso-mp').val('');
        $fila.find('.pedido-material-readonly').val('');
        $fila.find('.certificado-readonly').val('');
        $fila.find('.barras-readonly').val('');
        $fila.find('.select-maquina').val('');
        $fila.find('.familia-maquina-readonly').val('');
        refreshOfCatalog($fila);
    }

    function completarFilaConPedido($fila, pedido) {
        if (!pedido) {
            limpiarFila($fila);
            return;
        }

        $fila.find('.input-of').val(pedido.Nro_OF);
        $fila.find('.hidden-id-of').val(pedido.Id_OF);
        $fila.find('.hidden-cantidad-fabricacion').val(pedido.Cant_Fabricacion);
        $fila.find('.hidden-largo-pieza').val(pedido.Largo_Pieza || '');
        $fila.find('.hidden-codigo-mp-producto').val(pedido.Codigo_MP || '');
        $fila.find('.producto-readonly').val(pedido.Prod_Codigo || '');
        $fila.find('.codigo-mp-readonly').val(pedido.Codigo_MP || '');
    }

    function actualizarFilaConMaquina($fila) {
        const maquina = findMachineById($fila.find('.select-maquina').val());
        $fila.find('.familia-maquina-readonly').val(maquina ? maquina.familia_maquina || '' : '');
        recalcularBarrasFila($fila);
    }

    function actualizarFilaConIngreso($fila) {
        const ingreso = findIngresoByNumber($fila.find('.input-ingreso-mp').val());
        const pedido = findPedidoByOf($fila.find('.input-of').val());

        $fila.find('.pedido-material-readonly').val('');
        $fila.find('.certificado-readonly').val('');

        if (!ingreso) {
            $fila.find('.input-ingreso-mp').addClass('input-invalid');
            $fila.find('.barras-readonly').val('');
            actualizarEstadoFila($fila);
            return false;
        }

        const codigoEsperado = normalizeCodigoMp(pedido?.Codigo_MP || '');
        const codigoIngreso = normalizeCodigoMp(ingreso.Codigo_MP || '');
        if (codigoEsperado && codigoIngreso !== codigoEsperado) {
            $fila.find('.input-ingreso-mp').addClass('input-invalid');
            $fila.find('.pedido-material-readonly').val('');
            $fila.find('.certificado-readonly').val('');
            $fila.find('.barras-readonly').val('');
            actualizarEstadoFila($fila);
            return false;
        }

        $fila.find('.input-ingreso-mp').removeClass('input-invalid');
        $fila.find('.codigo-mp-readonly').val(ingreso.Codigo_MP || codigoEsperado || '');
        $fila.find('.pedido-material-readonly').val(ingreso.Pedido_Material_Nro || '');
        $fila.find('.certificado-readonly').val(ingreso.Nro_Certificado_MP || '');
        recalcularBarrasFila($fila);
        actualizarEstadoFila($fila);
        return true;
    }

    function recalcularBarrasFila($fila) {
        const pedido = findPedidoByOf($fila.find('.input-of').val());
        const maquina = findMachineById($fila.find('.select-maquina').val());
        const ingreso = findIngresoByNumber($fila.find('.input-ingreso-mp').val());
        $fila.find('.barras-readonly').val(calcularBarras(pedido, maquina, ingreso));
    }

    function filaCompleta($fila) {
        return Boolean(
            $fila.find('.hidden-id-of').val() &&
            $fila.find('.select-maquina').val() &&
            $fila.find('.input-ingreso-mp').val() &&
            $fila.find('select[name="Estado_Plani_Id[]"]').val()
        );
    }
    function ofEstaDuplicada($fila) {
        const idOf = String($fila.find('.hidden-id-of').val() || '').trim();
        if (!idOf) {
            return false;
        }

        let repeticiones = 0;
        $('#tablaListadoOF tbody tr').each(function () {
            const actual = String($(this).find('.hidden-id-of').val() || '').trim();
            if (actual && actual === idOf) {
                repeticiones += 1;
            }
        });

        return repeticiones > 1;
    }

    function limpiarOfDuplicada($fila) {
        limpiarFila($fila);
        $fila.find('.input-of').val('').addClass('input-invalid').trigger('focus');
        actualizarEstadoFila($fila);
    }
    function actualizarEstadoFila($fila) {
        const ofIngresada = ($fila.find('.input-of').val() || '').trim();
        const idOf = $fila.find('.hidden-id-of').val();
        const ingresoIngresado = ($fila.find('.input-ingreso-mp').val() || '').trim();
        const ingresoValido = !ingresoIngresado || !$fila.find('.input-ingreso-mp').hasClass('input-invalid');
        const ofInvalida = ofIngresada !== '' && !idOf;
        const ofDuplicada = ofEstaDuplicada($fila);

        $fila.removeClass('row-complete row-incomplete row-invalid');
        $fila.find('.input-of').removeClass('input-invalid');

        if (ofInvalida || ofDuplicada) {
            $fila.addClass('row-invalid');
            $fila.find('.input-of').addClass('input-invalid');
            return;
        }

        if (ingresoIngresado !== '' && !ingresoValido) {
            $fila.addClass('row-invalid');
            return;
        }

        $fila.addClass(filaCompleta($fila) ? 'row-complete' : 'row-incomplete');
    }

    function agregarFilas(cantidad) {
        for (let i = 0; i < cantidad; i += 1) {
            $('#tablaListadoOF tbody').append($(generarFila(filaCounter)));
            filaCounter += 1;
        }
        renumerarFilas();
        $('#tablaListadoOF tbody tr').each(function () {
            actualizarEstadoFila($(this));
        });
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
            limpiarOfDuplicada($fila);
            refreshOfCatalog($fila);
            SwalUtils.error('No puedes repetir la misma OF en la carga masiva. Cada orden debe tener una unica definicion de MP.');
            return;
        }

        if ($fila.find('.input-ingreso-mp').val()) {
            actualizarFilaConIngreso($fila);
        } else {
            actualizarEstadoFila($fila);
        }

        refreshOfCatalog($fila);
    });

    $(document).on('keydown', '.input-of', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            $(this).trigger('change');
            $(this).closest('tr').find('.select-maquina').trigger('focus');
        }
    });

    $(document).on('change', '.select-maquina', function () {
        const $fila = $(this).closest('tr');
        actualizarFilaConMaquina($fila);
        actualizarEstadoFila($fila);
    });

    $(document).on('change blur', '.input-ingreso-mp', function () {
        actualizarFilaConIngreso($(this).closest('tr'));
    });

    $(document).on('keydown', '.input-ingreso-mp', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const $fila = $(this).closest('tr');
            const ok = actualizarFilaConIngreso($fila);

            if (ok) {
                $fila.find('select[name="Estado_Plani_Id[]"]').trigger('focus');
                return;
            }

            SwalUtils.error('El ingreso seleccionado no existe o no es compatible con el Codigo MP requerido por la OF.');
        }
    });

    $(document).on('click', '.eliminarFila', function () {
        $(this).closest('tr').remove();
        renumerarFilas();
        refreshOfCatalog();
    });

    $(document).on('focusin', '#tablaListadoOF input, #tablaListadoOF select, #tablaListadoOF button', function () {
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
            SwalUtils.error('No puedes repetir la misma OF en la carga masiva. Cada orden debe tener una unica definicion de MP.');
            return;
        }

        if (hayFilasInvalidas) {
            event.preventDefault();
            SwalUtils.error('Hay filas con OF o ingresos MP invalidos. Corrigelas antes de guardar.');
        }
    });

    $('#agregarFila').on('click', function () {
        agregarFilas(1);
    });

    $('#agregarDiezFilas').on('click', function () {
        agregarFilas(10);
    });

    refreshOfCatalog();
    agregarFilas(10);
});
</script>
@stop
