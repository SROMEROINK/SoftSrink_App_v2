@extends('adminlte::page')

@section('title', 'Carga de Fechas de Producción')

@section('content_header')
    <h1>Carga de Fechas de Producción</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fechas_of_create.css') }}">
@stop

@section('content')
    <form method="post" action="{{ route('fechas_of.store') }}">
        @csrf
        <table class="table table-bordered custom-font centered-form" id="tablaFechas">
            <thead>
                <tr>
                    <th class="col-nro-fila">N° Fila</th>
                    <th class="col-nro-of">N° OF</th>
                    <th class="col-nro-programa-h1">N° Programa H1</th>
                    <th class="col-nro-programa-h2">N° Programa H2</th>
                    <th class="col-inicio-pap">Inicio PAP</th>
                    <th class="col-hora-inicio-pap">Hora Inicio PAP</th>
                    <th class="col-fin-pap">Fin PAP</th>
                    <th class="col-hora-fin-pap">Hora Fin PAP</th>
                    <th class="col-inicio-of">Inicio OF</th>
                    <th class="col-finalizacion-of">Finalización OF</th>
                    <th class="col-tiempo-pieza">Tiempo Pieza</th>
                    <th class="col-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Fila inicial vacía para ser usada como plantilla -->
            </tbody>
        </table>
        <div class="btn-der">
            <button type="button" class="btn btn-success" id="agregarFila">Agregar Fila</button>
            <input type="submit" class="btn btn-primary" value="Guardar Cambios">
        </div>
    </form>
@stop

@section('js')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    Swal.fire(
        '¡Bienvenido a la carga de Fechas de Producción!',
        'Asegúrese de completar correctamente todos los campos!',
        'success'
    );
});

// Agregar una fila base a la tabla
var filaCounter = 1;

function generarFila(filaCounter) {
    var fechaHoy = new Date().toISOString().slice(0, 10);
    return `<tr>
                <td class="nro-fila">${filaCounter}</td>
                <td><input type="number" class="nro_of_input" name="Nro_OF_fechas[]" required></td>
                <td><input type="text" name="Nro_Programa_H1[]" required></td>
                <td><input type="text" name="Nro_Programa_H2[]" required></td>
                <td><input type="date" name="Inicio_PAP[]" value="${fechaHoy}" required></td>
                <td><input type="time" name="Hora_Inicio_PAP[]" required></td>
                <td><input type="date" name="Fin_PAP[]" value="${fechaHoy}" required></td>
                <td><input type="time" name="Hora_Fin_PAP[]" required></td>
                <td><input type="date" name="Inicio_OF[]" value="${fechaHoy}" required></td>
                <td><input type="date" name="Finalizacion_OF[]" value="${fechaHoy}" required></td>
                <td><input type="number" name="Tiempo_Pieza[]" required></td>
                <td><button type="button" class="btn btn-danger eliminar">Eliminar Fila</button></td>
            </tr>`;
}

$('#agregarFila').click(function() {
    $('#tablaFechas tbody').append(generarFila(filaCounter));
    filaCounter++;
});

$(document).on('click', '.eliminar', function() {
    $(this).closest('tr').remove();
    if ($('#tablaFechas tbody tr').length === 0) {
        Swal.fire({
            title: 'Advertencia',
            text: 'No hay datos, agregue una fila.',
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Entendido'
        });
    }
});

$('form').submit(function(event) {
    event.preventDefault(); // Evitar el envío tradicional del formulario
    var formData = $(this).serialize(); // Serializar los datos del formulario

    if ($('#tablaFechas tbody tr').length === 0) {
        Swal.fire({
            title: 'Advertencia',
            text: 'No hay datos, agregue una fila.',
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formData,
        dataType: 'json', // Esperando una respuesta JSON
        success: function(response) {
            Swal.fire({
                title: response.success ? 'Éxito' : 'Error',
                text: response.message,
                icon: response.success ? 'success' : 'error',
                confirmButtonColor: response.success ? '#3085d6' : '#d33',
                confirmButtonText: response.success ? 'OK' : 'Entendido'
            }).then(function() {
                if (response.success) {
                    location.reload(); // Recargar la página si el registro fue exitoso
                }
            });
        },
        error: function(xhr) {
            var response = JSON.parse(xhr.responseText);
            var errorString = '';
            $.each(response.errors, function(key, value) {
                errorString += value + '<br/>';
            });

            Swal.fire({
                title: 'Error de Validación',
                html: errorString,
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Entendido'
            });
        }
    });
});
</script>
@stop
