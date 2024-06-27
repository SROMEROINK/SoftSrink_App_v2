@extends('adminlte::page')

@section('title', 'Carga de Producción')

@section('content_header')
    <h1>Carga de Producción</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_create.css') }}">
@stop

@section('content')
    <form method="post" action="{{ route('fabricacion.store') }}">
        @csrf
        <table class="table table-bordered custom-font centered-form" id="tablaProduccion">
            <thead>
                <tr>
                    <th class="col-nro-fila">N° Fila</th>
                    <th class="col-nro-of">N° OF</th>
                    <th class="col-id-producto">Id_Producto</th>
                    <th class="col-nro-parcial">N° de Parcial</th>
                    <th class="col-nro-of-parcial">Nro_OF_Parcial</th>
                    <th class="col-cant-piezas">Cant.Piezas</th>
                    <th class="col-fecha-fabricacion">Fecha de Fabricación</th>
                    <th class="col-horario">Horario</th>
                    <th class="col-operario">Operario</th>
                    <th class="col-turno">Turno</th>
                    <th class="col-cant-horas">Cant. de Horas</th>
                    <th class="col-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- La fila inicial queda vacía y oculta para ser usada como plantilla -->
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
    console.log("Hi, I'm using the Laravel-AdminLTE package!");
    // Bienvenida con SweetAlert2
    Swal.fire(
        '¡Bienvenido a la carga de datos!',
        'Tenga cuidado al duplicar los parciales de las OF!!',
        'success'
    );
});




// Agregar una fila base a la tabla
var filaCounter = 1;

function generarFila(filaCounter) {
    var fechaHoy = new Date().toISOString().slice(0, 10);
    return `<tr>
                <td class="nro-fila">${filaCounter}</td>
                <td><input type="number" class="nro_of_input" name="nro_of[]"></td>
                <td><input type="number" name="Id_Producto[]"></td>
                <td><input type="number" name="nro_parcial[]"></td>
                <td><input type="text" name="Nro_OF_Parcial[]" readonly></td>
                <td><input type="number" name="cant_piezas[]"></td>
                <td><input type="date" name="fecha_fabricacion[]" value="${fechaHoy}"></td>
                <td>
                    <select class="form-control horario" name="horario[]">
                        <option value="">Seleccione</option>
                        <option value="H.Normales">H.Normales</option>
                        <option value="H.Extras">H.Extras</option>
                        <option value="H.Extras/Sábados">H.Extras/Sábados</option>
                    </select>
                </td>
                <td>
                    <select class="form-control operario" name="operario[]" disabled>
                        <!-- Las opciones de este select se actualizarán dinámicamente -->
                    </select>
                </td>
                <td><input type="text" class="form-control turno" name="turno[]" readonly></td>
                <td><input type="number" name="cant_horas[]" readonly></td>
                <td><button type="button" class="btn btn-danger eliminar">Eliminar Fila</button></td>
            </tr>`;
}

$('#agregarFila').click(function() {
    $('#tablaProduccion tbody').append(generarFila(filaCounter));
    filaCounter++;
});

$('#tablaProduccion').on('click', '#edit_of', function() {
    var nroOF = $(this).closest('tr').find('input[name="nro_of[]"]').val();
    if (!nroOF) {
        Swal.fire({
            title: 'Error',
            text: 'Debe completar el campo N° OF antes de editar.',
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Entendido'
        });
    } else {
        window.location.href = `/fabricacion/show/${nroOF}`;
    }
});

$(document).on('change', '.horario', function() {
    var $row = $(this).closest('tr');
    var horarioValue = $(this).val();
    var $operarioSelect = $row.find('.operario');
    var $turnoInput = $row.find('.turno');
    var $cantHorasInput = $row.find('input[name="cant_horas[]"]');

    if (horarioValue === "H.Normales") {
        $turnoInput.val("Mañana");
        $cantHorasInput.val(8);
        $operarioSelect.empty().append(new Option(" ' ' ", " ' ' ")).val(" ' ' ").prop('disabled', false); // Establece "Todos" y deshabilita el select
    } else {
        $operarioSelect.prop('disabled', false).empty();
        var opcionesOperario = {
            "H.Extras": [{ value: "B.Abtt", text: "B.Abtt" }, { value: "G.Silva", text: "G.Silva" }, { value: "T.Berraz", text: "T.Berraz" }],
            "H.Extras/Sábados": [{ value: "B.Abtt", text: "B.Abtt" }, { value: "G.Silva", text: "G.Silva" }, { value: "T.Berraz", text: "T.Berraz" }]
        };

        opcionesOperario[horarioValue].forEach(function(opcion) {
            $operarioSelect.append(new Option(opcion.text, opcion.value));
        });
        $operarioSelect.prop('disabled', false);

        if (horarioValue === "H.Extras") {
            $turnoInput.val("Tarde");
            $cantHorasInput.val(3);
        } else if (horarioValue === "H.Extras/Sábados") {
            $turnoInput.val("Mañana");
            $cantHorasInput.val(6);
        }
    }
});

$(document).on('click', '.eliminar', function() {
    $(this).closest('tr').remove();
    if ($('#tablaProduccion tbody tr').length === 0) {
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

    if ($('#tablaProduccion tbody tr').length === 0) {
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
                confirmButtonColor: response.success ? '#3085d6' : '#d33', // Azul para éxito, rojo para error
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

            var duplicatedFilaNumbers = response.duplicatedRows ? response.duplicatedRows.map(function(index) {
                return index;
            }) : [];

            // Obtener el número de OF desde la fila que causó el error
            var nroOF = $('input[name="nro_of[]"]').first().val(); // Cambiado para obtener el primer valor de 'nro_of[]'
            console.log("El Número de OF es:", nroOF); // Agregar mensaje de consola para verificar el valor

            Swal.fire({
                title: 'Error de Validación',
                html: errorString + '<br/>Parciales duplicados: ' + duplicatedFilaNumbers.join(', '),
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Listar_OF',
                showCancelButton: true,
                cancelButtonText: 'Corregir'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/fabricacion/show/${nroOF}`;
                } else {
                    if (response.duplicatedRows) {
                        response.duplicatedRows.forEach(function(index) {
                            $('#tablaProduccion tbody tr').eq(index - 1).addClass('duplicated');
                        });
                    }
                }
            });
        }
    });
});



// Función para buscar y llenar el ID del Producto basado en el Nro de OF
function buscarIdProducto(nroOf, $fila) {
    if (nroOf) {
        $.ajax({
            url: '/listado-of/get-id-producto/' + encodeURIComponent(nroOf),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $fila.find('input[name="Id_Producto[]"]').val(response.id_producto);
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Entendido'
                    });
                    $fila.find('input[name="Id_Producto[]"]').val('');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al buscar el ID del producto: ' + error,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    }
}

// Evento para detectar el cambio en el campo de N° OF
$(document).on('change', 'input[name="nro_of[]"]', function() {
    var nroOf = $(this).val();
    var $fila = $(this).closest('tr'); // La fila donde se hizo el cambio
    buscarIdProducto(nroOf, $fila);
});

// Auto-cierre de alertas después de 5 segundos
$(".alert").fadeTo(2000, 500).slideUp(500, function() {
    $(".alert").slideUp(500);
});

$(document).on('change', 'input[name="nro_of[]"], input[name="nro_parcial[]"]', function() {
    var $fila = $(this).closest('tr');
    var nroOf = $fila.find('input[name="nro_of[]"]').val();
    var nroParcial = $fila.find('input[name="nro_parcial[]"]').val();

    console.log("Nro OF:", nroOf); // Deberías ver el número de OF en la consola al cambiar el valor
    console.log("Nro Parcial:", nroParcial); // Deberías ver el número de Parcial en la consola al cambiar el valor

    if (nroOf && nroParcial) {
        var nroOfParcial = nroOf + '/' + nroParcial;
        $fila.find('input[name="Nro_OF_Parcial[]"]').val(nroOfParcial);
    } else {
        $fila.find('input[name="Nro_OF_Parcial[]"]').val('');
    }
});
</script>
@stop
