@php
    $selectedPedidoMpId = old('Id_Pedido_MP', $selectedPedidoMpId ?? optional($egreso->pedidoMp ?? null)->Id_Pedido_MP);
    $selectedPedidoMp = collect($pedidosMp)->firstWhere('Id_Pedido_MP', (int) $selectedPedidoMpId);
@endphp

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ isset($egreso) ? 'Editar salida de materia prima' : 'Registrar salida de materia prima' }}</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <strong>Planificacion y calidad:</strong>
            los datos de <em>fecha de planificacion</em>, <em>responsable de planificacion</em> y <em>pedido de material Nro</em>
            se autocompletan desde la definicion de pedidos MP. Calidad solo necesita confirmar la entrega.
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="Id_Pedido_MP">OF / Pedido MP</label>
                    <select name="Id_Pedido_MP" id="Id_Pedido_MP" class="form-control" required>
                        <option value="">Seleccionar OF</option>
                        @foreach($pedidosMp as $pedidoMp)
                            <option value="{{ $pedidoMp->Id_Pedido_MP }}"
                                data-of="{{ $pedidoMp->pedido->Nro_OF ?? $pedidoMp->Id_OF }}"
                                data-producto="{{ $pedidoMp->pedido->producto->Prod_Codigo ?? '-' }}"
                                data-ingreso="{{ $pedidoMp->Nro_Ingreso_MP ?? '-' }}"
                                data-codigo="{{ $pedidoMp->Codigo_MP ?? '-' }}"
                                data-maquina="{{ $pedidoMp->Nro_Maquina ?? '-' }}"
                                data-barras="{{ (int) ($pedidoMp->Cant_Barras_MP ?? 0) }}"
                                data-longitud="{{ (float) ($pedidoMp->Longitud_Un_MP ?? 0) }}"
                                data-fecha-planificacion="{{ $pedidoMp->Fecha_Planificacion ? $pedidoMp->Fecha_Planificacion->format('Y-m-d') : '' }}"
                                data-resp-planificacion="{{ $pedidoMp->Responsable_Planificacion ?? '' }}"
                                data-pedido-material="{{ $pedidoMp->Pedido_Material_Nro ?? '' }}"
                                {{ (int) $selectedPedidoMpId === (int) $pedidoMp->Id_Pedido_MP ? 'selected' : '' }}>
                                OF #{{ $pedidoMp->pedido->Nro_OF ?? $pedidoMp->Id_OF }} - {{ $pedidoMp->pedido->producto->Prod_Codigo ?? 'Sin producto' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="reg_Status">Estado del Registro</label>
                    <select name="reg_Status" id="reg_Status" class="form-control" required>
                        <option value="1" {{ (string) old('reg_Status', isset($egreso) ? (int) $egreso->reg_Status : 1) === '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ (string) old('reg_Status', isset($egreso) ? (int) $egreso->reg_Status : 1) === '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Nro OF</label>
                    <input type="text" id="meta_nro_of" class="form-control" value="{{ old('meta_nro_of', $selectedPedidoMp->pedido->Nro_OF ?? optional(optional($egreso->pedidoMp)->pedido)->Nro_OF ?? '') }}" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Producto</label>
                    <input type="text" id="meta_producto" class="form-control" value="{{ old('meta_producto', $selectedPedidoMp->pedido->producto->Prod_Codigo ?? optional(optional(optional($egreso->pedidoMp)->pedido)->producto)->Prod_Codigo ?? '') }}" readonly>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Ingreso MP</label>
                    <input type="text" id="meta_ingreso" class="form-control" value="{{ old('meta_ingreso', $selectedPedidoMp->Nro_Ingreso_MP ?? optional($egreso->pedidoMp)->Nro_Ingreso_MP ?? '') }}" readonly>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Maquina</label>
                    <input type="text" id="meta_maquina" class="form-control" value="{{ old('meta_maquina', $selectedPedidoMp->Nro_Maquina ?? optional($egreso->pedidoMp)->Nro_Maquina ?? '') }}" readonly>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Largo x unidad</label>
                    <input type="text" id="meta_longitud" class="form-control" value="{{ number_format((float) old('meta_longitud', $selectedPedidoMp->Longitud_Un_MP ?? optional($egreso->pedidoMp)->Longitud_Un_MP ?? 0), 2, ',', '.') }}" readonly>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Codigo_MP">Codigo MP</label>
                    <input type="text" id="Codigo_MP" class="form-control" value="{{ old('Codigo_MP', $selectedPedidoMp->Codigo_MP ?? optional($egreso->pedidoMp)->Codigo_MP ?? '') }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Cantidad_Unidades_MP">Barras solicitadas</label>
                    <input type="number" name="Cantidad_Unidades_MP" id="Cantidad_Unidades_MP" class="form-control" min="0" step="1" value="{{ old('Cantidad_Unidades_MP', $egreso->Cantidad_Unidades_MP ?? ($selectedPedidoMp->Cant_Barras_MP ?? 0)) }}" required readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Cantidad_Unidades_MP_Preparadas">Barras preparadas</label>
                    <input type="number" name="Cantidad_Unidades_MP_Preparadas" id="Cantidad_Unidades_MP_Preparadas" class="form-control" min="0" step="1" value="{{ old('Cantidad_Unidades_MP_Preparadas', $egreso->Cantidad_Unidades_MP_Preparadas ?? 0) }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="Cantidad_MP_Adicionales">Adicionales</label>
                    <input type="number" name="Cantidad_MP_Adicionales" id="Cantidad_MP_Adicionales" class="form-control" min="0" step="1" value="{{ old('Cantidad_MP_Adicionales', $egreso->Cantidad_MP_Adicionales ?? 0) }}" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="Cant_Devoluciones">Devoluciones</label>
                    <input type="number" name="Cant_Devoluciones" id="Cant_Devoluciones" class="form-control" min="0" step="1" value="{{ old('Cant_Devoluciones', $egreso->Cant_Devoluciones ?? 0) }}" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="Total_Salidas_MP">Total salidas</label>
                    <input type="text" id="Total_Salidas_MP" class="form-control" value="{{ number_format((float) old('Total_Salidas_MP', $egreso->Total_Salidas_MP ?? 0), 0, ',', '.') }}" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="Total_Mtros_Utilizados">Total metros utilizados</label>
                    <input type="text" id="Total_Mtros_Utilizados" class="form-control" value="{{ number_format((float) old('Total_Mtros_Utilizados', $egreso->Total_Mtros_Utilizados ?? 0), 2, ',', '.') }}" readonly>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <h5 class="mb-3">Planificacion</h5>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Fecha_del_Pedido_Produccion">Fecha de planificacion</label>
                    <input type="date" name="Fecha_del_Pedido_Produccion" id="Fecha_del_Pedido_Produccion" class="form-control" value="{{ old('Fecha_del_Pedido_Produccion', $selectedPedidoMp->Fecha_Planificacion?->format('Y-m-d') ?? (isset($egreso) && $egreso->Fecha_del_Pedido_Produccion ? $egreso->Fecha_del_Pedido_Produccion->format('Y-m-d') : '')) }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Responsable_Pedido_Produccion">Resp. de planificacion</label>
                    <input type="text" name="Responsable_Pedido_Produccion" id="Responsable_Pedido_Produccion" class="form-control" value="{{ old('Responsable_Pedido_Produccion', $selectedPedidoMp->Responsable_Planificacion ?? $egreso->Responsable_Pedido_Produccion ?? '') }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Nro_Pedido_MP">Pedido de Material Nro</label>
                    <input type="number" name="Nro_Pedido_MP" id="Nro_Pedido_MP" class="form-control" min="0" step="1" value="{{ old('Nro_Pedido_MP', $selectedPedidoMp->Pedido_Material_Nro ?? $egreso->Nro_Pedido_MP ?? '') }}" readonly>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <h5 class="mb-3">Calidad</h5>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Fecha_de_Entrega_Pedido_Calidad">Fecha de entrega</label>
                    <input type="date" name="Fecha_de_Entrega_Pedido_Calidad" id="Fecha_de_Entrega_Pedido_Calidad" class="form-control" value="{{ old('Fecha_de_Entrega_Pedido_Calidad', isset($egreso) && $egreso->Fecha_de_Entrega_Pedido_Calidad ? $egreso->Fecha_de_Entrega_Pedido_Calidad->format('Y-m-d') : '') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Alerta_Calidad">Alerta</label>
                    <input type="text" id="Alerta_Calidad" class="form-control" value="{{ old('Alerta_Calidad', isset($egreso) && $egreso->Fecha_de_Entrega_Pedido_Calidad ? $egreso->Fecha_de_Entrega_Pedido_Calidad->format('d/m/Y') : '') }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Responsable_de_entrega_Calidad">Resp. de entrega</label>
                    <input type="text" name="Responsable_de_entrega_Calidad" id="Responsable_de_entrega_Calidad" class="form-control" value="{{ old('Responsable_de_entrega_Calidad', $egreso->Responsable_de_entrega_Calidad ?? auth()->user()->name ?? '') }}">
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-right">
        <a href="{{ route('mp_egresos.index') }}" class="btn btn-secondary">Volver</a>
        <button type="submit" class="btn btn-primary">{{ isset($egreso) ? 'Actualizar' : 'Guardar' }}</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pedidoSelect = document.getElementById('Id_Pedido_MP');
    const cantidadSolicitada = document.getElementById('Cantidad_Unidades_MP');
    const cantidadPreparada = document.getElementById('Cantidad_Unidades_MP_Preparadas');
    const adicionales = document.getElementById('Cantidad_MP_Adicionales');
    const devoluciones = document.getElementById('Cant_Devoluciones');
    const fechaEntregaCalidad = document.getElementById('Fecha_de_Entrega_Pedido_Calidad');
    const alertaCalidad = document.getElementById('Alerta_Calidad');
    const totalSalidas = document.getElementById('Total_Salidas_MP');
    const totalMetros = document.getElementById('Total_Mtros_Utilizados');
    const metaOf = document.getElementById('meta_nro_of');
    const metaProducto = document.getElementById('meta_producto');
    const metaIngreso = document.getElementById('meta_ingreso');
    const metaMaquina = document.getElementById('meta_maquina');
    const metaLongitud = document.getElementById('meta_longitud');
    const codigoMp = document.getElementById('Codigo_MP');

    function parseNumber(value) {
        const normalized = String(value ?? '').replace(',', '.');
        const number = parseFloat(normalized);
        return Number.isNaN(number) ? 0 : number;
    }

    function formatDecimal(value, decimals) {
        return new Intl.NumberFormat('es-AR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(value);
    }

    function formatDateForAlert(value) {
        if (!value) return '';
        const parts = value.split('-');
        if (parts.length !== 3) return value;
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function updateMeta() {
        const selected = pedidoSelect.options[pedidoSelect.selectedIndex];
        if (!selected || !selected.value) {
            metaOf.value = '';
            metaProducto.value = '';
            metaIngreso.value = '';
            metaMaquina.value = '';
            metaLongitud.value = formatDecimal(0, 2);
            codigoMp.value = '';
            document.getElementById('Fecha_del_Pedido_Produccion').value = '';
            document.getElementById('Responsable_Pedido_Produccion').value = '';
            document.getElementById('Nro_Pedido_MP').value = '';
            return 0;
        }

        metaOf.value = selected.dataset.of || '';
        metaProducto.value = selected.dataset.producto || '';
        metaIngreso.value = selected.dataset.ingreso || '';
        metaMaquina.value = selected.dataset.maquina || '';
        codigoMp.value = selected.dataset.codigo || '';
        const longitud = parseNumber(selected.dataset.longitud || 0);
        metaLongitud.value = formatDecimal(longitud, 2);
        document.getElementById('Fecha_del_Pedido_Produccion').value = selected.dataset.fechaPlanificacion || '';
        document.getElementById('Responsable_Pedido_Produccion').value = selected.dataset.respPlanificacion || '';
        document.getElementById('Nro_Pedido_MP').value = selected.dataset.pedidoMaterial || '';
        return longitud;
    }

    function recalculate() {
        const longitud = updateMeta();
        const preparadas = parseNumber(cantidadPreparada.value);
        const extra = parseNumber(adicionales.value);
        const devol = parseNumber(devoluciones.value);
        const salidas = Math.max(preparadas + extra - devol, 0);
        const metros = salidas * longitud;
        totalSalidas.value = formatDecimal(salidas, 0);
        totalMetros.value = formatDecimal(metros, 2);
        alertaCalidad.value = formatDateForAlert(fechaEntregaCalidad.value);
    }

    pedidoSelect.addEventListener('change', function () {
        const selected = pedidoSelect.options[pedidoSelect.selectedIndex];
        if (selected && selected.value) {
            cantidadSolicitada.value = selected.dataset.barras || 0;
        }
        recalculate();
    });

    [cantidadPreparada, adicionales, devoluciones, fechaEntregaCalidad].forEach(function (field) {
        field.addEventListener('input', recalculate);
        field.addEventListener('change', recalculate);
    });

    recalculate();
});
</script>
