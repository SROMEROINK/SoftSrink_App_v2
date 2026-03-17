@extends('adminlte::page')
{{-- resources/views/materia_prima/ingresos/create.blade.php --}}

@section('title', 'Registrar Ingreso de Materia Prima')

@section('content_header')
    <h1>Registrar Ingreso de Materia Prima</h1>
@stop

@section('content')

@if(!empty($ultimoIngreso))
<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title">Último ingreso registrado</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><b>Nro_Ingreso_MP:</b> {{ $ultimoIngreso->Nro_Ingreso_MP }}</div>
            <div class="col-md-3"><b>Fecha:</b> {{ $ultimoIngreso->Fecha_Ingreso ?? '—' }}</div>
            <div class="col-md-3"><b>Proveedor:</b> {{ optional($ultimoIngreso->proveedor)->Prov_Nombre ?? '—' }}</div>
            <div class="col-md-3"><b>Código MP:</b> {{ $ultimoIngreso->Codigo_MP ?? '—' }}</div>
        </div>
    </div>
</div>
@endif

<form method="POST"
      action="{{ route('mp_ingresos.store') }}"
      id="form-ingreso"
      data-ajax="true"
      data-redirect-url="{{ route('mp_ingresos.index') }}">

    @csrf

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos generales del ingreso</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="Fecha_Ingreso">Fecha_Ingreso</label>
                        <input type="date" name="Fecha_Ingreso" id="Fecha_Ingreso"
                               class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="Nro_Pedido">Nro_Pedido</label>
                        <input type="text" name="Nro_Pedido" id="Nro_Pedido"
                               class="form-control" maxlength="250" required>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="Nro_Remito">Nro_Remito</label>
                        <input type="text" name="Nro_Remito" id="Nro_Remito"
                               class="form-control" maxlength="255" required>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="Nro_OC">Nro_OC</label>
                        <input type="text" name="Nro_OC" id="Nro_OC"
                               class="form-control" maxlength="255">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="Id_Proveedor">Proveedor</label>
                        <select name="Id_Proveedor" id="Id_Proveedor" class="form-control" required>
                            <option value="">Seleccione…</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->Prov_Id }}">{{ $proveedor->Prov_Nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Detalle del ingreso</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered custom-font centered-form" id="tablaIngresoMP">
                    <thead>
                        <tr>
                            <th>N° Fila</th>
                            <th>Nro_Ingreso_MP</th>
                            <th>Materia Prima</th>
                            <th>Diámetro</th>
                            <th>Código MP</th>
                            <th>Certificado</th>
                            <th>Origen</th>
                            <th>Unidades</th>
                            <th>Longitud</th>
                            <th>Mts Totales</th>
                            <th>Kilos Totales</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- filas dinámicas --}}
                    </tbody>
                </table>
            </div>

            <input type="hidden" name="reg_Status" value="1">

            <div class="btn-der mt-3">
                <button type="button" class="btn btn-success" id="agregarFila">Agregar Fila</button>
                <button type="submit" class="btn btn-primary">Registrar Ingreso</button>
                <a href="{{ route('mp_ingresos.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</form>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_ingreso_create.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>

<script>
$(document).ready(function () {

    let filaCounter = 1;
    let ultimoNroIngreso = {{ (int)($proximoNroIngreso ?? 1) }};

    const materiasPrimas = @json($materiasPrimas);
    const diametros = @json($diametros);

    function escapeHtml(text) {
        return $('<div>').text(text ?? '').html();
    }

    function generarOpcionesMaterias() {
        let html = '<option value="">Seleccione…</option>';
        materiasPrimas.forEach(mp => {
            html += `<option value="${mp.Id_Materia_Prima}" data-nombre="${escapeHtml(mp.Nombre_Materia)}">${escapeHtml(mp.Nombre_Materia)}</option>`;
        });
        return html;
    }

    function generarOpcionesDiametros() {
        let html = '<option value="">Seleccione…</option>';
        diametros.forEach(d => {
            const valor = d.Valor_Diametro ?? d.Diametro ?? '';
            html += `<option value="${d.Id_Diametro}" data-valor="${escapeHtml(valor)}">${escapeHtml(valor)}</option>`;
        });
        return html;
    }

    function generarFila(nroFila, nroIngreso) {
        return `
            <tr>
                <td class="nro-fila">${nroFila}</td>
                <td>
                    <input type="number" name="Nro_Ingreso_MP[]" class="form-control nro-ingreso" value="${nroIngreso}" readonly required>
                </td>
                <td>
                    <select name="Id_Materia_Prima[]" class="form-control materia-prima" required>
                        ${generarOpcionesMaterias()}
                    </select>
                </td>
                <td>
                    <select name="Id_Diametro_MP[]" class="form-control diametro" required>
                        ${generarOpcionesDiametros()}
                    </select>
                </td>
                <td>
                    <input type="text" name="Codigo_MP[]" class="form-control codigo-mp" readonly required>
                </td>
                <td>
                    <input type="text" name="Nro_Certificado_MP[]" class="form-control nro-certificado" maxlength="255">
                </td>
                <td>
                    <input type="text" name="Detalle_Origen_MP[]" class="form-control detalle-origen" maxlength="255">
                </td>
                <td>
                    <input type="number" name="Unidades_MP[]" class="form-control unidades-mp" min="1" step="1" value="0" required>
                </td>
                <td>
                    <input type="number" name="Longitud_Unidad_MP[]" class="form-control longitud-unidad" min="0" step="0.01" value="0" required>
                </td>
                <td>
                    <input type="number" name="Mts_Totales[]" class="form-control mts-totales" step="0.01" readonly required>
                </td>
                <td>
                    <input type="number" name="Kilos_Totales[]" class="form-control kilos-totales" min="0" step="0.01" value="0">
                </td>
                <td>
                    <button type="button" class="btn btn-danger eliminar-fila">Eliminar Fila</button>
                </td>
            </tr>
        `;
    }

    function actualizarNumeracionFilas() {
        $('#tablaIngresoMP tbody tr').each(function(index) {
            $(this).find('.nro-fila').text(index + 1);
        });
    }

    function recalcularCorrelativos() {
        let nro = {{ (int)($proximoNroIngreso ?? 1) }};

        $('#tablaIngresoMP tbody tr').each(function() {
            $(this).find('.nro-ingreso').val(nro);
            nro++;
        });

        ultimoNroIngreso = nro;
    }

    function actualizarCodigoFila($fila) {
        const nombreMp = $fila.find('.materia-prima option:selected').data('nombre');
        const diametro = $fila.find('.diametro option:selected').data('valor');

        if (nombreMp && diametro) {
            $fila.find('.codigo-mp').val(String(nombreMp).trim() + '_' + String(diametro).trim());
        } else {
            $fila.find('.codigo-mp').val('');
        }
    }

    function actualizarMtsFila($fila) {
        const unidades = parseFloat($fila.find('.unidades-mp').val()) || 0;
        const longitud = parseFloat($fila.find('.longitud-unidad').val()) || 0;
        const total = unidades * longitud;
        $fila.find('.mts-totales').val(total.toFixed(2));
    }

    function actualizarOrigenFila($fila) {
        const cert = String($fila.find('.nro-certificado').val() || '').trim().toUpperCase();

        if (cert.startsWith('YT')) {
            $fila.find('.detalle-origen').val('CHINA');
        } else if ($fila.find('.detalle-origen').val() === 'CHINA') {
            $fila.find('.detalle-origen').val('');
        }
    }

    $('#agregarFila').on('click', function () {
        const ultimoVisible = parseInt($('#tablaIngresoMP tbody tr:last').find('.nro-ingreso').val()) || (ultimoNroIngreso - 1);
        const siguiente = ultimoVisible + 1;

        $('#tablaIngresoMP tbody').append(generarFila(filaCounter, siguiente));
        filaCounter++;
        actualizarNumeracionFilas();
    });

    $('#tablaIngresoMP').on('change', '.materia-prima, .diametro', function () {
        const $fila = $(this).closest('tr');
        actualizarCodigoFila($fila);
    });

    $('#tablaIngresoMP').on('input change', '.unidades-mp, .longitud-unidad', function () {
        const $fila = $(this).closest('tr');
        actualizarMtsFila($fila);
    });

    $('#tablaIngresoMP').on('input change blur', '.nro-certificado', function () {
        const $fila = $(this).closest('tr');
        actualizarOrigenFila($fila);
    });

    $('#tablaIngresoMP').on('click', '.eliminar-fila', function () {
        $(this).closest('tr').remove();
        actualizarNumeracionFilas();
        recalcularCorrelativos();

        if ($('#tablaIngresoMP tbody tr').length === 0) {
            Swal.fire({
                title: 'Advertencia',
                text: 'No hay filas cargadas, agregue una fila.',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
        }
    });

    $('#form-ingreso').on('submit', function (e) {
        if ($('#tablaIngresoMP tbody tr').length === 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Advertencia',
                text: 'Debe agregar al menos una fila de detalle.',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
    });

    // fila inicial
    $('#tablaIngresoMP tbody').append(generarFila(filaCounter, ultimoNroIngreso));
    filaCounter++;
});
</script>
@stop