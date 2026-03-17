@extends('adminlte::page')
{{-- resources/views/materia_prima/egresos/create.blade.php --}}

@section('title', 'Registrar Salida de Materia Prima')

@section('content_header')
    <h1>Registrar Salida de Materia Prima</h1>
@stop

@section('content')
<form method="post" action="{{ route('mp_egresos.store') }}" id="form-egreso">
    @csrf

    <div class="card">
        <div class="card-body">

            {{-- Id_Ingreso_MP (FK o identificador según tu tabla) --}}
            <div class="form-group">
                <label for="Id_Ingreso_MP">Ingreso de Materia Prima (Id_Ingreso_MP):</label>
                <select name="Id_Ingreso_MP" id="Id_Ingreso_MP" class="form-control" required>
                    <option value="">-- Seleccionar --</option>

                    {{-- Esperado: $egresos (mp_salidas) --}}
                    @foreach ($egresos as $egreso)

                    @endforeach
                </select>
            </div>

            {{-- Id_OF_Salidas_MP --}}
            <div class="form-group">
                <label for="Id_OF_Salidas_MP">OF (Id_OF_Salidas_MP):</label>
                <select name="Id_OF_Salidas_MP" id="Id_OF_Salidas_MP" class="form-control" required>
                    <option value="">-- Seleccionar --</option>

                    {{-- Esperado: $ordenes o $ofs --}}
                    @foreach ($ordenes as $of)
                        <option value="{{ $of->Nro_OF ?? $of->Id_OF ?? $of->Id_OF_Salidas_MP }}">
                            {{ $of->Nro_OF ?? $of->Id_OF ?? '' }} - {{ $of->Descripcion ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Cantidades --}}
            <div class="form-group">
                <label for="Cantidad_Unidades_MP">Cantidad_Unidades_MP:</label>
                <input type="number" name="Cantidad_Unidades_MP" id="Cantidad_Unidades_MP"
                       class="form-control" step="1" min="0" required value="0">
            </div>

            <div class="form-group">
                <label for="Cantidad_Unidades_MP_Preparadas">Cantidad_Unidades_MP_Preparadas:</label>
                <input type="number" name="Cantidad_Unidades_MP_Preparadas" id="Cantidad_Unidades_MP_Preparadas"
                       class="form-control" step="1" min="0" required value="0">
            </div>

            <div class="form-group">
                <label for="Cantidad_MP_Adicionales">Cantidad_MP_Adicionales:</label>
                <input type="number" name="Cantidad_MP_Adicionales" id="Cantidad_MP_Adicionales"
                       class="form-control" step="1" min="0" required value="0">
            </div>

            <div class="form-group">
                <label for="Cant_Devoluciones">Cant_Devoluciones:</label>
                <input type="number" name="Cant_Devoluciones" id="Cant_Devoluciones"
                       class="form-control" step="0.01" min="0" required value="0">
            </div>

            {{-- Totales (calculados) --}}
            <div class="form-group">
                <label for="Total_Salidas_MP">Total_Salidas_MP:</label>
                <input type="number" name="Total_Salidas_MP" id="Total_Salidas_MP"
                       class="form-control" step="0.01" readonly>
                <small class="text-muted">Se calcula automáticamente (podemos ajustar fórmula según tu lógica real).</small>
            </div>

            <div class="form-group">
                <label for="Total_Mtros_Utilizados">Total_Mtros_Utilizados:</label>
                <input type="number" name="Total_Mtros_Utilizados" id="Total_Mtros_Utilizados"
                       class="form-control" step="0.01" readonly>
                <small class="text-muted">Usa Longitud_Unidad_MP del ingreso (si está disponible).</small>
            </div>

            {{-- Pedido / responsables --}}
            <div class="form-group">
                <label for="Fecha_del_Pedido_Produccion">Fecha_del_Pedido_Produccion:</label>
                <input type="date" name="Fecha_del_Pedido_Produccion" id="Fecha_del_Pedido_Produccion"
                       class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label for="Responsable_Pedido_Produccion">Responsable_Pedido_Produccion:</label>
                <input type="text" name="Responsable_Pedido_Produccion" id="Responsable_Pedido_Produccion"
                       class="form-control" value="{{ auth()->user()->name ?? '' }}" required>
            </div>

            <div class="form-group">
                <label for="Nro_Pedido_MP">Nro_Pedido_MP:</label>
                <input type="number" name="Nro_Pedido_MP" id="Nro_Pedido_MP"
                       class="form-control" step="1" min="0">
            </div>

            {{-- Calidad --}}
            <div class="form-group">
                <label for="Fecha_de_Entrega_Pedido_Calidad">Fecha_de_Entrega_Pedido_Calidad:</label>
                <input type="date" name="Fecha_de_Entrega_Pedido_Calidad" id="Fecha_de_Entrega_Pedido_Calidad"
                       class="form-control">
            </div>

            <div class="form-group">
                <label for="Responsable_de_entrega_Calidad">Responsable_de_entrega_Calidad:</label>
                <input type="text" name="Responsable_de_entrega_Calidad" id="Responsable_de_entrega_Calidad"
                       class="form-control">
            </div>

            <input type="hidden" name="reg_Status" value="1">

        </div>
    </div>

    <div class="btn-der">
        <button type="submit" class="btn btn-primary">Registrar Salida</button>
    </div>
</form>
@stop

@section('css')
{{-- Si querés, creamos un css específico: mp_egreso_create.css --}}
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_egreso_create.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    function toNum(v) {
        let n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    // Fórmula base (ajustable)
    // Total_Salidas_MP = Cantidad_Unidades_MP + Cantidad_MP_Adicionales - Cant_Devoluciones
    // (No sumo Preparadas porque normalmente "preparadas" es un estado, no un consumo real; si tu lógica es otra, lo ajustamos.)
    function calcularTotales() {
        const unidades = toNum($('#Cantidad_Unidades_MP').val());
        const adicionales = toNum($('#Cantidad_MP_Adicionales').val());
        const devol = toNum($('#Cant_Devoluciones').val());

        const totalSalidas = Math.max((unidades + adicionales - devol), 0);
        $('#Total_Salidas_MP').val(totalSalidas.toFixed(2));

        // Metros usados = totalSalidas * longitud_unidad (tomada del ingreso seleccionado)
        const longitud = toNum($('#Id_Ingreso_MP option:selected').data('longitud'));
        const totalMetros = totalSalidas * longitud;
        $('#Total_Mtros_Utilizados').val(totalMetros.toFixed(2));
    }

    $('#Cantidad_Unidades_MP, #Cantidad_MP_Adicionales, #Cant_Devoluciones, #Id_Ingreso_MP').on('input change', calcularTotales);
    calcularTotales();

    // Submit AJAX + SweetAlert
    $('#form-egreso').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Salida de materia prima creada exitosamente',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    setTimeout(function() {
                        window.location.href = "{{ route('mp_egresos.index') }}";
                    }, 1500);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                const errorMessages = Object.values(errors).map(e => e.join('<br>'));

                Swal.fire({
                    icon: 'error',
                    title: 'Errores de validación',
                    html: errorMessages.join('<br>'),
                    confirmButtonText: 'Corregir'
                });
            }
        });
    });

});
</script>
@stop
