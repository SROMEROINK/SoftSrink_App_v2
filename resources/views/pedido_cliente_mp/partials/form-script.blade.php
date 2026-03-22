<script>
$(document).ready(function () {
    const $of = $('#Id_OF');
    const $maquina = $('#Id_Maquina');
    const $nroMaquina = $('#Nro_Maquina');
    const $familiaMaquina = $('#Familia_Maquina');
    const $scrapMaquina = $('#Scrap_Maquina');
    const $materia = $('#Materia_Prima');
    const $diametro = $('#Diametro_MP');
    const $codigo = $('#Codigo_MP');
    const $largoPieza = $('#Largo_Pieza');
    const $frenteado = $('#Frenteado');
    const $anchoCutOff = $('#Ancho_Cut_Off');
    const $sobrematerial = $('#Sobrematerial_Promedio');
    const $largoTotal = $('#Largo_Total_Pieza');
    const $mmTotales = $('#MM_Totales');
    const $longitudUnMp = $('#Longitud_Un_MP');
    const $longitudBarraSinScrap = $('#Longitud_Barra_Sin_Scrap');
    const $cantBarras = $('#Cant_Barras_MP');
    const $cantPiezasPorBarra = $('#Cant_Piezas_Por_Barra');
    const $consultarStock = $('#consultar_stock_mp');
    const $nroIngresoMp = $('#Nro_Ingreso_MP');
    const $pedidoMaterialNro = $('#Pedido_Material_Nro');
    const $nroCertificadoMp = $('#Nro_Certificado_MP');
    const $compatibilidadTexto = $('#compatibilidad_producto_texto');
    const $stockBody = $('#stock_ingresos_body');
    const $stockFaltante = $('#stock_faltante_texto');
    const $stockMetrosReq = $('#stock_metros_requeridos');
    const $stockBarrasReq = $('#stock_barras_requeridas');
    const $stockBarrasDisp = $('#stock_barras_disponibles');
    const $stockMetrosDisp = $('#stock_metros_disponibles');

    const todasLasMaterias = $materia.find('option').map(function () {
        return { value: $(this).val(), text: $(this).text() };
    }).get();
    const todosLosDiametros = $diametro.find('option').map(function () {
        return { value: $(this).val(), text: $(this).text() };
    }).get();

    function parseNumber(value) {
        const normalized = String(value || '').replace(',', '.').trim();
        const number = parseFloat(normalized);
        return Number.isFinite(number) ? number : 0;
    }

    function formatNumber(value, decimals = 2) {
        if (!Number.isFinite(value)) return '-';
        return value.toLocaleString('es-AR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    }

    function formatInteger(value) {
        if (!Number.isFinite(value)) return '';
        return String(Math.ceil(value));
    }

    function getSelectedOption() {
        return $of.find('option:selected');
    }

    function getSelectedMachineOption() {
        return $maquina.find('option:selected');
    }

    function resetStockPanel(message = 'Selecciona una OF y consulta ingresos compatibles.') {
        $stockBody.html(`<tr><td colspan="7" class="text-center text-muted">${message}</td></tr>`);
        $stockFaltante.text('Sin consulta todavia.');
        $stockMetrosReq.text('-');
        $stockBarrasReq.text('-');
        $stockBarrasDisp.text('-');
        $stockMetrosDisp.text('-');
    }

    function fillSelect($select, options, selectedValue = '', lock = false) {
        const placeholder = 'Sin definir';
        $select.empty().append(`<option value="">${placeholder}</option>`);
        options.forEach(option => {
            $select.append(`<option value="${option.value}">${option.text}</option>`);
        });
        $select.val(selectedValue || '');
        $select.attr('data-locked', lock ? '1' : '0');
    }

    function updateMachineSummary() {
        const option = getSelectedMachineOption();
        $nroMaquina.val(option.data('nro') || '');
        $familiaMaquina.val(option.data('familia') || '');
        $scrapMaquina.val(option.data('scrap') || '');
    }

    function updateCompatibilityFromProduct() {
        const option = getSelectedOption();
        const productoMaterial = (option.data('producto-material-mp') || '').toString().trim();
        const productoDiametro = (option.data('producto-diametro-mp') || '').toString().trim();
        const productoCodigo = (option.data('producto-codigo-mp') || '').toString().trim();
        const productoLargoPieza = option.data('producto-largo-pieza');

        if (!$largoPieza.val() && productoLargoPieza) {
            $largoPieza.val(productoLargoPieza);
        }

        if (productoCodigo || (productoMaterial && productoDiametro)) {
            const materias = productoMaterial ? [{ value: productoMaterial, text: productoMaterial }] : [];
            const diametros = productoDiametro ? [{ value: productoDiametro, text: productoDiametro }] : [];
            fillSelect($materia, materias, productoMaterial, !!productoMaterial);
            fillSelect($diametro, diametros, productoDiametro, !!productoDiametro);
            const texto = productoCodigo
                ? `El producto exige el Codigo MP ${productoCodigo}. Solo se sugeriran ingresos compatibles con ese codigo.`
                : `El producto exige la combinacion ${productoMaterial} / ${productoDiametro}.`;
            $compatibilidadTexto.text(texto);
        } else {
            fillSelect($materia, todasLasMaterias, $materia.val(), false);
            fillSelect($diametro, todosLosDiametros, $diametro.val(), false);
            $compatibilidadTexto.text('El producto no tiene MP fija. Puedes definir manualmente la materia prima compatible en esta etapa.');
        }

        updateCodigoMp();
    }

    function updatePedidoResumen() {
        const option = getSelectedOption();
        const cantidad = parseNumber(option.data('cantidad'));
        $('#detalle_nro_of').text(option.data('nro-of') || '-');
        $('#detalle_producto').text(option.data('producto') || '-');
        $('#detalle_categoria').text(option.data('categoria') || '-');
        $('#detalle_subcategoria').text(option.data('subcategoria') || '-');
        $('#detalle_descripcion').text(option.data('descripcion') || '-');
        $('#detalle_fecha').text(option.data('fecha') || '-');
        $('#detalle_cantidad').text(cantidad ? cantidad.toLocaleString('es-AR') : '-');
        updateCompatibilityFromProduct();
        recalculateFields();
    }

    function updateCodigoMp() {
        const materia = $materia.val();
        const diametro = $diametro.val();
        $codigo.val(materia && diametro ? `${materia}_${diametro}` : '');
    }

    function recalculateFields() {
        updateCodigoMp();
        updateMachineSummary();

        const cantidadPedido = parseNumber(getSelectedOption().data('cantidad'));
        const largoTotalPieza = parseNumber($largoPieza.val()) + parseNumber($frenteado.val()) + parseNumber($anchoCutOff.val()) + parseNumber($sobrematerial.val());
        const mmTotales = cantidadPedido > 0 && largoTotalPieza > 0 ? cantidadPedido * largoTotalPieza : 0;
        const longitudBarraSinScrap = Math.max(0, (parseNumber($longitudUnMp.val()) * 1000) - parseNumber($scrapMaquina.val()));

        $largoTotal.val(largoTotalPieza ? largoTotalPieza.toFixed(2) : '');
        $mmTotales.val(mmTotales ? mmTotales.toFixed(2) : '');
        $longitudBarraSinScrap.val(longitudBarraSinScrap ? longitudBarraSinScrap.toFixed(2) : '');

        if (mmTotales > 0 && longitudBarraSinScrap > 0) {
            $cantBarras.val(formatInteger(mmTotales / longitudBarraSinScrap));
        } else {
            $cantBarras.val('');
        }

        if (largoTotalPieza > 0 && longitudBarraSinScrap > 0) {
            const piezasPorBarra = Math.floor((longitudBarraSinScrap / largoTotalPieza) * 100) / 100;
            $cantPiezasPorBarra.val(piezasPorBarra.toFixed(2));
        } else {
            $cantPiezasPorBarra.val('');
        }
    }

    function fillIngresoSeleccionado(ingreso) {
        $nroIngresoMp.val(ingreso.Nro_Ingreso_MP ?? '');
        $nroCertificadoMp.val(ingreso.Nro_Certificado_MP ?? '');

        if (ingreso.Longitud_Unidad_MP !== undefined && ingreso.Longitud_Unidad_MP !== null && ingreso.Longitud_Unidad_MP !== '') {
            $longitudUnMp.val(Number(ingreso.Longitud_Unidad_MP).toFixed(2));
        }

        recalculateFields();
    }

    function getIngresoDataFromRow($row) {
        return {
            Nro_Ingreso_MP: $row.attr('data-nro-ingreso-mp') || '',
            Pedido_Material_Nro: $row.attr('data-pedido-material-nro') || '',
            Nro_Certificado_MP: $row.attr('data-nro-certificado-mp') || '',
            Longitud_Unidad_MP: $row.attr('data-longitud-unidad-mp') || ''
        };
    }

    function renderIngresos(data) {
        const ingresos = data.ingresos || [];
        const stock = data.stock || {};
        const seleccion = data.seleccion || {};
        const maquina = data.maquina || {};

        if (seleccion.longitud_un_mp && !$longitudUnMp.val()) {
            $longitudUnMp.val(Number(seleccion.longitud_un_mp).toFixed(2));
        }
        if (seleccion.longitud_barra_sin_scrap) {
            $longitudBarraSinScrap.val(Number(seleccion.longitud_barra_sin_scrap).toFixed(2));
        }
        if (seleccion.cant_barras_requeridas) {
            $cantBarras.val(seleccion.cant_barras_requeridas);
        }
        if (seleccion.cant_piezas_por_barra) {
            $cantPiezasPorBarra.val(Number(seleccion.cant_piezas_por_barra).toFixed(2));
        }
        if (maquina.scrap_maquina && !$scrapMaquina.val()) {
            $scrapMaquina.val(Number(maquina.scrap_maquina).toFixed(2));
        }

        $stockMetrosReq.text(stock.metros_requeridos ? `${formatNumber(Number(stock.metros_requeridos))} m` : '-');
        $stockBarrasReq.text(stock.cant_barras_requeridas ?? '-');
        $stockBarrasDisp.text(stock.total_barras_disponibles ?? '-');
        $stockMetrosDisp.text(stock.total_metros_disponibles ? `${formatNumber(Number(stock.total_metros_disponibles))} m` : '-');

        if ((stock.faltan_barras || 0) > 0 || Number(stock.faltan_metros || 0) > 0) {
            $stockFaltante.text(`Faltan ${stock.faltan_barras || 0} barras aprox. / ${formatNumber(Number(stock.faltan_metros || 0))} m para cubrir el pedido.`);
        } else {
            $stockFaltante.text('El stock compatible alcanza para cubrir la necesidad actual del pedido.');
        }

        if (!ingresos.length) {
            $stockBody.html('<tr><td colspan="7" class="text-center text-muted">No se encontraron ingresos compatibles para este Codigo MP.</td></tr>');
            return;
        }

        const sugerencias = {};
        (stock.sugerencia || []).forEach(item => { sugerencias[item.Nro_Ingreso_MP] = item; });
        const rows = ingresos.map(ingreso => {
            const sugerencia = sugerencias[ingreso.Nro_Ingreso_MP];
            const sugerenciaTexto = sugerencia
                ? `Usar ${sugerencia.barras_a_usar} barras. Remanente: ${sugerencia.barras_remanentes}.`
                : 'Sin asignacion sugerida';

            return `
                <tr class="js-ingreso-sugerido"
                    data-nro-ingreso-mp="${ingreso.Nro_Ingreso_MP || ''}"
                    data-pedido-material-nro="${ingreso.Pedido_Material_Nro || ''}"
                    data-nro-certificado-mp="${ingreso.Nro_Certificado_MP || ''}"
                    data-longitud-unidad-mp="${ingreso.Longitud_Unidad_MP || ''}"
                    title="Seleccionar este ingreso">
                    <td><button type="button" class="btn btn-link btn-sm p-0 js-seleccionar-ingreso">${ingreso.Nro_Ingreso_MP}</button></td>
                    <td>${ingreso.Codigo_MP || '-'}</td>
                    <td>${ingreso.Nro_Certificado_MP || '-'}</td>
                    <td>${Number(ingreso.Unidades_MP || 0).toLocaleString('es-AR')}</td>
                    <td>${formatNumber(Number(ingreso.Longitud_Unidad_MP || 0))}</td>
                    <td>${formatNumber(Number(ingreso.Mts_Totales || 0))}</td>
                    <td>${sugerenciaTexto}</td>
                </tr>`;
        }).join('');
        $stockBody.html(rows);
    }

    function selectIngresoRow($row) {
        $stockBody.find('.js-ingreso-sugerido').removeClass('is-selected');
        $row.addClass('is-selected');
        fillIngresoSeleccionado(getIngresoDataFromRow($row));
    }

    $stockBody.on('click', '.js-ingreso-sugerido', function () {
        selectIngresoRow($(this));
    });

    $stockBody.on('click', '.js-seleccionar-ingreso', function (event) {
        event.preventDefault();
        event.stopPropagation();
        selectIngresoRow($(this).closest('.js-ingreso-sugerido'));
    });

    function consultarStock() {
        const idOf = $of.val();
        if (!idOf) {
            resetStockPanel('Selecciona una OF antes de consultar ingresos compatibles.');
            return;
        }

        $.get(@json(route('pedido_cliente_mp.planner')), {
            id_of: idOf,
            Id_Maquina: $maquina.val(),
            Scrap_Maquina: $scrapMaquina.val(),
            Materia_Prima: $materia.val(),
            Diametro_MP: $diametro.val(),
            Codigo_MP: $codigo.val(),
            Longitud_Un_MP: $longitudUnMp.val(),
            Largo_Pieza: $largoPieza.val(),
            Frenteado: $frenteado.val(),
            Ancho_Cut_Off: $anchoCutOff.val(),
            Sobrematerial_Promedio: $sobrematerial.val()
        }).done(function (response) {
            if (response.compatibilidad && response.compatibilidad.mensaje) {
                $compatibilidadTexto.text(response.compatibilidad.mensaje);
            }
            renderIngresos(response);
            recalculateFields();
        }).fail(function () {
            resetStockPanel('No se pudo consultar el stock compatible en este momento.');
        });
    }

    $of.on('change', function () { updatePedidoResumen(); resetStockPanel(); });
    $maquina.on('change', function () { recalculateFields(); resetStockPanel('La maquina cambio. Vuelve a consultar ingresos compatibles.'); });
    $materia.on('change', function () { recalculateFields(); resetStockPanel('Los datos cambiaron. Vuelve a consultar ingresos compatibles.'); });
    $diametro.on('change', function () { recalculateFields(); resetStockPanel('Los datos cambiaron. Vuelve a consultar ingresos compatibles.'); });
    $('.js-calc').on('input change', function () { recalculateFields(); resetStockPanel('Los calculos cambiaron. Vuelve a consultar ingresos compatibles.'); });
    $consultarStock.on('click', consultarStock);

    updateMachineSummary();
    updatePedidoResumen();
});
</script>
