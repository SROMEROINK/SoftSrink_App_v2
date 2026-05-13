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
    @include('components.swal-session')
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

    @if(!empty($fixedPedidoMaterial))
        <div class="alert alert-success pedido-mp-mass-alert">
            <strong>Modo grupo:</strong>
            las OF que cargues en esta hoja se guardaran con el <strong>Pedido MP Interno {{ $fixedPedidoMaterial }}</strong>.
        </div>
    @endif

    @if(!empty($fixedPedidoMaterial) && !empty($existingGroupRows) && $existingGroupRows->count())
        <div class="alert alert-secondary pedido-mp-mass-alert">
            <strong>OF ya cargadas en este grupo:</strong>
            {{ $existingGroupRows->pluck('Nro_OF')->filter()->implode(', ') }}.
            Estas filas se muestran como referencia y no se volveran a guardar.
        </div>
    @endif

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
                <th class="col-pedido">Pedido MP Interno</th>
                <th class="col-certificado">Certificado</th>
                <th class="col-barras">Cant. Barras MP</th>
                <th class="col-estado">Estado MP</th>
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
    const definicionesCatalogo = @json($definicionesCatalogo ?? []);
    const maquinasCatalogo = @json($maquinasCatalogo);
    const ingresosCatalogo = @json($ingresosCatalogo);
    const estadosPlanificacion = @json($estadosPlanificacion);
    const nextPedidoMaterialNro = @json((string) ($nextPedidoMaterialNro ?? ''));
    const fixedPedidoMaterialNro = @json((string) ($fixedPedidoMaterial ?? ''));
    const currentPedidoMaterialNro = fixedPedidoMaterialNro || nextPedidoMaterialNro;
    const existingGroupRows = @json($existingGroupRows ?? []);
    const pendingOfCount = Number(@json((int) ($pendingOfCount ?? 0)));
    const createSingleUrl = @json(route('pedido_cliente_mp.create'));
    const preselectedOfId = @json($preselectedOf ?? null);

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

    function getUnifiedOfCatalog() {
        const merged = new Map();

        pedidosCatalogo.forEach((pedido) => {
            merged.set(String(pedido.Nro_OF), {
                ...pedido,
                source: 'pending'
            });
        });

        definicionesCatalogo.forEach((definicion) => {
            const key = String(definicion.Nro_OF || '');
            if (!key) {
                return;
            }

            if (!merged.has(key)) {
                merged.set(key, {
                    Id_Pedido_MP: definicion.Id_Pedido_MP,
                    Id_OF: definicion.Id_OF,
                    Nro_OF: definicion.Nro_OF,
                    Prod_Codigo: definicion.Prod_Codigo || '',
                    Prod_Descripcion: definicion.Prod_Descripcion || '',
                    Cant_Fabricacion: definicion.Cant_Fabricacion || '',
                    Largo_Pieza: definicion.Largo_Pieza || '',
                    Codigo_MP: definicion.Prod_Codigo_MP || definicion.Codigo_MP || '',
                    Materia_Prima: definicion.Prod_Material_MP || definicion.Materia_Prima || '',
                    Diametro_MP: definicion.Prod_Diametro_de_MP || definicion.Diametro_MP || '',
                    DefinitionPedidoMaterial: definicion.Pedido_Material_Nro || '',
                    DefinitionLocked: Boolean(definicion.DefinitionLocked),
                    source: 'existing'
                });
            }
        });

        return Array.from(merged.values()).sort((a, b) => Number(a.Nro_OF) - Number(b.Nro_OF));
    }

    function refreshOfCatalog(excludeRow = null) {
        const selectedOfs = getSelectedOfs(excludeRow);
        const currentValue = excludeRow ? String(excludeRow.find('.input-of').val() || '').trim() : '';
        const options = getUnifiedOfCatalog()
            .filter((pedido) => !selectedOfs.has(String(pedido.Nro_OF)) || String(pedido.Nro_OF) === currentValue)
            .map((pedido) => {
                const suffix = pedido.source === 'existing' && pedido.DefinitionPedidoMaterial
                    ? ` - ya asignada al Pedido MP ${pedido.DefinitionPedidoMaterial}`
                    : '';
                return `<option value="${pedido.Nro_OF}">OF #${pedido.Nro_OF} - ${pedido.Prod_Codigo}${suffix}</option>`;
            })
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

    function normalizeMateria(value) {
        return String(value || '')
            .normalize('NFKC')
            .replace(/\s+/g, '')
            .trim()
            .toUpperCase();
    }

    function extractDiameter(value) {
        const text = String(value || '');
        const match = text.match(/[??]?\s*([0-9]+(?:[\.,][0-9]+)?)/u);
        return match ? parseFloat(String(match[1]).replace(',', '.')) : null;
    }

    function splitCodigoMp(value) {
        const text = String(value || '').trim();
        if (!text.includes('_')) {
            return { materia: '', diametro: '' };
        }

        const [materia, ...rest] = text.split('_');
        return {
            materia: materia || '',
            diametro: rest.join('_') || ''
        };
    }

    function isCompatibleIngresoWithPedido(pedido, ingreso) {
        if (!pedido || !ingreso) {
            return false;
        }

        const pedidoParts = splitCodigoMp(pedido.Codigo_MP || '');
        const ingresoParts = splitCodigoMp(ingreso.Codigo_MP || '');

        const materiaPedido = normalizeMateria(pedido.Materia_Prima || pedidoParts.materia);
        const materiaIngreso = normalizeMateria(ingreso.Materia_Prima || ingresoParts.materia);
        if (materiaPedido && materiaIngreso && materiaPedido !== materiaIngreso) {
            return false;
        }

        const diametroPedido = extractDiameter(pedido.Diametro_MP || pedidoParts.diametro || pedido.Codigo_MP || '');
        const diametroIngreso = extractDiameter(ingreso.Diametro_MP || ingresoParts.diametro || ingreso.Codigo_MP || '');

        if (diametroPedido === null || diametroIngreso === null) {
            return true;
        }

        return diametroIngreso >= diametroPedido;
    }

    function findPedidoByOf(nroOf) {
        const normalized = String(nroOf).trim();
        return pedidosCatalogo.find((pedido) => String(pedido.Nro_OF) === normalized)
            || getUnifiedOfCatalog().find((pedido) => String(pedido.Nro_OF) === normalized);
    }

    function findMachineById(idMaquina) {
        return maquinasCatalogo.find((maquina) => String(maquina.id_maquina) === String(idMaquina));
    }

    function findIngresoByNumber(nroIngreso) {
        return ingresosCatalogo.find((ingreso) => String(ingreso.Nro_Ingreso_MP) === String(nroIngreso).trim());
    }

    function getNextAvailablePedidos(limit = null) {
        const selectedOfs = getSelectedOfs();
        const disponibles = pedidosCatalogo.filter((pedido) => !selectedOfs.has(String(pedido.Nro_OF)));
        return limit === null ? disponibles : disponibles.slice(0, limit);
    }

    function countEmptyRows() {
        return $('#tablaListadoOF tbody tr').filter(function () {
            return String($(this).find('.hidden-id-of').val() || '').trim() === '';
        }).length;
    }

    function getRowsCanBeAdded(requested) {
        const remainingPedidos = getNextAvailablePedidos().length;
        const emptyRows = countEmptyRows();
        return Math.max(0, Math.min(requested, remainingPedidos - emptyRows));
    }


    function generarFila(numeroFila, existing = false) {
        const idOfName = 'name="Id_OF[]"';
        const existingPedidoMpIdName = 'name="Existing_Pedido_MP_Id[]"';
        const idMaquinaName = 'name="Id_Maquina[]"';
        const nroIngresoName = 'name="Nro_Ingreso_MP[]"';
        const pedidoMaterialName = 'name="Pedido_Material_Nro[]"';
        const regStatusName = 'name="reg_Status[]"';
        const estadoName = 'name="Estado_Plani_Id[]"';
        const rowStateClass = existing ? 'row-complete row-existing' : 'row-incomplete';
        const actionCell = existing
            ? '<span class="text-muted font-weight-bold">Asignada</span>'
            : '<button type="button" class="btn btn-danger eliminarFila">Eliminar</button>';

        return `<tr class="${rowStateClass}" data-existing-row="${existing ? '1' : '0'}">
            <td class="cell-index">${numeroFila}</td>
            <td>
                <input type="text" class="form-control input-of" list="of-catalogo-list" placeholder="Buscar OF">
                <input type="hidden" ${idOfName} class="hidden-id-of">
                <input type="hidden" ${existingPedidoMpIdName} class="hidden-existing-pedido-mp-id">
                <input type="hidden" class="hidden-cantidad-fabricacion">
                <input type="hidden" class="hidden-largo-pieza">
                <input type="hidden" class="hidden-codigo-mp-producto">
                <input type="hidden" class="hidden-producto-materia">
                <input type="hidden" class="hidden-producto-diametro">
                <input type="hidden" ${pedidoMaterialName} class="hidden-pedido-material" value="${currentPedidoMaterialNro}">
                <input type="hidden" ${regStatusName} value="1">
            </td>
            <td><input type="text" class="form-control producto-readonly" readonly></td>
            <td>
                <select ${idMaquinaName} class="form-control filtro-select select-maquina" required>
                    ${maquinasOptions}
                </select>
            </td>
            <td><input type="text" class="form-control familia-maquina-readonly" readonly></td>
            <td><input type="text" class="form-control codigo-mp-readonly" readonly></td>
            <td>
                <div class="ingreso-selector-cell">
                    <input type="text" ${nroIngresoName} class="form-control input-ingreso-mp" list="ingreso-mp-catalogo-list" placeholder="Buscar ingreso">
                    <input type="hidden" class="hidden-longitud-unidad">
                    <button type="button" class="btn btn-info btn-sm consultar-sugeridos">Consultar ingresos sugeridos</button>
                </div>
            </td>
            <td><input type="text" class="form-control pedido-material-readonly" readonly value="${currentPedidoMaterialNro}"></td>
            <td><input type="text" class="form-control certificado-readonly" readonly></td>
            <td><input type="text" class="form-control barras-readonly" readonly></td>
            <td>
                <select ${estadoName} class="form-control filtro-select" required>
                    ${estadosOptions}
                </select>
            </td>
            <td>${actionCell}</td>
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
        $fila.find('.hidden-existing-pedido-mp-id').val('');
        $fila.find('.hidden-cantidad-fabricacion').val('');
        $fila.find('.hidden-largo-pieza').val('');
        $fila.find('.hidden-codigo-mp-producto').val('');
        $fila.find('.hidden-producto-materia').val('');
        $fila.find('.hidden-producto-diametro').val('');
        $fila.find('.producto-readonly').val('');
        $fila.find('.codigo-mp-readonly').val('');
        $fila.find('.input-ingreso-mp').val('');
        $fila.find('.pedido-material-readonly').val(currentPedidoMaterialNro);
        $fila.find('.hidden-pedido-material').val(currentPedidoMaterialNro);
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
        $fila.find('.hidden-existing-pedido-mp-id').val(pedido.Id_Pedido_MP || '');
        $fila.find('.hidden-cantidad-fabricacion').val(pedido.Cant_Fabricacion);
        $fila.find('.hidden-largo-pieza').val(pedido.Largo_Pieza || '');
        $fila.find('.hidden-codigo-mp-producto').val(pedido.Codigo_MP || '');
        $fila.find('.hidden-producto-materia').val(pedido.Materia_Prima || '');
        $fila.find('.hidden-producto-diametro').val(pedido.Diametro_MP || '');
        $fila.find('.producto-readonly').val(pedido.Prod_Codigo || '');
        $fila.find('.codigo-mp-readonly').val(pedido.Codigo_MP || '');

        if (pedido.Id_Maquina) {
            $fila.find('.select-maquina').val(pedido.Id_Maquina).trigger('change');
        }

        if (pedido.Nro_Ingreso_MP) {
            $fila.find('.input-ingreso-mp').val(pedido.Nro_Ingreso_MP);
            $fila.find('.hidden-longitud-unidad').val(pedido.Longitud_Un_MP || '');
            $fila.find('.certificado-readonly').val(pedido.Nro_Certificado_MP || '');
        }

        if (pedido.Cant_Barras_MP) {
            $fila.find('.barras-readonly').val(pedido.Cant_Barras_MP);
        }

        if (pedido.Estado_Plani_Id) {
            $fila.find('select[name="Estado_Plani_Id[]"]').val(String(pedido.Estado_Plani_Id));
        }
    }

    function completarFilaConDatosExistentes($fila, rowData) {
        $fila.find('.input-of').val(rowData.Nro_OF || '');
        $fila.find('.hidden-id-of').val(rowData.Id_OF || '');
        $fila.find('.hidden-existing-pedido-mp-id').val(rowData.Id_Pedido_MP || '');
        $fila.find('.hidden-cantidad-fabricacion').val(rowData.Cant_Fabricacion || '');
        $fila.find('.hidden-largo-pieza').val(rowData.Largo_Pieza || '');
        $fila.find('.hidden-codigo-mp-producto').val(rowData.Prod_Codigo_MP || rowData.Codigo_MP || '');
        $fila.find('.hidden-producto-materia').val(rowData.Prod_Material_MP || '');
        $fila.find('.hidden-producto-diametro').val(rowData.Prod_Diametro_de_MP || '');
        $fila.find('.producto-readonly').val(rowData.Prod_Codigo || '');
        $fila.find('.codigo-mp-readonly').val(rowData.Codigo_MP || rowData.Prod_Codigo_MP || '');
    }

    function getPedidoDataForRow($fila) {
        return {
            Id_OF: String($fila.find('.hidden-id-of').val() || '').trim(),
            Nro_OF: String($fila.find('.input-of').val() || '').trim(),
            Prod_Codigo: String($fila.find('.producto-readonly').val() || '').trim(),
            Cant_Fabricacion: parseNumber($fila.find('.hidden-cantidad-fabricacion').val()),
            Largo_Pieza: $fila.find('.hidden-largo-pieza').val(),
            Codigo_MP: String($fila.find('.hidden-codigo-mp-producto').val() || '').trim() || String($fila.find('.codigo-mp-readonly').val() || '').trim(),
            Materia_Prima: String($fila.find('.hidden-producto-materia').val() || '').trim(),
            Diametro_MP: String($fila.find('.hidden-producto-diametro').val() || '').trim(),
        };
    }

    function actualizarFilaConMaquina($fila) {
        const maquina = findMachineById($fila.find('.select-maquina').val());
        $fila.find('.familia-maquina-readonly').val(maquina ? maquina.familia_maquina || '' : '');
        recalcularBarrasFila($fila);
    }

    function actualizarFilaConIngreso($fila) {
        const ingreso = findIngresoByNumber($fila.find('.input-ingreso-mp').val());
        const pedido = getPedidoDataForRow($fila);

        $fila.find('.pedido-material-readonly').val(currentPedidoMaterialNro);
        $fila.find('.hidden-pedido-material').val(currentPedidoMaterialNro);
        $fila.find('.certificado-readonly').val('');

        if (!ingreso) {
            $fila.find('.input-ingreso-mp').addClass('input-invalid');
            $fila.find('.barras-readonly').val('');
            actualizarEstadoFila($fila);
            return false;
        }

        if (!isCompatibleIngresoWithPedido(pedido, ingreso)) {
            $fila.find('.input-ingreso-mp').addClass('input-invalid');
            $fila.find('.pedido-material-readonly').val(currentPedidoMaterialNro);
            $fila.find('.hidden-pedido-material').val(currentPedidoMaterialNro);
            $fila.find('.certificado-readonly').val('');
            $fila.find('.barras-readonly').val('');
            actualizarEstadoFila($fila);
            return false;
        }

        $fila.find('.input-ingreso-mp').removeClass('input-invalid');
        $fila.find('.codigo-mp-readonly').val(ingreso.Codigo_MP || pedido?.Codigo_MP || '');
        $fila.find('.pedido-material-readonly').val(currentPedidoMaterialNro);
        $fila.find('.hidden-pedido-material').val(currentPedidoMaterialNro);
        $fila.find('.certificado-readonly').val(ingreso.Nro_Certificado_MP || '');
        $fila.find('.hidden-longitud-unidad').val(ingreso.Longitud_Unidad_MP || '');
        recalcularBarrasFila($fila);
        actualizarEstadoFila($fila);
        return true;
    }

    function recalcularBarrasFila($fila) {
        const pedido = getPedidoDataForRow($fila);
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
        const cantidadReal = getRowsCanBeAdded(cantidad);
        if (cantidadReal <= 0) {
            SwalUtils.error('No hay OF disponibles para agregar mas filas en esta carga masiva.');
            return;
        }

        for (let i = 0; i < cantidadReal; i += 1) {
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
            return String($(this).find('.hidden-id-of').val() || '').trim() === '';
        });

        if ($filasVacias.length === 0) {
            SwalUtils.error('No hay filas vacias para completar. Agrega mas filas para seguir cargando OF correlativas.');
            return;
        }

        const disponibles = getNextAvailablePedidos($filasVacias.length);
        if (!disponibles.length) {
            SwalUtils.error('No quedan OF pendientes para autocompletar en la carga masiva.');
            return;
        }

        $filasVacias.each(function (index) {
            const pedido = disponibles[index];
            if (!pedido) {
                return false;
            }

            const $fila = $(this);
            $fila.find('.input-of').removeClass('input-invalid');
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

            SwalUtils.error('El ingreso seleccionado no existe o no es compatible. Debe ser del mismo material y con diametro igual o mayor al requerido por la OF.');
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

    $('#autocompletarCorrelativas').on('click', function () {
        autocompletarOfCorrelativas();
    });

    $('#agregarFila').on('click', function () {
        agregarFilas(1);
    });

    $('#agregarDiezFilas').on('click', function () {
        agregarFilas(10);
    });

    function buildMassiveDraft() {
        return $('#tablaListadoOF tbody tr').map(function () {
            const $fila = $(this);
            return {
                existing: $fila.attr('data-existing-row') === '1',
                existingPedidoMpId: String($fila.find('.hidden-existing-pedido-mp-id').val() || '').trim(),
                rowIndex: String($fila.find('.cell-index').text() || '').trim(),
                nroOf: String($fila.find('.input-of').val() || '').trim(),
                machineId: String($fila.find('.select-maquina').val() || '').trim(),
                nroIngreso: String($fila.find('.input-ingreso-mp').val() || '').trim(),
                pedidoMaterial: String($fila.find('.pedido-material-readonly').val() || '').trim(),
                certificado: String($fila.find('.certificado-readonly').val() || '').trim(),
                barras: String($fila.find('.barras-readonly').val() || '').trim(),
                estadoId: String($fila.find('select[name="Estado_Plani_Id[]"]').val() || '').trim(),
                longitudUnMp: String($fila.find('.hidden-longitud-unidad').val() || '').trim()
            };
        }).get();
    }

    function saveMassiveDraftToStorage() {
        sessionStorage.setItem('pedidoClienteMpMassiveDraft', JSON.stringify(buildMassiveDraft()));
    }

    function clearMassiveStorage() {
        sessionStorage.removeItem('pedidoClienteMpMassiveDraft');
        sessionStorage.removeItem('pedidoClienteMpMassiveSelection');
    }

    function restoreMassiveDraftFromStorage() {
        const raw = sessionStorage.getItem('pedidoClienteMpMassiveDraft');
        if (!raw) {
            return false;
        }

        sessionStorage.removeItem('pedidoClienteMpMassiveDraft');

        let draftRows;
        try {
            draftRows = JSON.parse(raw);
        } catch (error) {
            return false;
        }

        if (!Array.isArray(draftRows) || !draftRows.length) {
            return false;
        }

        $('#tablaListadoOF tbody').empty();
        filaCounter = 1;

        draftRows.forEach((rowData, index) => {
            $('#tablaListadoOF tbody').append($(generarFila(index + 1, Boolean(rowData.existing))));
            filaCounter = index + 2;

            const $fila = $('#tablaListadoOF tbody tr').last();
            const pedido = findPedidoByOf(rowData.nroOf);

            if (pedido) {
                completarFilaConPedido($fila, pedido);
            } else if (rowData.nroOf) {
                $fila.find('.input-of').val(rowData.nroOf);
            }

            $fila.find('.select-maquina').val(rowData.machineId || '').trigger('change');
            $fila.find('.input-ingreso-mp').val(rowData.nroIngreso || '');
            $fila.find('select[name="Estado_Plani_Id[]"], select.filtro-select').last().val(rowData.estadoId || '11');

        if (rowData.nroIngreso) {
            actualizarFilaConIngreso($fila);
        } else {
            actualizarEstadoFila($fila);
        }

        if (rowData.existingPedidoMpId) {
            $fila.find('.hidden-existing-pedido-mp-id').val(rowData.existingPedidoMpId);
        }

            if (rowData.pedidoMaterial) {
                $fila.find('.pedido-material-readonly').val(rowData.pedidoMaterial);
                $fila.find('.hidden-pedido-material').val(rowData.pedidoMaterial);
            }

            if (rowData.certificado) {
                $fila.find('.certificado-readonly').val(rowData.certificado);
            }

            if (rowData.barras) {
                $fila.find('.barras-readonly').val(rowData.barras);
            }

            if (rowData.longitudUnMp) {
                $fila.find('.hidden-longitud-unidad').val(rowData.longitudUnMp);
            }
        });

        renumerarFilas();
        refreshOfCatalog();
        return true;
    }

    function inicializarFilasExistentesDelGrupo() {
        if (!Array.isArray(existingGroupRows) || !existingGroupRows.length) {
            return false;
        }

        $('#tablaListadoOF tbody').empty();
        filaCounter = 1;

        existingGroupRows.forEach((rowData, index) => {
            $('#tablaListadoOF tbody').append($(generarFila(index + 1, true)));
            filaCounter = index + 2;

            const $fila = $('#tablaListadoOF tbody tr').last();
            const pedido = findPedidoByOf(rowData.Nro_OF);

            if (pedido) {
                completarFilaConPedido($fila, pedido);
            } else {
                completarFilaConDatosExistentes($fila, rowData);
            }

            $fila.find('.select-maquina').val(rowData.Id_Maquina || '').trigger('change');
            $fila.find('.input-ingreso-mp').val(rowData.Nro_Ingreso_MP || '');
            $fila.find('select').last().val(rowData.Estado_Plani_Id || '11');

            if (rowData.Nro_Ingreso_MP) {
                actualizarFilaConIngreso($fila);
            } else {
                actualizarEstadoFila($fila);
            }

            if (rowData.Pedido_Material_Nro) {
                $fila.find('.pedido-material-readonly').val(rowData.Pedido_Material_Nro);
                $fila.find('.hidden-pedido-material').val(rowData.Pedido_Material_Nro);
            }

            if (rowData.Nro_Certificado_MP) {
                $fila.find('.certificado-readonly').val(rowData.Nro_Certificado_MP);
            }

            if (rowData.Cant_Barras_MP) {
                $fila.find('.barras-readonly').val(rowData.Cant_Barras_MP);
            }

            if (rowData.Longitud_Un_MP) {
                $fila.find('.hidden-longitud-unidad').val(rowData.Longitud_Un_MP);
            }

            if (rowData.Id_Pedido_MP) {
                $fila.find('.hidden-existing-pedido-mp-id').val(rowData.Id_Pedido_MP);
            }
        });

        renumerarFilas();
        refreshOfCatalog();
        return true;
    }

    $(document).on('click', '.consultar-sugeridos', function () {
        const $fila = $(this).closest('tr');
        const pedido = findPedidoByOf($fila.find('.input-of').val());
        const idMaquina = $fila.find('.select-maquina').val();
        const rowIndex = $fila.find('.cell-index').text().trim();

        if (!pedido) {
            SwalUtils.error('Primero selecciona una OF valida en la fila.');
            return;
        }

        if (!idMaquina) {
            SwalUtils.error('Primero selecciona una maquina para poder consultar ingresos sugeridos.');
            return;
        }

        saveMassiveDraftToStorage();

        const selectedIngreso = String($fila.find('.input-ingreso-mp').val() || '').trim();
        const selectedCertificado = String($fila.find('.certificado-readonly').val() || '').trim();
        const selectedPedidoMaterial = String($fila.find('.pedido-material-readonly').val() || '').trim();
        const selectedLongitudUnMp = String($fila.find('.hidden-longitud-unidad').val() || '').trim();

        const params = new URLSearchParams({
            of: pedido.Id_OF,
            from_massive: '1',
            machine: idMaquina,
            row: rowIndex
        });

        if (selectedIngreso !== '') {
            params.set('selected_ingreso', selectedIngreso);
        }

        if (selectedCertificado !== '') {
            params.set('selected_certificado', selectedCertificado);
        }

        if (selectedPedidoMaterial !== '') {
            params.set('selected_pedido_material', selectedPedidoMaterial);
        }

        if (selectedLongitudUnMp !== '') {
            params.set('selected_longitud_un_mp', selectedLongitudUnMp);
        }

        window.location.href = `${createSingleUrl}?${params.toString()}`;
    });

    function applyMassiveSelectionFromStorage() {
        const raw = sessionStorage.getItem('pedidoClienteMpMassiveSelection');
        if (!raw) {
            return;
        }

        sessionStorage.removeItem('pedidoClienteMpMassiveSelection');

        let selection;
        try {
            selection = JSON.parse(raw);
        } catch (error) {
            return;
        }

        const pedido = findPedidoByOf(selection.nroOf || selection.idOf);
        if (!pedido) {
            return;
        }

        let $fila = $();
        const rowIndex = Number(selection.rowIndex || 0);

        if (rowIndex > 0) {
            $fila = $('#tablaListadoOF tbody tr').filter(function () {
                return String($(this).find('.cell-index').text()).trim() === String(rowIndex);
            }).first();
        }

        if (!$fila.length) {
            $fila = $('#tablaListadoOF tbody tr').filter(function () {
                return String($(this).find('.input-of').val() || '').trim() === String(selection.nroOf || '');
            }).first();
        }

        if (!$fila.length) {
            return;
        }

        completarFilaConPedido($fila, pedido);
        $fila.find('.select-maquina').val(selection.machineId || '').trigger('change');
        $fila.find('.input-ingreso-mp').val(selection.nroIngreso || '');
        actualizarFilaConIngreso($fila);

        if (selection.certificado) {
            $fila.find('.certificado-readonly').val(selection.certificado);
        }

        if (selection.pedidoMaterial) {
            $fila.find('.pedido-material-readonly').val(selection.pedidoMaterial);
            $fila.find('.hidden-pedido-material').val(selection.pedidoMaterial);
        }

        if (selection.longitudUnMp) {
            $fila.find('.hidden-longitud-unidad').val(selection.longitudUnMp);
        }

        $fila.addClass('row-active').siblings().removeClass('row-active');
        $fila.find('select[name="Estado_Plani_Id[]"]').trigger('focus');
        saveMassiveDraftToStorage();
        SwalUtils.success('Ingreso sugerido aplicado a la fila de la carga masiva.');
    }

    $(document).on('change', 'select[name="Estado_Plani_Id[]"]', function () {
        saveMassiveDraftToStorage();
    });

    function inicializarConOfPreseleccionada() {
        if (!preselectedOfId) {
            return false;
        }

        const pedidoPreseleccionado = pedidosCatalogo.find((pedido) =>
            String(pedido.Id_OF) === String(preselectedOfId) ||
            String(pedido.Nro_OF) === String(preselectedOfId)
        ) || findPedidoByOf(preselectedOfId);

        if (!pedidoPreseleccionado) {
            return false;
        }

        clearMassiveStorage();
        $('#tablaListadoOF tbody').empty();
        filaCounter = 1;
        agregarFilas(1);

        const $fila = $('#tablaListadoOF tbody tr').first();
        if (!$fila.length) {
            return false;
        }

        completarFilaConPedido($fila, pedidoPreseleccionado);
        actualizarEstadoFila($fila);
        $fila.addClass('row-active').siblings().removeClass('row-active');
        $fila.find('.select-maquina').trigger('focus');
        saveMassiveDraftToStorage();
        return true;
    }

    refreshOfCatalog();
    const initializedWithSelectedOf = inicializarConOfPreseleccionada();
    const restoredDraft = initializedWithSelectedOf ? false : restoreMassiveDraftFromStorage();
    const initializedWithExistingGroup = (initializedWithSelectedOf || restoredDraft) ? false : inicializarFilasExistentesDelGrupo();
    if (!initializedWithSelectedOf && !restoredDraft && !initializedWithExistingGroup) {
        agregarFilas(1);
    }
    applyMassiveSelectionFromStorage();
});
</script>
@stop







