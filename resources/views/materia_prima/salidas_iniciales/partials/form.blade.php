@php
    $selectedIngreso = old('Id_Ingreso_MP', $salidaInicial->Id_Ingreso_MP ?? $selectedIngresoId ?? null);
    $selectedIngresoModel = $ingresos->firstWhere('Id_MP', (int) $selectedIngreso);
    $preparadas = old('Cantidad_Unidades_MP_Preparadas', $salidaInicial->Cantidad_Unidades_MP_Preparadas ?? 0);
    $adicionales = old('Cantidad_MP_Adicionales', $salidaInicial->Cantidad_MP_Adicionales ?? 0);
    $devoluciones = old('Devoluciones_Unidades_MP', $salidaInicial->Devoluciones_Unidades_MP ?? 0);
    $estadoRegistro = old('reg_Status', isset($salidaInicial) ? (int) $salidaInicial->reg_Status : 1);
@endphp

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
            <div class="col-md-4"><div class="detail-item"><span class="detail-label">Metros del ingreso</span><div class="detail-value" id="detalle_metros">{{ $selectedIngresoModel ? number_format((float) $selectedIngresoModel->Mts_Totales, 2, ',', '.') : '-' }}</div></div></div>
        </div>

        <div class="alert alert-light formula-ajuste-alert">
            <strong>Formula del ajuste:</strong>
            <span>Total salidas = preparadas + adicionales - devoluciones.</span>
            <span>Stock ajustado = unidades ingresadas - total salidas.</span>
            <span>Total utilizado = total salidas x longitud por unidad.</span>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Cantidad_Unidades_MP">Unidades ingresadas</label>
                    <input type="number" id="Cantidad_Unidades_MP" class="form-control text-center" value="{{ $selectedIngresoModel->Unidades_MP ?? $salidaInicial->Cantidad_Unidades_MP ?? 0 }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Longitud_Unidad_MP">Longitud por unidad</label>
                    <input type="text" id="Longitud_Unidad_MP" class="form-control text-center" value="{{ $selectedIngresoModel ? number_format((float) $selectedIngresoModel->Longitud_Unidad_MP, 2, ',', '.') : (isset($salidaInicial) ? number_format((float) $salidaInicial->Longitud_Unidad_MP, 2, ',', '.') : '0,00') }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Cantidad_Unidades_MP_Preparadas">Unidades preparadas</label>
                    <input type="number" name="Cantidad_Unidades_MP_Preparadas" id="Cantidad_Unidades_MP_Preparadas" class="form-control text-center" min="0" step="1" value="{{ $preparadas }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Cantidad_MP_Adicionales">Unidades adicionales</label>
                    <input type="number" name="Cantidad_MP_Adicionales" id="Cantidad_MP_Adicionales" class="form-control text-center" min="0" step="1" value="{{ $adicionales }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Devoluciones_Unidades_MP">Devoluciones</label>
                    <input type="number" name="Devoluciones_Unidades_MP" id="Devoluciones_Unidades_MP" class="form-control text-center" min="0" step="1" value="{{ $devoluciones }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Total_Salidas_MP">Total salidas ajustadas</label>
                    <input type="text" id="Total_Salidas_MP" class="form-control text-center" readonly>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Total_Unidades">Stock ajustado de unidades</label>
                    <input type="text" id="Total_Unidades" class="form-control text-center" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Total_mm_Utilizados">Total utilizado</label>
                    <input type="text" id="Total_mm_Utilizados" class="form-control text-center" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="show-card-footer">
        <div class="show-actions">
            <a href="{{ route('mp_salidas_iniciales.index') }}" class="btn btn-secondary">Volver</a>
            <button type="submit" class="btn btn-primary">{{ isset($salidaInicial) ? 'Actualizar' : 'Guardar' }}</button>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ingresoSelect = document.getElementById('Id_Ingreso_MP');
    const unidadesInput = document.getElementById('Cantidad_Unidades_MP');
    const longitudInput = document.getElementById('Longitud_Unidad_MP');
    const preparadasInput = document.getElementById('Cantidad_Unidades_MP_Preparadas');
    const adicionalesInput = document.getElementById('Cantidad_MP_Adicionales');
    const devolucionesInput = document.getElementById('Devoluciones_Unidades_MP');
    const totalSalidasInput = document.getElementById('Total_Salidas_MP');
    const totalUnidadesInput = document.getElementById('Total_Unidades');
    const totalUtilizadoInput = document.getElementById('Total_mm_Utilizados');
    const detalleNroIngreso = document.getElementById('detalle_nro_ingreso');
    const detalleCodigo = document.getElementById('detalle_codigo');
    const detalleMateria = document.getElementById('detalle_materia');
    const detalleDiametro = document.getElementById('detalle_diametro');
    const detalleProveedor = document.getElementById('detalle_proveedor');
    const detalleCertificado = document.getElementById('detalle_certificado');
    const detalleMetros = document.getElementById('detalle_metros');

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
            detalleMetros.textContent = '-';
            unidadesInput.value = 0;
            longitudInput.value = '0,00';
            recalculate();
            return;
        }

        const unidades = parseNumber(option.dataset.unidades);
        const longitud = parseNumber(option.dataset.longitud);
        const metros = parseNumber(option.dataset.metros);

        detalleNroIngreso.textContent = option.dataset.nroIngreso || '-';
        detalleCodigo.textContent = option.dataset.codigo || '-';
        detalleMateria.textContent = option.dataset.materia || '-';
        detalleDiametro.textContent = option.dataset.diametro || '-';
        detalleProveedor.textContent = option.dataset.proveedor || '-';
        detalleCertificado.textContent = option.dataset.certificado || '-';
        detalleMetros.textContent = formatDecimal(metros);

        unidadesInput.value = unidades;
        longitudInput.value = formatDecimal(longitud);
        recalculate();
    }

    function recalculate() {
        const unidades = parseNumber(unidadesInput.value);
        const longitud = parseNumber(longitudInput.value);
        const preparadas = parseNumber(preparadasInput.value);
        const adicionales = parseNumber(adicionalesInput.value);
        const devoluciones = parseNumber(devolucionesInput.value);

        const totalSalidas = preparadas + adicionales - devoluciones;
        const totalUnidades = unidades - totalSalidas;
        const totalUtilizado = totalSalidas * longitud;

        totalSalidasInput.value = formatInt(totalSalidas);
        totalUnidadesInput.value = formatInt(totalUnidades);
        totalUtilizadoInput.value = formatDecimal(totalUtilizado);
    }

    if (ingresoSelect) {
        ingresoSelect.addEventListener('change', updateIngresoDetails);
    }

    [preparadasInput, adicionalesInput, devolucionesInput].forEach((input) => {
        if (input) input.addEventListener('input', recalculate);
    });

    updateIngresoDetails();
});
</script>
@endpush
