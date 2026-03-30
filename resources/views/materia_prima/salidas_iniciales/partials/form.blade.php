@php
    $selectedIngreso = old('Id_Ingreso_MP', $salidaInicial->Id_Ingreso_MP ?? $selectedIngresoId ?? null);
    $selectedIngresoModel = $ingresos->firstWhere('Id_MP', (int) $selectedIngreso);
    $stockInicial = old('Stock_Inicial', $salidaInicial->Stock_Inicial ?? $selectedIngresoModel->Unidades_MP ?? 0);
    $devolucionesProveedor = old('Devoluciones_Proveedor', $salidaInicial->Devoluciones_Proveedor ?? $salidaInicial->Devoluciones_Proveedor_Calculadas ?? 0);
    $ajusteStock = old('Ajuste_Stock', $salidaInicial->Ajuste_Stock ?? $salidaInicial->Ajuste_Stock_Calculado ?? 0);
    $estadoRegistro = old('reg_Status', isset($salidaInicial) ? (int) $salidaInicial->reg_Status : 1);
@endphp

<input type="hidden" name="return_to" value="{{ request('return_to', 'salidas_iniciales') }}">

<div class="card show-card mt-3 salida-inicial-form-card">
    <div class="show-card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">{{ isset($salidaInicial) ? 'Editar ajuste inicial de MP' : 'Nuevo ajuste inicial de MP' }}</h3>
            <div class="pedido-show-subtitle">Ajuste historico para cuadrar el stock real por ingreso de materia prima.</div>
        </div>
        <span class="badge detail-badge pedido-estado-badge">
            {{ isset($salidaInicial) ? 'AJUSTE CARGADO' : 'PENDIENTE AJUSTE' }}
        </span>
    </div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="Id_Ingreso_MP">Ingreso MP Seleccionado</label>
                    <select name="Id_Ingreso_MP" id="Id_Ingreso_MP" class="form-control filtro-select" required @if(isset($salidaInicial)) disabled @endif>
                        <option value="">Seleccionar ingreso</option>
                        @foreach ($ingresos as $ingreso)
                            <option
                                value="{{ $ingreso->Id_MP }}"
                                data-nro-ingreso="{{ $ingreso->Nro_Ingreso_MP }}"
                                data-codigo="{{ $ingreso->Codigo_MP }}"
                                data-certificado="{{ $ingreso->Nro_Certificado_MP }}"
                                data-proveedor="{{ $ingreso->proveedor->Prov_Nombre ?? '' }}"
                                data-materia="{{ $ingreso->materiaPrima->Nombre_Materia ?? '' }}"
                                data-diametro="{{ $ingreso->diametro->Valor_Diametro ?? '' }}"
                                data-unidades="{{ $ingreso->Unidades_MP ?? 0 }}"
                                data-longitud="{{ $ingreso->Longitud_Unidad_MP ?? 0 }}"
                                data-metros="{{ $ingreso->Mts_Totales ?? 0 }}"
                                @selected((int) $selectedIngreso === (int) $ingreso->Id_MP)
                            >
                                Ingreso #{{ $ingreso->Nro_Ingreso_MP }} - {{ $ingreso->Codigo_MP }}
                            </option>
                        @endforeach
                    </select>
                    @if(isset($salidaInicial))
                        <input type="hidden" name="Id_Ingreso_MP" value="{{ $salidaInicial->Id_Ingreso_MP }}">
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="reg_Status">Estado del Registro</label>
                    <select name="reg_Status" id="reg_Status" class="form-control filtro-select" required>
                        <option value="1" @selected((int) $estadoRegistro === 1)>Activo</option>
                        <option value="0" @selected((int) $estadoRegistro === 0)>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Nro Ingreso MP</span><div class="detail-value" id="detalle_nro_ingreso">{{ $selectedIngresoModel->Nro_Ingreso_MP ?? '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Codigo MP</span><div class="detail-value" id="detalle_codigo">{{ $selectedIngresoModel->Codigo_MP ?? '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Materia Prima</span><div class="detail-value" id="detalle_materia">{{ $selectedIngresoModel->materiaPrima->Nombre_Materia ?? '-' }}</div></div></div>
            <div class="col-md-3"><div class="detail-item"><span class="detail-label">Diametro MP</span><div class="detail-value" id="detalle_diametro">{{ $selectedIngresoModel->diametro->Valor_Diametro ?? '-' }}</div></div></div>
        </div>

        <div class="row">
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Proveedor</span><div class="detail-value" id="detalle_proveedor">{{ $selectedIngresoModel->proveedor->Prov_Nombre ?? '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Certificado</span><div class="detail-value" id="detalle_certificado">{{ $selectedIngresoModel->Nro_Certificado_MP ?? '-' }}</div></div></div>
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Longitud por unidad</span><div class="detail-value" id="detalle_longitud">{{ $selectedIngresoModel ? number_format((float) $selectedIngresoModel->Longitud_Unidad_MP, 2, ',', '.') : (isset($salidaInicial) ? number_format((float) $salidaInicial->Longitud_Unidad_Calculada, 2, ',', '.') : '0,00') }}</div></div></div>
        </div>

        <div class="alert alert-light formula-ajuste-alert">
            <strong>Formula del ajuste:</strong>
            <span>Salidas final = stock inicial - devoluciones al proveedor + diferencia de stock. Si la diferencia es negativa, el resultado baja; si es positiva, sube.</span>
            <span>Mts. Totales = salidas final x longitud por unidad.</span>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Stock_Inicial">Stock inicial</label>
                    <input type="number" name="Stock_Inicial" id="Stock_Inicial" class="form-control text-center" min="0" step="1" value="{{ $stockInicial }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Devoluciones_Proveedor">Devoluciones al proveedor</label>
                    <input type="number" name="Devoluciones_Proveedor" id="Devoluciones_Proveedor" class="form-control text-center" min="0" step="1" value="{{ $devolucionesProveedor }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Ajuste_Stock">Diferencia de Stock</label>
                    <input type="number" name="Ajuste_Stock" id="Ajuste_Stock" class="form-control text-center" step="1" value="{{ $ajusteStock }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Total_Salidas_MP">Salidas Final</label>
                    <input type="text" id="Total_Salidas_MP" class="form-control text-center" value="{{ isset($salidaInicial) ? number_format((int) ($salidaInicial->Total_Salidas_Calculadas ?? 0), 0, ',', '.') : '0' }}" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Total_mm_Utilizados">Mts. Totales</label>
                    <input type="text" id="Total_mm_Utilizados" class="form-control text-center" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="show-card-footer">
        <div class="show-actions">
            <a href="{{ request('return_to') === 'stock_mp' ? route('mp_stock.index') : route('mp_salidas_iniciales.index') }}" class="btn btn-secondary">Volver</a>
            <button type="submit" class="btn btn-primary">{{ isset($salidaInicial) ? 'Actualizar' : 'Guardar' }}</button>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ingresoSelect = document.getElementById('Id_Ingreso_MP');
    const stockInicialInput = document.getElementById('Stock_Inicial');
    const devolucionesProveedorInput = document.getElementById('Devoluciones_Proveedor');
    const ajusteStockInput = document.getElementById('Ajuste_Stock');
    const totalSalidasInput = document.getElementById('Total_Salidas_MP');
    const totalMtsAjustadosInput = document.getElementById('Total_mm_Utilizados');
    const detalleNroIngreso = document.getElementById('detalle_nro_ingreso');
    const detalleCodigo = document.getElementById('detalle_codigo');
    const detalleMateria = document.getElementById('detalle_materia');
    const detalleDiametro = document.getElementById('detalle_diametro');
    const detalleProveedor = document.getElementById('detalle_proveedor');
    const detalleCertificado = document.getElementById('detalle_certificado');
    const detalleLongitud = document.getElementById('detalle_longitud');

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

    function updateIngresoDetails() {
        const option = ingresoSelect ? ingresoSelect.selectedOptions[0] : null;
        if (!option || !option.value) {
            detalleNroIngreso.textContent = '-';
            detalleCodigo.textContent = '-';
            detalleMateria.textContent = '-';
            detalleDiametro.textContent = '-';
            detalleProveedor.textContent = '-';
            detalleCertificado.textContent = '-';
            detalleLongitud.textContent = '0,00';
            totalSalidasInput.value = '0';
            totalMtsAjustadosInput.value = '0,00';
            return;
        }

        const longitud = parseNumber(option.dataset.longitud);
        detalleNroIngreso.textContent = option.dataset.nroIngreso || '-';
        detalleCodigo.textContent = option.dataset.codigo || '-';
        detalleMateria.textContent = option.dataset.materia || '-';
        detalleDiametro.textContent = option.dataset.diametro || '-';
        detalleProveedor.textContent = option.dataset.proveedor || '-';
        detalleCertificado.textContent = option.dataset.certificado || '-';
        detalleLongitud.textContent = formatDecimal(longitud);

        if (!stockInicialInput.dataset.manualEdited) {
            stockInicialInput.value = parseNumber(option.dataset.unidades);
        }

        recalculate();
    }

    function recalculate() {
        const stockInicial = parseNumber(stockInicialInput.value);
        const devolucionesProveedor = parseNumber(devolucionesProveedorInput.value);
        const ajusteStock = parseNumber(ajusteStockInput.value);
        const longitud = parseNumber(detalleLongitud.textContent);
        const totalSalidas = stockInicial - devolucionesProveedor + ajusteStock;
        const totalMtsAjustados = totalSalidas * longitud;

        totalSalidasInput.value = formatInt(totalSalidas);
        totalMtsAjustadosInput.value = formatDecimal(totalMtsAjustados);
    }

    if (ingresoSelect) {
        ingresoSelect.addEventListener('change', updateIngresoDetails);
    }

    if (stockInicialInput) {
        stockInicialInput.addEventListener('input', function () {
            stockInicialInput.dataset.manualEdited = '1';
            recalculate();
        });
    }

    [devolucionesProveedorInput, ajusteStockInput].forEach((input) => {
        if (input) {
            input.addEventListener('input', recalculate);
        }
    });

    updateIngresoDetails();
});
</script>
@endpush
