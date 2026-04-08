@extends('adminlte::page')

@section('title', 'Registrar Entrega de Producto')

@section('content_header')
    <h1 class="text-center">Registrar Entrega de Producto</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/entregas_productos_create.css') }}">
@stop

@section('content')
    @include('components.swal-session')

    <form method="POST"
          action="{{ route('entregas_productos.store') }}"
          id="form-entrega-producto"
          class="entrega-create-form"
          data-ajax="true"
          data-redirect-url="{{ route('entregas_productos.index') }}">
        @csrf

        <div class="entrega-create-alert">
            Esta carga registra una entrega completa con una misma fecha y un mismo numero de remito.
            Cada fila genera un parcial de calidad unico por OF, correlativo y no repetible.
        </div>

        <div class="entrega-create-head">
            <div class="entrega-head-card">
                <span class="entrega-head-label">Remito Sugerido</span>
                <div class="entrega-head-value" id="remito-sugerido-label">{{ $proximoRemito }}</div>
                <span class="entrega-head-hint">Ultimo remito cargado: {{ $ultimoRemito }}</span>
                <input type="hidden"
                       name="Nro_Remito_Entrega_Calidad"
                       id="Nro_Remito_Entrega_Calidad"
                       value="{{ old('Nro_Remito_Entrega_Calidad', $proximoRemito) }}">
            </div>

            <div class="entrega-head-card">
                <span class="entrega-head-label">Responsable de liberacion</span>
                <div class="entrega-head-value entrega-head-user">{{ auth()->user()?->name ?? '-' }}</div>
                <span class="entrega-head-hint">Usuario logueado que libera esta entrega</span>
            </div>

            <div class="entrega-head-card">
                <div class="entrega-head-input">
                    <label for="Fecha_Entrega_Calidad">Fecha de entrega</label>
                    <input type="date"
                           name="Fecha_Entrega_Calidad"
                           id="Fecha_Entrega_Calidad"
                           class="form-control"
                           value="{{ old('Fecha_Entrega_Calidad', $fechaEntregaSugerida) }}"
                           required>
                </div>
            </div>
        </div>

        <div class="entrega-grid-wrap">
            <table class="table table-bordered custom-font centered-form" id="tablaListadoOF">
                <thead>
                    <tr>
                        <th class="entrega-col-fila">N° Fila</th>
                        <th class="entrega-col-of">N° OF</th>
                        <th class="entrega-col-parcial">N° Parcial Calidad</th>
                        <th class="entrega-col-cantidad">Cantidad</th>
                        <th class="entrega-col-inspector">Controló</th>
                        <th class="entrega-col-codigo">Codigo Producto</th>
                        <th class="entrega-col-descripcion">Descripcion</th>
                        <th class="entrega-col-ingreso">Nro Ingreso MP</th>
                        <th class="entrega-col-codigomp">Codigo MP</th>
                        <th class="entrega-col-cert">Certificado MP</th>
                        <th class="entrega-col-maquina">Nro Maquina</th>
                        <th class="entrega-col-familia">Familia Maquina</th>
                        <th class="entrega-col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="entrega-toolbar">
            <button type="button" class="btn btn-success" id="agregarFila">Agregar Fila</button>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="{{ route('entregas_productos.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </form>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
<script>
(function () {
    const endpointTemplate = @json(route('entregas_productos.ofData', ['nroOf' => '__OF__']));
    const inspectores = @json(($inspectoresCalidad ?? collect())->map(fn ($item) => ['id' => $item->id, 'nombre' => $item->nombre])->values());
    const tableBody = document.querySelector('#tablaListadoOF tbody');
    const addRowButton = document.getElementById('agregarFila');
    let rowCounter = 0;

    function inspectorOptions(selected = '') {
        const options = ['<option value="">Seleccione</option>'];
        inspectores.forEach(function (inspector) {
            const isSelected = String(inspector.id) === String(selected) ? 'selected' : '';
            options.push(`<option value="${inspector.id}" ${isSelected}>${inspector.nombre}</option>`);
        });
        return options.join('');
    }

    function createRow() {
        rowCounter++;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="entrega-row-number"></td>
            <td><input type="number" min="1" step="1" class="form-control js-of"></td>
            <td><input type="text" class="form-control partial-output js-parcial" readonly tabindex="-1"></td>
            <td><input type="number" min="1" step="1" class="form-control js-cantidad"></td>
            <td>
                <select class="form-control js-inspector">
                    ${inspectorOptions()}
                </select>
            </td>
            <td><input type="text" class="form-control readonly-ref js-prod-codigo" readonly tabindex="-1"></td>
            <td><input type="text" class="form-control readonly-ref js-prod-descripcion" readonly tabindex="-1"></td>
            <td><input type="text" class="form-control readonly-ref js-nro-ingreso" readonly tabindex="-1"></td>
            <td><input type="text" class="form-control readonly-ref js-codigo-mp" readonly tabindex="-1"></td>
            <td><input type="text" class="form-control readonly-ref js-certificado" readonly tabindex="-1"></td>
            <td><input type="text" class="form-control readonly-ref js-maquina" readonly tabindex="-1"></td>
            <td><input type="text" class="form-control readonly-ref js-familia" readonly tabindex="-1"></td>
            <td><button type="button" class="btn btn-danger entrega-remove-btn js-remove-row">Eliminar</button></td>
        `;
        tableBody.appendChild(tr);
        reindexRows();
        updateRowState(tr);
        return tr;
    }

    function reindexRows() {
        Array.from(tableBody.querySelectorAll('tr')).forEach(function (tr, index) {
            const rowIndex = index + 1;
            tr.dataset.rowIndex = String(index);
            tr.querySelector('.entrega-row-number').textContent = rowIndex;

            tr.querySelector('.js-of').setAttribute('name', `rows[${index}][Id_OF]`);
            tr.querySelector('.js-cantidad').setAttribute('name', `rows[${index}][Cant_Piezas_Entregadas]`);
            tr.querySelector('.js-inspector').setAttribute('name', `rows[${index}][Id_Inspector_Calidad]`);
            tr.querySelector('.js-parcial').setAttribute('name', `rows[${index}][Nro_Parcial_Calidad]`);
        });
    }

    function formatOf(ofValue) {
        return String(ofValue).padStart(6, '0');
    }

    function clearReferenceFields(tr) {
        ['.js-prod-codigo', '.js-prod-descripcion', '.js-nro-ingreso', '.js-codigo-mp', '.js-certificado', '.js-maquina', '.js-familia', '.js-parcial']
            .forEach(function (selector) {
                const field = tr.querySelector(selector);
                if (field) field.value = '';
            });
        delete tr.dataset.nextPartialBase;
        updateRowState(tr);
    }

    function fillReferenceFields(tr, data) {
        tr.querySelector('.js-prod-codigo').value = data.Prod_Codigo || '';
        tr.querySelector('.js-prod-descripcion').value = data.Prod_Descripcion || '';
        tr.querySelector('.js-nro-ingreso').value = data.Nro_Ingreso_MP || '';
        tr.querySelector('.js-codigo-mp').value = data.Codigo_MP || '';
        tr.querySelector('.js-certificado').value = data.Nro_Certificado_MP || '';
        tr.querySelector('.js-maquina').value = data.Nro_Maquina || '';
        tr.querySelector('.js-familia').value = data.Familia_Maquinas || '';
        tr.dataset.nextPartialBase = data.Next_Parcial_Numero || '';
        recalculatePartials();
        updateRowState(tr);
    }

    function recalculatePartials() {
        const partialTracker = {};

        Array.from(tableBody.querySelectorAll('tr')).forEach(function (tr) {
            const ofValue = (tr.querySelector('.js-of').value || '').trim();
            const partialInput = tr.querySelector('.js-parcial');
            const base = Number(tr.dataset.nextPartialBase || 0);

            if (!ofValue || !base) {
                partialInput.value = '';
                return;
            }

            if (typeof partialTracker[ofValue] === 'undefined') {
                partialTracker[ofValue] = base;
            }

            partialInput.value = `${formatOf(ofValue)}/${partialTracker[ofValue]}`;
            partialTracker[ofValue] += 1;
        });
    }

    function rowIsComplete(tr) {
        return Boolean(
            tr.querySelector('.js-of').value &&
            tr.querySelector('.js-cantidad').value &&
            tr.querySelector('.js-inspector').value &&
            tr.querySelector('.js-parcial').value
        );
    }

    function updateRowState(tr) {
        tr.classList.remove('row-complete', 'row-incomplete', 'row-invalid');

        const ofValue = (tr.querySelector('.js-of').value || '').trim();
        const cantidad = (tr.querySelector('.js-cantidad').value || '').trim();
        const inspector = (tr.querySelector('.js-inspector').value || '').trim();

        if (!ofValue && !cantidad && !inspector) {
            tr.classList.add('row-incomplete');
            return;
        }

        if (rowIsComplete(tr)) {
            tr.classList.add('row-complete');
        } else {
            tr.classList.add('row-incomplete');
        }
    }

    function fetchOfData(tr) {
        const nroOf = (tr.querySelector('.js-of').value || '').trim();
        if (!nroOf) {
            clearReferenceFields(tr);
            recalculatePartials();
            return;
        }

        $.get(endpointTemplate.replace('__OF__', encodeURIComponent(nroOf)))
            .done(function (response) {
                if (!response.success || !response.data) {
                    clearReferenceFields(tr);
                    SwalUtils.error('No se encontraron datos para la OF ingresada.');
                    return;
                }

                fillReferenceFields(tr, response.data);
            })
            .fail(function () {
                clearReferenceFields(tr);
                SwalUtils.error('No se pudo cargar la OF seleccionada.');
            });
    }

    function bindRowEvents(tr) {
        tr.querySelector('.js-of').addEventListener('change', function () {
            fetchOfData(tr);
        });
        tr.querySelector('.js-of').addEventListener('blur', function () {
            fetchOfData(tr);
        });
        tr.querySelector('.js-cantidad').addEventListener('input', function () {
            updateRowState(tr);
        });
        tr.querySelector('.js-inspector').addEventListener('change', function () {
            updateRowState(tr);
        });
        tr.querySelector('.js-remove-row').addEventListener('click', function () {
            tr.remove();
            reindexRows();
            recalculatePartials();
            if (!tableBody.querySelector('tr')) {
                const first = createRow();
                bindRowEvents(first);
            }
        });
    }

    function addNewRow() {
        const tr = createRow();
        bindRowEvents(tr);
        return tr;
    }

    $(document).ready(function () {
        Swal.fire({
            icon: 'info',
            title: 'Carga de entregas desde Calidad',
            html: 'Cada fila genera un parcial unico por OF.<br>El remito y la fecha aplican a toda la entrega.',
            confirmButtonText: 'OK'
        });

        addNewRow();

        addRowButton.addEventListener('click', function () {
            addNewRow();
        });
    });
})();
</script>
@stop
