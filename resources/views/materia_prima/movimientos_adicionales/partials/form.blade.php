<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="Fecha_Movimiento">Fecha</label>
            <input type="date" name="Fecha_Movimiento" id="Fecha_Movimiento" class="form-control" value="{{ old('Fecha_Movimiento', isset($movimiento) && $movimiento->Fecha_Movimiento ? optional($movimiento->Fecha_Movimiento)->format('Y-m-d') : date('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="reg_Status">Estado del Registro</label>
            <select name="reg_Status" id="reg_Status" class="form-control filtro-select" required>
                <option value="1" {{ (string) old('reg_Status', $movimiento->reg_Status ?? 1) === '1' ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ (string) old('reg_Status', $movimiento->reg_Status ?? 1) === '0' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="Nro_Ingreso_MP">Nro Ingreso MP</label>
            <select name="Nro_Ingreso_MP" id="Nro_Ingreso_MP" class="form-control filtro-select" required>
                <option value="">Seleccionar ingreso</option>
                @foreach($ingresos as $ingreso)
                    <option value="{{ $ingreso->Nro_Ingreso_MP }}"
                        data-codigo="{{ $ingreso->Codigo_MP }}"
                        data-certificado="{{ $ingreso->Nro_Certificado_MP }}"
                        data-longitud="{{ $ingreso->Longitud_Unidad_MP }}"
                        {{ (string) old('Nro_Ingreso_MP', $movimiento->Nro_Ingreso_MP ?? '') === (string) $ingreso->Nro_Ingreso_MP ? 'selected' : '' }}>
                        Ingreso #{{ $ingreso->Nro_Ingreso_MP }} - {{ $ingreso->Codigo_MP }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="Nro_OF">Nro OF</label>
            <input type="number" name="Nro_OF" id="Nro_OF" class="form-control" value="{{ old('Nro_OF', $movimiento->Nro_OF ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="Codigo_Producto">Codigo de Producto</label>
            <input type="text" name="Codigo_Producto" id="Codigo_Producto" class="form-control" value="{{ old('Codigo_Producto', $movimiento->Codigo_Producto ?? '') }}">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="Nro_Maquina">Nro de Maquina</label>
            <input type="text" name="Nro_Maquina" id="Nro_Maquina" class="form-control" value="{{ old('Nro_Maquina', $movimiento->Nro_Maquina ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="Cantidad_Adicionales">Adicionales</label>
            <input type="number" min="0" name="Cantidad_Adicionales" id="Cantidad_Adicionales" class="form-control" value="{{ old('Cantidad_Adicionales', $movimiento->Cantidad_Adicionales ?? 0) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="Cantidad_Devoluciones">Devoluciones</label>
            <input type="number" min="0" name="Cantidad_Devoluciones" id="Cantidad_Devoluciones" class="form-control" value="{{ old('Cantidad_Devoluciones', $movimiento->Cantidad_Devoluciones ?? 0) }}">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="Longitud_Unidad_Mts">Longitud (Mts)</label>
            <input type="number" min="0" step="0.01" name="Longitud_Unidad_Mts" id="Longitud_Unidad_Mts" class="form-control" value="{{ old('Longitud_Unidad_Mts', $movimiento->Longitud_Unidad_Mts ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="Total_Mtros_Movimiento_preview">Metros netos del movimiento</label>
            <input type="text" id="Total_Mtros_Movimiento_preview" class="form-control" value="{{ isset($movimiento) ? number_format((float) $movimiento->Total_Mtros_Movimiento, 2, ',', '.') : '0,00' }}" readonly>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="Autorizado_por">Autorizado por</label>
            <select name="Autorizado_por" id="Autorizado_por" class="form-control filtro-select">
                <option value="">Sin definir</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (string) old('Autorizado_por', $movimiento->Autorizado_por ?? '') === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="alert alert-info mb-3">
    <strong>Formula:</strong> metros netos = (adicionales - devoluciones) x longitud. Este modulo se usa para movimientos extra historicos y futuros, fuera de la salida inicial y de la entrega operativa de calidad.
</div>

<div class="form-group">
    <label for="Observaciones">Observaciones</label>
    <textarea name="Observaciones" id="Observaciones" rows="4" class="form-control">{{ old('Observaciones', $movimiento->Observaciones ?? '') }}</textarea>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ingresoSelect = document.getElementById('Nro_Ingreso_MP');
        const longitudInput = document.getElementById('Longitud_Unidad_Mts');
        const adicionalesInput = document.getElementById('Cantidad_Adicionales');
        const devolucionesInput = document.getElementById('Cantidad_Devoluciones');
        const totalPreview = document.getElementById('Total_Mtros_Movimiento_preview');

        function recalculateTotal() {
            const longitud = parseFloat((longitudInput.value || '0').replace(',', '.')) || 0;
            const adicionales = parseInt(adicionalesInput.value || '0', 10) || 0;
            const devoluciones = parseInt(devolucionesInput.value || '0', 10) || 0;
            const total = (adicionales - devoluciones) * longitud;
            totalPreview.value = total.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function hydrateIngreso() {
            const option = ingresoSelect.options[ingresoSelect.selectedIndex];
            if (!option || !option.value) return;
            if (!longitudInput.value && option.dataset.longitud) {
                longitudInput.value = option.dataset.longitud;
            }
            recalculateTotal();
        }

        ingresoSelect?.addEventListener('change', hydrateIngreso);
        longitudInput?.addEventListener('input', recalculateTotal);
        adicionalesInput?.addEventListener('input', recalculateTotal);
        devolucionesInput?.addEventListener('input', recalculateTotal);
        hydrateIngreso();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-mov-adicional') || document.querySelector('form');

        form?.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' || event.target.tagName === 'TEXTAREA') {
                return;
            }

            event.preventDefault();
        });
    });
</script>
