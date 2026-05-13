@extends('adminlte::page')

@section('title', 'Editar MP por Hoja')

@section('content_header')
    <h1>Editar MP por Hoja</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_mp_mass_create.css') }}">
@stop

@section('content')
    @include('components.swal-session')
<form method="POST" action="{{ route('pedido_cliente_mp.updateMassive', $pedidoMp->Id_Pedido_MP) }}">
    @csrf
    <input type="hidden" name="Estado_Plani_Id" value="{{ $pedidoMp->Estado_Plani_Id ?? 11 }}">
    <input type="hidden" name="Observaciones" value="{{ $pedidoMp->Observaciones ?? '' }}">

    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap pedido-mp-mass-alert">
        <div>
            <strong>Editando OF:</strong>
            <span>#{{ number_format($pedidoMp->pedido->Nro_OF ?? 0, 0, ',', '.') }}</span>
        </div>
        <div>
            <strong>Pedido MP Interno:</strong>
            <span>{{ $pedidoMp->Pedido_Material_Nro }}</span>
        </div>
    </div>

    @if(!empty($definitionLocked))
        <div class="alert alert-warning">
            <strong>Definicion bloqueada:</strong>
            esta OF ya tiene una salida registrada en Egreso de Materia Prima, por lo que no se puede redefinir la MP desde esta hoja.
            @if(!empty($egresoId))
                <a href="{{ route('mp_egresos.edit', $egresoId) }}" class="btn btn-sm btn-outline-dark ml-2">Ir a editar salida MP</a>
            @endif
        </div>
    @endif

    <datalist id="ingreso-mp-catalogo-list">
        @foreach ($ingresosCatalogo as $ingreso)
            <option value="{{ $ingreso['Nro_Ingreso_MP'] }}">{{ $ingreso['Codigo_MP'] }}</option>
        @endforeach
    </datalist>

    <datalist id="of-catalogo-edit-list">
        <option value="{{ $pedidoMp->pedido->Nro_OF ?? '' }}">OF #{{ $pedidoMp->pedido->Nro_OF ?? '' }} - {{ $pedidoMp->pedido->producto->Prod_Codigo ?? '' }}</option>
        @foreach ($pedidosCatalogo as $pedido)
            <option value="{{ $pedido['Nro_OF'] }}">OF #{{ $pedido['Nro_OF'] }} - {{ $pedido['Prod_Codigo'] }}</option>
        @endforeach
    </datalist>

    <table class="table table-bordered custom-font centered-form pedido-mp-mass-table" id="tablaEditarPedidoMP">
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
            </tr>
        </thead>
        <tbody>
            <tr class="row-complete row-active">
                <td class="cell-index">1</td>
                <td>
                    <input type="text" class="form-control input-of" list="of-catalogo-edit-list" value="{{ $pedidoMp->pedido->Nro_OF ?? '' }}" {{ !empty($definitionLocked) ? 'readonly' : '' }}>
                    <input type="hidden" name="Id_OF" class="hidden-id-of" value="{{ $pedidoMp->Id_OF }}">
                    <input type="hidden" class="hidden-cantidad-fabricacion" value="{{ $pedidoMp->pedido->Cant_Fabricacion ?? 0 }}">
                    <input type="hidden" class="hidden-largo-pieza" value="{{ $pedidoMp->pedido->producto->Prod_Longitud_de_Pieza ?? '' }}">
                    <input type="hidden" class="hidden-codigo-mp-producto" value="{{ $pedidoMp->pedido->producto->Prod_Codigo_MP ?? '' }}">
                    <input type="hidden" class="hidden-producto-materia" value="{{ $pedidoMp->pedido->producto->Prod_Material_MP ?? '' }}">
                    <input type="hidden" class="hidden-producto-diametro" value="{{ $pedidoMp->pedido->producto->Prod_Diametro_de_MP ?? '' }}">
                    <input type="hidden" class="hidden-pedido-material" value="{{ $pedidoMp->Pedido_Material_Nro }}">
                    <input type="hidden" class="hidden-longitud-unidad" value="{{ $pedidoMp->Longitud_Un_MP ?? '' }}">
                    <input type="hidden" class="hidden-pedido-proveedor" value="">
                    <input type="hidden" class="hidden-materia-prima" value="{{ $pedidoMp->Materia_Prima ?? '' }}">
                    <input type="hidden" class="hidden-diametro-mp" value="{{ $pedidoMp->Diametro_MP ?? '' }}">
                </td>
                <td><input type="text" class="form-control producto-readonly" value="{{ $pedidoMp->pedido->producto->Prod_Codigo ?? '' }}" readonly></td>
                <td>
                    <select name="Id_Maquina" class="form-control filtro-select select-maquina" required {{ !empty($definitionLocked) ? 'disabled' : '' }}>
                        <option value="">Seleccione maquina</option>
                        @foreach ($maquinasCatalogo as $maquina)
                            <option value="{{ $maquina['id_maquina'] }}" data-nro="{{ $maquina['Nro_maquina'] }}" data-familia="{{ $maquina['familia_maquina'] }}" data-scrap="{{ $maquina['scrap_maquina'] }}" {{ (string) $pedidoMp->Id_Maquina === (string) $maquina['id_maquina'] ? 'selected' : '' }}>
                                {{ $maquina['Nro_maquina'] }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" class="form-control familia-maquina-readonly" value="{{ $pedidoMp->Familia_Maquina ?? '' }}" readonly></td>
                <td><input type="text" class="form-control codigo-mp-readonly" value="{{ $pedidoMp->Codigo_MP ?? '' }}" readonly></td>
                <td>
                    <div class="ingreso-selector-cell">
                        <input type="text" name="Nro_Ingreso_MP" class="form-control input-ingreso-mp" list="ingreso-mp-catalogo-list" placeholder="Buscar ingreso" value="{{ $pedidoMp->Nro_Ingreso_MP ?? '' }}" required {{ !empty($definitionLocked) ? 'disabled' : '' }}>
                        <button type="button" class="btn btn-info btn-sm consultar-sugeridos" {{ !empty($definitionLocked) ? 'disabled' : '' }}>Consultar ingresos sugeridos</button>
                    </div>
                </td>
                <td><input type="text" name="Pedido_Material_Nro" class="form-control pedido-material-readonly" value="{{ $pedidoMp->Pedido_Material_Nro }}" {{ !empty($definitionLocked) ? 'readonly' : '' }}></td>
                <td><input type="text" class="form-control certificado-readonly" value="{{ $pedidoMp->Nro_Certificado_MP ?? '' }}" readonly></td>
                <td><input type="text" class="form-control barras-readonly" value="{{ $pedidoMp->Cant_Barras_MP ?? '' }}" readonly></td>
                <td><input type="text" class="form-control estado-readonly" value="{{ $pedidoMp->estadoPlanificacion->Nombre_Estado ?? 'EN ANALISIS DE STOCK' }}" readonly></td>
            </tr>
        </tbody>
    </table>

    <div class="btn-der">
        <a href="{{ route('pedido_cliente_mp.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary" {{ !empty($definitionLocked) ? 'disabled' : '' }}>Guardar cambios</button>
    </div>
</form>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
$(document).ready(function () {
    const pedidosCatalogoBase = @json($pedidosCatalogo);
    const ingresosCatalogo = @json($ingresosCatalogo);
    const createSingleUrl = @json(route('pedido_cliente_mp.create'));
    const returnUrl = @json(route('pedido_cliente_mp.editMassive', $pedidoMp->Id_Pedido_MP));
    const selectionStorageKey = @json($editMassiveSelectionStorageKey);
    const draftStorageKey = @json($editMassiveDraftStorageKey);
    const rowPedidoInicial = {
        Id_OF: @json($pedidoMp->Id_OF),
        Nro_OF: @json($pedidoMp->pedido->Nro_OF ?? null),
        Prod_Codigo: @json($pedidoMp->pedido->producto->Prod_Codigo ?? ''),
        Cant_Fabricacion: @json((int) ($pedidoMp->pedido->Cant_Fabricacion ?? 0)),
        Largo_Pieza: @json($pedidoMp->pedido->producto->Prod_Longitud_de_Pieza ?? null),
        Codigo_MP: @json($pedidoMp->pedido->producto->Prod_Codigo_MP ?? $pedidoMp->Codigo_MP),
        Materia_Prima: @json($pedidoMp->pedido->producto->Prod_Material_MP ?? $pedidoMp->Materia_Prima),
        Diametro_MP: @json($pedidoMp->pedido->producto->Prod_Diametro_de_MP ?? $pedidoMp->Diametro_MP)
    };
    const pedidosCatalogo = (() => {
        const existeActual = pedidosCatalogoBase.some((pedido) => String(pedido.Id_OF) === String(rowPedidoInicial.Id_OF));
        if (existeActual) {
            return pedidosCatalogoBase;
        }

        return [{
            Id_OF: rowPedidoInicial.Id_OF,
            Nro_OF: rowPedidoInicial.Nro_OF,
            Prod_Codigo: rowPedidoInicial.Prod_Codigo,
            Cant_Fabricacion: rowPedidoInicial.Cant_Fabricacion,
            Largo_Pieza: rowPedidoInicial.Largo_Pieza,
            Codigo_MP: rowPedidoInicial.Codigo_MP,
            Materia_Prima: rowPedidoInicial.Materia_Prima,
            Diametro_MP: rowPedidoInicial.Diametro_MP,
        }, ...pedidosCatalogoBase];
    })();

    function parseNumber(value) {
        const normalized = String(value || '').replace(',', '.').trim();
        const number = parseFloat(normalized);
        return Number.isFinite(number) ? number : 0;
    }

    function formatInteger(value) {
        if (!Number.isFinite(value) || value <= 0) return '';
        return Math.ceil(value).toLocaleString('es-AR');
    }

    function splitCodigoMp(value) {
        const text = String(value || '').trim();
        if (!text.includes('_')) {
            return { materia: '', diametro: '' };
        }

        const [materia, ...rest] = text.split('_');
        return { materia: materia || '', diametro: rest.join('_') || '' };
    }

    function extractDiameter(value) {
        const text = String(value || '');
        const match = text.match(/[??]?\s*([0-9]+(?:[\.,][0-9]+)?)/u);
        return match ? parseFloat(String(match[1]).replace(',', '.')) : null;
    }

    function normalizeMateria(value) {
        return String(value || '').normalize('NFKC').replace(/\s+/g, '').trim().toUpperCase();
    }

    function findIngresoByNumber(nroIngreso) {
        return ingresosCatalogo.find((ingreso) => String(ingreso.Nro_Ingreso_MP) === String(nroIngreso).trim());
    }

    function findPedidoByOf(nroOf) {
        return pedidosCatalogo.find((pedido) => String(pedido.Nro_OF) === String(nroOf).trim());
    }

    function getCurrentPedidoData($fila) {
        return {
            Id_OF: String($fila.find('.hidden-id-of').val() || '').trim(),
            Nro_OF: String($fila.find('.input-of').val() || '').trim(),
            Prod_Codigo: String($fila.find('.producto-readonly').val() || '').trim(),
            Cant_Fabricacion: parseNumber($fila.find('.hidden-cantidad-fabricacion').val()),
            Largo_Pieza: $fila.find('.hidden-largo-pieza').val(),
            Codigo_MP: String($fila.find('.hidden-codigo-mp-producto').val() || '').trim() || String($fila.find('.codigo-mp-readonly').val() || '').trim(),
            Materia_Prima: String($fila.find('.hidden-producto-materia').val() || '').trim() || String($fila.find('.hidden-materia-prima').val() || '').trim(),
            Diametro_MP: String($fila.find('.hidden-producto-diametro').val() || '').trim() || String($fila.find('.hidden-diametro-mp').val() || '').trim(),
        };
    }

    function completarFilaConPedido($fila, pedido) {
        if (!pedido) {
            $fila.find('.hidden-id-of').val('');
            $fila.find('.hidden-cantidad-fabricacion').val('');
            $fila.find('.hidden-largo-pieza').val('');
            $fila.find('.hidden-codigo-mp-producto').val('');
            $fila.find('.hidden-producto-materia').val('');
            $fila.find('.hidden-producto-diametro').val('');
            $fila.find('.producto-readonly').val('');
            $fila.find('.codigo-mp-readonly').val('');
            $fila.find('.barras-readonly').val('');
            return;
        }

        $fila.find('.input-of').val(pedido.Nro_OF || '');
        $fila.find('.hidden-id-of').val(pedido.Id_OF || '');
        $fila.find('.hidden-cantidad-fabricacion').val(pedido.Cant_Fabricacion || '');
        $fila.find('.hidden-largo-pieza').val(pedido.Largo_Pieza || '');
        $fila.find('.hidden-codigo-mp-producto').val(pedido.Codigo_MP || '');
        $fila.find('.hidden-producto-materia').val(pedido.Materia_Prima || '');
        $fila.find('.hidden-producto-diametro').val(pedido.Diametro_MP || '');
        $fila.find('.producto-readonly').val(pedido.Prod_Codigo || '');
        $fila.find('.codigo-mp-readonly').val(pedido.Codigo_MP || '');
    }

    function isCompatibleIngresoWithPedido(pedido, ingreso) {
        if (!pedido || !ingreso) return false;
        const pedidoParts = splitCodigoMp(pedido.Codigo_MP || '');
        const ingresoParts = splitCodigoMp(ingreso.Codigo_MP || '');
        const materiaPedido = normalizeMateria(pedido.Materia_Prima || pedidoParts.materia);
        const materiaIngreso = normalizeMateria(ingreso.Materia_Prima || ingresoParts.materia);
        if (materiaPedido && materiaIngreso && materiaPedido !== materiaIngreso) return false;
        const diametroPedido = extractDiameter(pedido.Diametro_MP || pedidoParts.diametro || pedido.Codigo_MP || '');
        const diametroIngreso = extractDiameter(ingreso.Diametro_MP || ingresoParts.diametro || ingreso.Codigo_MP || '');
        if (diametroPedido === null || diametroIngreso === null) return true;
        return diametroIngreso >= diametroPedido;
    }

    function calcularBarras(pedido, maquina, ingreso) {
        if (!pedido || !maquina || !ingreso) return '';
        const largoPieza = parseNumber(pedido.Largo_Pieza);
        const largoTotal = largoPieza + 0.50 + 1.00 + 0.50;
        const mmTotales = parseNumber(pedido.Cant_Fabricacion) * largoTotal;
        const longitudBarraSinScrap = (parseNumber(ingreso.Longitud_Unidad_MP) * 1000) - parseNumber(maquina.scrap);
        if (mmTotales <= 0 || longitudBarraSinScrap <= 0) return '';
        return formatInteger(mmTotales / longitudBarraSinScrap);
    }

    function getSelectedMachineData($fila) {
        const $option = $fila.find('.select-maquina option:selected');
        return {
            id: $option.val() || '',
            nro: $option.data('nro') || '',
            familia: $option.data('familia') || '',
            scrap: $option.data('scrap') || 0
        };
    }

    function saveDraft() {
        const $fila = $('#tablaEditarPedidoMP tbody tr').first();
        const payload = {
            idOf: String($fila.find('.hidden-id-of').val() || '').trim(),
            nroOf: String($fila.find('.input-of').val() || '').trim(),
            machineId: String($fila.find('.select-maquina').val() || '').trim(),
            nroIngreso: String($fila.find('.input-ingreso-mp').val() || '').trim(),
            certificado: String($fila.find('.certificado-readonly').val() || '').trim(),
            pedidoMaterial: String($fila.find('.pedido-material-readonly').val() || '').trim(),
            barras: String($fila.find('.barras-readonly').val() || '').trim(),
            codigoMp: String($fila.find('.codigo-mp-readonly').val() || '').trim(),
            longitudUnMp: String($fila.find('.hidden-longitud-unidad').val() || '').trim(),
            pedidoProveedor: String($fila.find('.hidden-pedido-proveedor').val() || '').trim(),
            materiaPrima: String($fila.find('.hidden-materia-prima').val() || '').trim(),
            diametroMp: String($fila.find('.hidden-diametro-mp').val() || '').trim()
        };
        sessionStorage.setItem(draftStorageKey, JSON.stringify(payload));
    }

    function restoreDraft() {
        const raw = sessionStorage.getItem(draftStorageKey);
        if (!raw) return;
        sessionStorage.removeItem(draftStorageKey);
        try {
            const draft = JSON.parse(raw);
            const $fila = $('#tablaEditarPedidoMP tbody tr').first();
            if (draft.nroOf) {
                const pedido = findPedidoByOf(draft.nroOf);
                if (pedido) {
                    completarFilaConPedido($fila, pedido);
                } else {
                    $fila.find('.input-of').val(draft.nroOf);
                    $fila.find('.hidden-id-of').val(draft.idOf || '');
                }
            }
            $fila.find('.select-maquina').val(draft.machineId || '').trigger('change');
            $fila.find('.input-ingreso-mp').val(draft.nroIngreso || '');
            if (draft.nroIngreso) {
                actualizarFilaConIngreso($fila, false);
            }
            if (draft.certificado) $fila.find('.certificado-readonly').val(draft.certificado);
            if (draft.pedidoMaterial) $fila.find('.pedido-material-readonly').val(draft.pedidoMaterial);
            if (draft.barras) $fila.find('.barras-readonly').val(draft.barras);
            if (draft.codigoMp) $fila.find('.codigo-mp-readonly').val(draft.codigoMp);
            if (draft.longitudUnMp) $fila.find('.hidden-longitud-unidad').val(draft.longitudUnMp);
            if (draft.pedidoProveedor) $fila.find('.hidden-pedido-proveedor').val(draft.pedidoProveedor);
            if (draft.materiaPrima) $fila.find('.hidden-materia-prima').val(draft.materiaPrima);
            if (draft.diametroMp) $fila.find('.hidden-diametro-mp').val(draft.diametroMp);
        } catch (error) {}
    }

    function actualizarFilaConMaquina($fila) {
        const maquina = getSelectedMachineData($fila);
        $fila.find('.familia-maquina-readonly').val(maquina.familia || '');
        recalcularBarrasFila($fila);
    }

    function actualizarFilaConIngreso($fila, showError = true) {
        const ingreso = findIngresoByNumber($fila.find('.input-ingreso-mp').val());
        if (!ingreso) {
            $fila.find('.input-ingreso-mp').addClass('input-invalid');
            $fila.find('.certificado-readonly').val('');
            $fila.find('.barras-readonly').val('');
            if (showError) SwalUtils.error('El ingreso seleccionado no existe.');
            return false;
        }
        const pedidoActual = getCurrentPedidoData($fila);
        if (!isCompatibleIngresoWithPedido(pedidoActual, ingreso)) {
            $fila.find('.input-ingreso-mp').addClass('input-invalid');
            if (showError) SwalUtils.error('El ingreso seleccionado no es compatible con la OF.');
            return false;
        }
        $fila.find('.input-ingreso-mp').removeClass('input-invalid');
        $fila.find('.codigo-mp-readonly').val(ingreso.Codigo_MP || pedidoActual.Codigo_MP || '');
        $fila.find('.certificado-readonly').val(ingreso.Nro_Certificado_MP || '');
        $fila.find('.hidden-longitud-unidad').val(ingreso.Longitud_Unidad_MP || '');
        $fila.find('.hidden-pedido-proveedor').val(ingreso.Nro_Pedido_Proveedor || ingreso.Nro_Pedido || '');
        const ingresoParts = splitCodigoMp(ingreso.Codigo_MP || '');
        $fila.find('.hidden-materia-prima').val(ingreso.Materia_Prima || ingresoParts.materia || '');
        $fila.find('.hidden-diametro-mp').val(ingreso.Diametro_MP || ingresoParts.diametro || '');
        recalcularBarrasFila($fila);
        return true;
    }

    function recalcularBarrasFila($fila) {
        const maquina = getSelectedMachineData($fila);
        const ingreso = findIngresoByNumber($fila.find('.input-ingreso-mp').val());
        const pedidoActual = getCurrentPedidoData($fila);
        $fila.find('.barras-readonly').val(calcularBarras(pedidoActual, maquina, ingreso));
    }

    function applySelectionFromStorage() {
        const raw = sessionStorage.getItem(selectionStorageKey);
        if (!raw) return;
        sessionStorage.removeItem(selectionStorageKey);
        try {
            const selection = JSON.parse(raw);
            const $fila = $('#tablaEditarPedidoMP tbody tr').first();
            if (selection.nroOf) {
                const pedido = findPedidoByOf(selection.nroOf);
                if (pedido) {
                    completarFilaConPedido($fila, pedido);
                }
            }
            if (selection.machineId) $fila.find('.select-maquina').val(selection.machineId).trigger('change');
            if (selection.nroIngreso) {
                $fila.find('.input-ingreso-mp').val(selection.nroIngreso);
                actualizarFilaConIngreso($fila, false);
            }
            if (selection.certificado) $fila.find('.certificado-readonly').val(selection.certificado);
            if (selection.pedidoMaterial) $fila.find('.pedido-material-readonly').val(selection.pedidoMaterial);
            if (selection.longitudUnMp) $fila.find('.hidden-longitud-unidad').val(selection.longitudUnMp);
            if (selection.pedidoProveedor) $fila.find('.hidden-pedido-proveedor').val(selection.pedidoProveedor);
            if (selection.materiaPrima) $fila.find('.hidden-materia-prima').val(selection.materiaPrima);
            if (selection.diametroMp) $fila.find('.hidden-diametro-mp').val(selection.diametroMp);
            if (selection.codigoMp) $fila.find('.codigo-mp-readonly').val(selection.codigoMp);
            saveDraft();
            SwalUtils.success('Ingreso sugerido aplicado a la hoja de edicion.');
        } catch (error) {}
    }

    $(document).on('change blur', '.input-of', function () {
        const $fila = $(this).closest('tr');
        const pedido = findPedidoByOf($(this).val());

        if (!pedido && String($(this).val() || '').trim() !== '') {
            $fila.find('.hidden-id-of').val('');
            $fila.find('.producto-readonly').val('');
            $fila.find('.codigo-mp-readonly').val('');
            $fila.find('.barras-readonly').val('');
            $(this).addClass('input-invalid');
            SwalUtils.error('La OF seleccionada no es valida para esta hoja.');
            return;
        }

        $(this).removeClass('input-invalid');
        if (pedido) {
            completarFilaConPedido($fila, pedido);
        }

        if (String($fila.find('.input-ingreso-mp').val() || '').trim() !== '') {
            actualizarFilaConIngreso($fila, false);
        } else {
            recalcularBarrasFila($fila);
        }

        saveDraft();
    });

    $(document).on('change', '.select-maquina', function () {
        actualizarFilaConMaquina($(this).closest('tr'));
        saveDraft();
    });

    $(document).on('change blur', '.input-ingreso-mp', function () {
        actualizarFilaConIngreso($(this).closest('tr'), false);
        saveDraft();
    });

    $(document).on('keydown', '.input-ingreso-mp', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            actualizarFilaConIngreso($(this).closest('tr'));
            saveDraft();
        }
    });

    $(document).on('click', '.consultar-sugeridos', function () {
        const $fila = $(this).closest('tr');
        const machineId = String($fila.find('.select-maquina').val() || '').trim();
        if (!machineId) {
            SwalUtils.error('Primero selecciona una maquina para poder consultar ingresos sugeridos.');
            return;
        }
        const pedidoActual = getCurrentPedidoData($fila);
        if (!pedidoActual.Id_OF) {
            SwalUtils.error('Primero selecciona una OF valida.');
            return;
        }
        saveDraft();

        const params = new URLSearchParams({
            of: String(pedidoActual.Id_OF),
            from_massive: '1',
            machine: machineId,
            row: '1',
            return_url: returnUrl,
            storage_key: selectionStorageKey,
        });

        const selectedIngreso = String($fila.find('.input-ingreso-mp').val() || '').trim();
        const selectedCertificado = String($fila.find('.certificado-readonly').val() || '').trim();
        const selectedPedidoMaterial = String($fila.find('.pedido-material-readonly').val() || '').trim();
        const selectedPedidoProveedor = String($fila.find('.hidden-pedido-proveedor').val() || '').trim();
        const selectedLongitudUnMp = String($fila.find('.hidden-longitud-unidad').val() || '').trim();
        const selectedCodigoMp = String($fila.find('.codigo-mp-readonly').val() || '').trim();
        const selectedMateriaPrima = String($fila.find('.hidden-materia-prima').val() || '').trim();
        const selectedDiametroMp = String($fila.find('.hidden-diametro-mp').val() || '').trim();

        if (selectedIngreso) params.set('selected_ingreso', selectedIngreso);
        if (selectedCertificado) params.set('selected_certificado', selectedCertificado);
        if (selectedPedidoMaterial) params.set('selected_pedido_material', selectedPedidoMaterial);
        if (selectedPedidoProveedor) params.set('selected_pedido_proveedor', selectedPedidoProveedor);
        if (selectedLongitudUnMp) params.set('selected_longitud_un_mp', selectedLongitudUnMp);
        if (selectedCodigoMp) params.set('selected_codigo_mp', selectedCodigoMp);
        if (selectedMateriaPrima) params.set('selected_materia_prima', selectedMateriaPrima);
        if (selectedDiametroMp) params.set('selected_diametro_mp', selectedDiametroMp);
        window.location.href = `${createSingleUrl}?${params.toString()}`;
    });

    $('form').on('submit', function (event) {
        const $fila = $('#tablaEditarPedidoMP tbody tr').first();
        if (!String($fila.find('.hidden-id-of').val() || '').trim()) {
            event.preventDefault();
            SwalUtils.error('Debes seleccionar una OF valida.');
            return;
        }
        if (!String($fila.find('.select-maquina').val() || '').trim()) {
            event.preventDefault();
            SwalUtils.error('Debes seleccionar una maquina.');
            return;
        }
        if (!String($fila.find('.input-ingreso-mp').val() || '').trim()) {
            event.preventDefault();
            SwalUtils.error('Debes seleccionar un ingreso MP.');
            return;
        }
        if (!actualizarFilaConIngreso($fila, true)) {
            event.preventDefault();
        }
    });

    restoreDraft();
    applySelectionFromStorage();
    actualizarFilaConMaquina($('#tablaEditarPedidoMP tbody tr').first());
});
</script>
@stop







