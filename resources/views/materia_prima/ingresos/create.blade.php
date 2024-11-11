@extends('adminlte::page')
{{-- resources\views\materia_prima\ingresos\create.blade.php --}}
@section('title', 'Registrar Ingreso de Materia Prima')

@section('content_header')
    <h1>Registrar Ingreso de Materia Prima</h1>
@stop

@section('content')
<form method="post" action="{{ route('mp_ingresos.store') }}">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label for="Nro_Ingreso_MP">Número de Ingreso MP:</label>
                <input type="number" name="Nro_Ingreso_MP" id="Nro_Ingreso_MP" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="Nro_Pedido">Número de Pedido:</label>
                <input type="text" name="Nro_Pedido" id="Nro_Pedido" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="Nro_Remito">Número de Remito:</label>
                <input type="text" name="Nro_Remito" id="Nro_Remito" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="Fecha_Ingreso">Fecha de Ingreso:</label>
                <input type="date" name="Fecha_Ingreso" id="Fecha_Ingreso" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group">
                <label for="Nro_OC">Número de Orden de Compra:</label>
                <input type="text" name="Nro_OC" id="Nro_OC" class="form-control">
            </div>
            <div class="form-group">
                <label for="Id_Proveedor">Proveedor:</label>
                <select name="Id_Proveedor" id="Id_Proveedor" class="form-control">
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->Prov_Id }}" data-tipo="{{ $proveedor->Es_Proveedor_MP ? 'mp' : 'herramientas' }}">
                            {{ $proveedor->Prov_Nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="Id_Materia_Prima">Materia Prima:</label>
                <select name="Id_Materia_Prima" id="Id_Materia_Prima" class="form-control">
                    @foreach ($materiasPrimas as $materia)
                        <option value="{{ $materia->Id_Materia_Prima }}">{{ $materia->Nombre_Materia }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="Id_Diametro_MP">Diámetro:</label>
                <select name="Id_Diametro_MP" id="Id_Diametro_MP" class="form-control">
                    @foreach ($diametros as $diametro)
                        <option value="{{ $diametro->Id_Diametro }}">{{ $diametro->Valor_Diametro }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Campo concatenado (Materia Prima + Diámetro) como solo lectura -->
            <div class="form-group">
                <label for="Codigo_MP">Código de Materia Prima:</label>
                <input type="text" name="Codigo_MP" id="Codigo_MP" class="form-control" readonly>
            </div>

            <div class="form-group">
                <label for="Nro_Certificado_MP">Número de Certificado:</label>
                <input type="text" name="Nro_Certificado_MP" id="Nro_Certificado_MP" class="form-control">
            </div>

            <!-- Campo Detalle de Origen (protegido) -->
            <div class="form-group">
                <label for="Detalle_Origen_MP">Detalle de Origen:</label>
                <input type="text" name="Detalle_Origen_MP" id="Detalle_Origen_MP" class="form-control" readonly>
            </div>

            <div class="form-group">
                <label for="Unidades_MP">Unidades de Materia Prima:</label>
                <input type="number" name="Unidades_MP" id="Unidades_MP" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="Longitud_Unidad_MP">Longitud por Unidad:</label>
                <input type="number" name="Longitud_Unidad_MP" id="Longitud_Unidad_MP" class="form-control" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="Mts_Totales">Metros Totales:</label>
                <input type="number" name="Mts_Totales" id="Mts_Totales" class="form-control" step="0.01" readonly>
            </div>
            <div class="form-group">
                <label for="Kilos_Totales">Kilos Totales:</label>
                <input type="number" name="Kilos_Totales" id="Kilos_Totales" class="form-control" step="0.01" value="0" required>
            </div>

            <input type="hidden" name="reg_Status" value="1">

        </div>
    </div>
    <div class="btn-der">
        <input type="submit" class="btn btn-primary" value="Registrar Ingreso">
    </div>
</form>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_ingreso_create.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Configuración del formulario para envío con AJAX
    $('form').on('submit', function(e) {
        e.preventDefault(); // Evita la recarga de la página
        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Ingreso de materia prima creado exitosamente',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    // Redireccionar después de mostrar la alerta de éxito
                    setTimeout(function() {
                        window.location.href = '{{ route('mp_ingresos.index') }}';
                    }, 1500);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors || {};
                var errorMessages = Object.values(errors).map(function(error) {
                    return error.join('<br>');
                });

                Swal.fire({
                    icon: 'error',
                    title: 'Errores de validación',
                    html: errorMessages.join('<br>'),
                    confirmButtonText: 'Corregir'
                });
            }
        });
    });

    // Funciones adicionales para concatenar campos y calcular valores
    function updateConcatenatedField() {
        const materiaPrima = $('#Id_Materia_Prima option:selected').text();
        const diametro = $('#Id_Diametro_MP option:selected').text();
        const concatenatedText = `${materiaPrima}_${diametro}`;
        $('#Codigo_MP').val(concatenatedText);
    }

    $('#Id_Materia_Prima, #Id_Diametro_MP').change(updateConcatenatedField);
    updateConcatenatedField();

    $('#Nro_Certificado_MP').on('input', function() {
        const certificado = $(this).val();
        if (certificado.startsWith('YT')) {
            $('#Detalle_Origen_MP').val('CHINA');
        } else {
            $('#Detalle_Origen_MP').val('');
        }
    });

    function calculateMetrosTotales() {
        const unidades = parseFloat($('#Unidades_MP').val()) || 0;
        const longitud = parseFloat($('#Longitud_Unidad_MP').val()) || 0;
        const metrosTotales = (unidades * longitud).toFixed(2);
        $('#Mts_Totales').val(metrosTotales);
    }

    $('#Unidades_MP, #Longitud_Unidad_MP').on('input', calculateMetrosTotales);

    $.ajax({
        url: "{{ route('mp_ingresos.ultimo_nro_ingreso') }}", 
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#Nro_Ingreso_MP').val(response.nuevo_nro_ingreso);
            } else {
                console.error('Error al obtener el último número de ingreso.');
            }
        },
        error: function() {
            console.error('Error en la llamada AJAX para obtener el último número de ingreso.');
        }
    });
});
</script>
@stop
