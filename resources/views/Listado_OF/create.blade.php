@extends('adminlte::page')

@section('title', 'Crear Nueva Orden de Fabricación')

@section('content_header')
    <h1>Crear Nueva Orden de Fabricación</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/Listado_OF_create.css') }}">
@stop

@section('content')
    <form method="post" action="{{ route('listado_of.store') }}">
        @csrf
        <table class="table table-bordered custom-font centered-form" id="tablaListadoOF">
            <thead>
                <tr>
                    <th class="col-nro-fila">N° Fila</th>
                    <th class="col-nro-of">N° OF</th>
                    <th class="col-estado-planificacion">Estado de Planificación</th>
                    <th class="col-estado">Estado</th>
                    <th class="col-id-producto">ID del Producto</th>
                    <th class="col-revision-plano-1">Revisión Plano 1</th>
                    <th class="col-revision-plano-2">Revisión Plano 2</th>
                    <th class="col-fecha-del-pedido">Fecha del Pedido</th>
                    <th class="col-cant-fabricacion">Cantidad de Fabricación</th>
                    <th class="col-nro-maquina">Número de Máquina</th>
                    <th class="col-familia-maquinas">Familia de Máquinas</th>
                    <th class="col-mp-id">ID del Material Primario</th>
                    <th class="col-pedido-mp">Pedido de MP</th>
                    <th class="col-tiempo-pieza-real">Tiempo Pieza Real</th>
                    <th class="col-tiempo-pieza-aprox">Tiempo Pieza Aprox</th>
                    <th class="col-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aquí se irán agregando filas dinámicamente -->
            </tbody>
        </table>
        <div class="btn-der">
            <button type="button" class="btn btn-success" id="agregarFila">Agregar Fila</button>
            <input type="submit" class="btn btn-primary" value="Guardar Cambios">
        </div>
    </form>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Inicializar el contador de filas
    var filaCounter = 1;

    // Función para generar una nueva fila de la tabla
    function generarFila(filaCounter) {
        return `<tr>
                    <td>${filaCounter}</td>
                    <td><input type="number" name="nro_of[]" class="form-control" required></td>
                    <td><input type="text" name="estado_planificacion[]" class="form-control" required></td>
                    <td><input type="text" name="estado[]" class="form-control" required></td>
                    <td><input type="number" name="producto_id[]" class="form-control" required></td>
                    <td><input type="text" name="revision_plano_1[]" class="form-control" required></td>
                    <td><input type="text" name="revision_plano_2[]" class="form-control" required></td>
                    <td><input type="date" name="fecha_del_pedido[]" class="form-control" required></td>
                    <td><input type="number" name="cant_fabricacion[]" class="form-control" required></td>
                    <td><input type="text" name="nro_maquina[]" class="form-control"></td>
                    <td><input type="text" name="familia_maquinas[]" class="form-control"></td>
                    <td><input type="number" name="mp_id[]" class="form-control" required></td>
                    <td><input type="text" name="pedido_de_mp[]" class="form-control"></td>
                    <td><input type="number" step="0.01" name="tiempo_pieza_real[]" class="form-control"></td>
                    <td><input type="number" step="0.01" name="tiempo_pieza_aprox[]" class="form-control"></td>
                    <td><button type="button" class="btn btn-danger eliminarFila">Eliminar</button></td>
                </tr>`;
    }

    // Evento para agregar una nueva fila
    $('#agregarFila').click(function() {
        $('#tablaListadoOF tbody').append(generarFila(filaCounter));
        filaCounter++;
    });

    // Evento para eliminar una fila
    $(document).on('click', '.eliminarFila', function() {
        $(this).closest('tr').remove();
    });
});
</script>
@stop
