@extends('adminlte::page')
<!-- resources\views\Fabricacion\create.blade.php -->
@section('title', 'Carga de Produccion')

@section('content_header')
    <h1>Carga de Producci&oacute;n</h1>
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
                    <th class="col-nro-fila">N&deg; Fila</th>
                    <th class="col-nro-of">N&deg; OF</th>
                    <th class="col-id-producto">Id_Producto</th>
                    <th class="col-nro-parcial">N&deg; de Parcial</th>
                    <th class="col-nro-of-parcial">Nro_OF_Parcial</th>
                    <th class="col-cant-piezas">Cant.Piezas</th>
                    <th class="col-fecha-fabricacion">Fecha de Fabricaci&oacute;n</th>
                    <th class="col-horario">Horario</th>
                    <th class="col-operario">Operario</th>
                    <th class="col-turno">Turno</th>
                    <th class="col-cant-horas">Cant. de Horas</th>
                    <th class="col-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
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
        '\u00a1Bienvenido a la carga de datos!',
        'Tenga cuidado al duplicar los parciales de las OF.',
        'success'
    );

    agregarFila();
});

function obtenerFechaHoyDisplay() {
    var hoy = new Date();
    var dia = String(hoy.getDate()).padStart(2, '0');
    var mes = String(hoy.getMonth() + 1).padStart(2, '0');
    var anio = hoy.getFullYear();

    return dia + '/' + mes + '/' + anio;
}

function normalizarFechaParaSubmit(fecha) {
    if (!fecha) {
        return '';
    }

    fecha = fecha.trim();

    if (/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
        return fecha;
    }

    var match = fecha.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) {
        return '';
    }

    var dia = parseInt(match[1], 10);
    var mes = parseInt(match[2], 10);
    var anio = parseInt(match[3], 10);
    var fechaValidada = new Date(anio, mes - 1, dia);

    if (
        fechaValidada.getFullYear() !== anio ||
        fechaValidada.getMonth() !== mes - 1 ||
        fechaValidada.getDate() !== dia
    ) {
        return '';
    }

    return match[3] + '-' + match[2] + '-' + match[1];
}

function renumerarFilas() {
    $('#tablaProduccion tbody tr').each(function(index) {
        $(this).find('.nro-fila').text(index + 1);
    });
}

function actualizarNroOfParcial($fila) {
    var nroOf = $fila.find('input[name="nro_of[]"]').val();
    var nroParcial = $fila.find('input[name="nro_parcial[]"]').val();

    if (nroOf && nroParcial) {
        $fila.find('input[name="Nro_OF_Parcial[]"]').val(nroOf + '/' + nroParcial);
    } else {
        $fila.find('input[name="Nro_OF_Parcial[]"]').val('');
    }
}

function actualizarOpcionesOperario($fila, horarioValue) {
    var $operarioSelect = $fila.find('.operario');
    var $turnoInput = $fila.find('.turno');
    var $cantHorasInput = $fila.find('input[name="cant_horas[]"]');
    var opcionesOperario = {
        'H.Extras': [
            { value: 'B.Abtt', text: 'B.Abtt' },
            { value: 'G.Silva', text: 'G.Silva' },
            { value: 'T.Berraz', text: 'T.Berraz' }
        ],
        'H.Extras/S\u00e1bados': [
            { value: 'B.Abtt', text: 'B.Abtt' },
            { value: 'G.Silva', text: 'G.Silva' },
            { value: 'T.Berraz', text: 'T.Berraz' }
        ]
    };

    $operarioSelect.empty().prop('disabled', false);

    if (horarioValue === 'H.Normales') {
        $turnoInput.val('Ma\u00f1ana');
        $cantHorasInput.val(8);
        $operarioSelect.append(new Option('', ''));
        $operarioSelect.val('');
        return;
    }

    if (horarioValue in opcionesOperario) {
        opcionesOperario[horarioValue].forEach(function(opcion) {
            $operarioSelect.append(new Option(opcion.text, opcion.value));
        });

        if (horarioValue === 'H.Extras') {
            $turnoInput.val('Tarde');
            $cantHorasInput.val(3);
        } else {
            $turnoInput.val('Ma\u00f1ana');
            $cantHorasInput.val(6);
        }

        return;
    }

    $turnoInput.val('');
    $cantHorasInput.val('');
    $operarioSelect.append(new Option('Seleccione', ''));
    $operarioSelect.val('');
}

function generarFila() {
    var fechaHoy = obtenerFechaHoyDisplay();

    return `<tr>
                <td class="nro-fila"></td>
                <td><input type="number" class="nro_of_input" name="nro_of[]" min="0" step="1" autocomplete="off"></td>
                <td><input type="number" name="Id_Producto[]" autocomplete="off"></td>
                <td><input type="number" name="nro_parcial[]" autocomplete="off"></td>
                <td><input type="text" name="Nro_OF_Parcial[]" readonly></td>
                <td><input type="number" name="cant_piezas[]" autocomplete="off"></td>
                <td><input type="text" name="fecha_fabricacion[]" value="${fechaHoy}" placeholder="dd/mm/aaaa" inputmode="numeric" autocomplete="off"></td>
                <td>
                    <select class="form-control horario" name="horario[]">
                        <option value="">Seleccione</option>
                        <option value="H.Normales" selected>H.Normales</option>
                        <option value="H.Extras">H.Extras</option>
                        <option value="H.Extras/S\u00e1bados">H.Extras/S&aacute;bados</option>
                    </select>
                </td>
                <td>
                    <select class="form-control operario" name="operario[]">
                        <option value=""></option>
                    </select>
                </td>
                <td><input type="text" class="form-control turno" name="turno[]" readonly></td>
                <td><input type="number" name="cant_horas[]" readonly></td>
                <td><button type="button" class="btn btn-danger eliminar">Eliminar Fila</button></td>
            </tr>`;
}

function agregarFila() {
    $('#tablaProduccion tbody').append(generarFila());

    var $nuevaFila = $('#tablaProduccion tbody tr:last');
    actualizarOpcionesOperario($nuevaFila, 'H.Normales');
    renumerarFilas();
    $nuevaFila.find('input[name="nro_of[]"]').trigger('focus');
}

$('#agregarFila').click(function() {
    agregarFila();
});

$('#tablaProduccion').on('click', '#edit_of', function() {
    var nroOF = $(this).closest('tr').find('input[name="nro_of[]"]').val();

    if (!nroOF) {
        Swal.fire({
            title: 'Error',
            text: 'Debe completar el campo N\u00b0 OF antes de editar.',
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Entendido'
        });
    } else {
        window.location.href = `/fabricacion/show/${nroOF}`;
    }
});

$(document).on('change', '.horario', function() {
    actualizarOpcionesOperario($(this).closest('tr'), $(this).val());
});

$(document).on('click', '.eliminar', function() {
    $(this).closest('tr').remove();
    renumerarFilas();

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
    event.preventDefault();

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

    var fechaInvalida = false;

    $('input[name="fecha_fabricacion[]"]').each(function() {
        var fechaNormalizada = normalizarFechaParaSubmit($(this).val());

        if (!fechaNormalizada) {
            fechaInvalida = true;
            $(this).trigger('focus');
            return false;
        }

        $(this).val(fechaNormalizada);
    });

    if (fechaInvalida) {
        Swal.fire({
            title: 'Fecha invalida',
            text: 'Use el formato dd/mm/aaaa para la fecha de fabricacion.',
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    var formData = $(this).serialize();

    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formData,
        dataType: 'json',
        success: function(response) {
            Swal.fire({
                title: response.success ? 'Exito' : 'Error',
                text: response.message,
                icon: response.success ? 'success' : 'error',
                confirmButtonColor: response.success ? '#3085d6' : '#d33',
                confirmButtonText: response.success ? 'OK' : 'Entendido'
            }).then(function() {
                if (response.success) {
                    location.reload();
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
            var nroOF = $('input[name="nro_of[]"]').first().val();

            Swal.fire({
                title: 'Error de Validacion',
                html: errorString + '<br/>Parciales duplicados: ' + duplicatedFilaNumbers.join(', '),
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Listar_OF',
                showCancelButton: true,
                cancelButtonText: 'Corregir'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/fabricacion/show/${nroOF}`;
                } else if (response.duplicatedRows) {
                    response.duplicatedRows.forEach(function(index) {
                        $('#tablaProduccion tbody tr').eq(index - 1).addClass('duplicated');
                    });
                }
            });
        }
    });
});

function buscarIdProducto(nroOf, $fila) {
    if (!nroOf) {
        $fila.find('input[name="Id_Producto[]"]').val('');
        return;
    }

    $.ajax({
        url: '/pedido-cliente/get-id-producto/' + encodeURIComponent(nroOf),
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

$(document).on('change', 'input[name="nro_of[]"]', function() {
    var $fila = $(this).closest('tr');
    var valorActual = parseInt($(this).val(), 10);

    if (!isNaN(valorActual) && valorActual < 0) {
        $(this).val('');
        $fila.find('input[name="Id_Producto[]"]').val('');
        $fila.find('input[name="Nro_OF_Parcial[]"]').val('');

        Swal.fire({
            title: 'Valor invalido',
            text: 'El N° OF no puede ser negativo.',
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    buscarIdProducto($(this).val(), $fila);
    actualizarNroOfParcial($fila);
});

$(".alert").fadeTo(2000, 500).slideUp(500, function() {
    $(".alert").slideUp(500);
});

$(document).on('change', 'input[name="nro_parcial[]"]', function() {
    actualizarNroOfParcial($(this).closest('tr'));
});

$(document).on('keydown', 'input[name="nro_of[]"], input[name="nro_parcial[]"], input[name="cant_piezas[]"], input[name="fecha_fabricacion[]"]', function(event) {
    if (event.key !== 'Enter') {
        return;
    }

    event.preventDefault();

    var $fila = $(this).closest('tr');
    var nombreCampo = $(this).attr('name');
    var siguienteSelector = '';

    if (nombreCampo === 'nro_of[]') {
        siguienteSelector = 'input[name="nro_parcial[]"]';
    } else if (nombreCampo === 'nro_parcial[]') {
        siguienteSelector = 'input[name="cant_piezas[]"]';
    } else if (nombreCampo === 'cant_piezas[]') {
        siguienteSelector = 'input[name="fecha_fabricacion[]"]';
    } else if (nombreCampo === 'fecha_fabricacion[]') {
        siguienteSelector = 'select[name="horario[]"]';
    }

    if (!siguienteSelector) {
        return;
    }

    var $siguienteCampo = $fila.find(siguienteSelector);
    $siguienteCampo.trigger('focus');

    if ($siguienteCampo.is('input[type="text"], input[type="number"]')) {
        $siguienteCampo.select();
    }
});
</script>
@stop
