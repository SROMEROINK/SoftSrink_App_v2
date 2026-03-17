@extends('adminlte::page')

@section('title', 'Crear Registros Multifiла')

@section('content_header')
    <h1>Crear Registros Multifiла</h1>
@stop

@section('content')

@if(!empty($ultimoRegistro))
<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title">Último registro cargado</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><b>ID:</b> {{ $ultimoRegistro->Id_Modulo }}</div>
            <div class="col-md-4"><b>Campo 1:</b> {{ $ultimoRegistro->Campo_1 ?? '—' }}</div>
            <div class="col-md-4"><b>Campo 2:</b> {{ $ultimoRegistro->Campo_2 ?? '—' }}</div>
        </div>
    </div>
</div>
@endif

<form method="POST"
      action="{{ route('modulo.store') }}"
      id="form-modulo-multifila"
      data-ajax="true"
      data-redirect-url="{{ route('modulo.index') }}">

    @csrf

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Detalle de carga</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered custom-font centered-form" id="tablaModuloMultifila">
                    <thead>
                        <tr>
                            <th>N° Fila</th>
                            <th>Campo 1</th>
                            <th>Campo 2</th>
                            <th>Campo 3</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <input type="hidden" name="reg_Status" value="1">

            <div class="btn-der mt-3">
                <button type="button" class="btn btn-success" id="agregarFila">Agregar Fila</button>
                <button type="submit" class="btn btn-primary">Guardar Registros</button>
                <a href="{{ route('modulo.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</form>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_multifila_reutilizable/modulo_create.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>

<script>
$(document).ready(function () {
    let filaCounter = 1;

    function generarFila(nroFila) {
        return `
            <tr>
                <td class="nro-fila">${nroFila}</td>
                <td>
                    <input type="text" name="Campo_1[]" class="form-control campo-1" maxlength="255" required>
                </td>
                <td>
                    <input type="text" name="Campo_2[]" class="form-control campo-2" maxlength="255" required>
                </td>
                <td>
                    <input type="text" name="Campo_3[]" class="form-control campo-3" maxlength="255">
                </td>
                <td>
                    <button type="button" class="btn btn-danger eliminar-fila">Eliminar Fila</button>
                </td>
            </tr>
        `;
    }

    function actualizarNumeracionFilas() {
        $('#tablaModuloMultifila tbody tr').each(function(index) {
            $(this).find('.nro-fila').text(index + 1);
        });
    }

    $('#agregarFila').on('click', function () {
        $('#tablaModuloMultifila tbody').append(generarFila(filaCounter));
        filaCounter++;
        actualizarNumeracionFilas();
    });

    $('#tablaModuloMultifila').on('click', '.eliminar-fila', function () {
        $(this).closest('tr').remove();
        actualizarNumeracionFilas();

        if ($('#tablaModuloMultifila tbody tr').length === 0) {
            Swal.fire({
                title: 'Advertencia',
                text: 'No hay filas cargadas, agregue una fila.',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
        }
    });

    $('#form-modulo-multifila').on('submit', function (e) {
        if ($('#tablaModuloMultifila tbody tr').length === 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Advertencia',
                text: 'Debe agregar al menos una fila.',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
    });

    $('#tablaModuloMultifila tbody').append(generarFila(filaCounter));
    filaCounter++;
});
</script>
@stop