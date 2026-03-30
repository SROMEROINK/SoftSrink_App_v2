@extends('adminlte::page')

@section('title', 'Editar Ingreso de Materia Prima')
{{-- resources\views\materia_prima\ingresos\edit.blade.php --}}
@section('content_header')
    <h1>Editar Ingreso de Materia Prima</h1>
@stop


@section('content')
    @include('components.swal-session')

    <form action="{{ route('mp_ingresos.update', $ingreso->Id_MP) }}" method="POST" data-edit-check="true" data-exclude-fields="_token,_method" data-redirect-url="{{ route('mp_ingresos.index') }}" data-success-message="Ingreso de materia prima actualizado correctamente">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="Nro_Ingreso_MP">NÃºmero de Ingreso MP:</label>
            <input type="number" class="form-control" id="Nro_Ingreso_MP" name="Nro_Ingreso_MP" value="{{ $ingreso->Nro_Ingreso_MP }}" required>
        </div>

        <div class="form-group">
            <label for="Nro_Pedido">NÃºmero de Pedido:</label>
            <input type="text" class="form-control" id="Nro_Pedido" name="Nro_Pedido" value="{{ $ingreso->Nro_Pedido }}" required>
        </div>

        <div class="form-group">
            <label for="Nro_Remito">NÃºmero de Remito:</label>
            <input type="text" class="form-control" id="Nro_Remito" name="Nro_Remito" value="{{ $ingreso->Nro_Remito }}" required>
        </div>

        <div class="form-group">
            <label for="Fecha_Ingreso">Fecha de Ingreso:</label>
            <input type="date" class="form-control" id="Fecha_Ingreso" name="Fecha_Ingreso" value="{{ $ingreso->Fecha_Ingreso }}" required>
        </div>

        <div class="form-group">
            <label for="Nro_OC">NÃºmero de Orden de Compra:</label>
            <input type="text" class="form-control" id="Nro_OC" name="Nro_OC" value="{{ $ingreso->Nro_OC }}" required>
        </div>

        <div class="form-group">
            <label for="Id_Proveedor">Proveedor:</label>
            <select name="Id_Proveedor" id="Id_Proveedor" class="form-control">
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->Prov_Id }}" {{ $ingreso->Id_Proveedor == $proveedor->Prov_Id ? 'selected' : '' }}>
                        {{ $proveedor->Prov_Nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="Id_Materia_Prima">Materia Prima:</label>
            <select name="Id_Materia_Prima" id="Id_Materia_Prima" class="form-control">
                @foreach ($materiasPrimas as $materia)
                    <option value="{{ $materia->Id_Materia_Prima }}" {{ $ingreso->Id_Materia_Prima == $materia->Id_Materia_Prima ? 'selected' : '' }}>
                        {{ $materia->Nombre_Materia }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="Id_Diametro_MP">DiÃ¡metro:</label>
            <select name="Id_Diametro_MP" id="Id_Diametro_MP" class="form-control">
                @foreach ($diametros as $diametro)
                    <option value="{{ $diametro->Id_Diametro }}" {{ $ingreso->Id_Diametro_MP == $diametro->Id_Diametro ? 'selected' : '' }}>
                        {{ $diametro->Valor_Diametro }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="Codigo_MP">CÃ³digo de Materia Prima:</label>
            <input type="text" class="form-control" id="Codigo_MP" name="Codigo_MP" value="{{ $ingreso->Codigo_MP }}" required readonly>
        </div>

        <div class="form-group">
            <label for="Nro_Certificado_MP">NÃºmero de Certificado:</label>
            <input type="text" class="form-control" id="Nro_Certificado_MP" name="Nro_Certificado_MP" value="{{ $ingreso->Nro_Certificado_MP }}">
        </div>

        <div class="form-group">
            <label for="Detalle_Origen_MP">Detalle de Origen:</label>
            <input type="text" class="form-control" id="Detalle_Origen_MP" name="Detalle_Origen_MP" value="{{ $ingreso->Detalle_Origen_MP }}">
        </div>

        <div class="form-group">
            <label for="Unidades_MP">Unidades de Materia Prima:</label>
            <input type="number" class="form-control" id="Unidades_MP" name="Unidades_MP" value="{{ $ingreso->Unidades_MP }}" required>
        </div>

        <div class="form-group">
            <label for="Longitud_Unidad_MP">Longitud por Unidad:</label>
            <input type="number" class="form-control" id="Longitud_Unidad_MP" name="Longitud_Unidad_MP" value="{{ $ingreso->Longitud_Unidad_MP }}" required>
        </div>

        <div class="form-group">
            <label for="Mts_Totales">Metros Totales:</label>
            <input type="number" class="form-control" id="Mts_Totales" name="Mts_Totales" value="{{ $ingreso->Mts_Totales }}" required readonly>
        </div>

        <div class="form-group">
            <label for="Kilos_Totales">Kilos Totales:</label>
            <input type="number" class="form-control" id="Kilos_Totales" name="Kilos_Totales" value="{{ $ingreso->Kilos_Totales }}" required>
        </div>

        <div class="form-group">
            <label for="reg_Status">Estado:</label>
            <select name="reg_Status" id="reg_Status" class="form-control">
                <option value="1" {{ $ingreso->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ $ingreso->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('mp_ingresos.index') }}" class="btn btn-default">Cancelar</a>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_ingreso_edit.css') }}">
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
<script>
$(document).ready(function() {
    // FunciÃ³n para concatenar "Materia Prima" y "DiÃ¡metro" en "CÃ³digo de Materia Prima"
    function updateConcatenatedField() {
        const materiaPrima = $('#Id_Materia_Prima option:selected').text().trim();
        const diametro = $('#Id_Diametro_MP option:selected').text().trim();
        $('#Codigo_MP').val(`${materiaPrima} _ ${diametro}`);
    }

    $('#Id_Materia_Prima, #Id_Diametro_MP').change(updateConcatenatedField);
    updateConcatenatedField();

    // Calcular Metros Totales automÃ¡ticamente
    function calculateMetrosTotales() {
        const unidades = parseFloat($('#Unidades_MP').val()) || 0;
        const longitud = parseFloat($('#Longitud_Unidad_MP').val()) || 0;
        $('#Mts_Totales').val((unidades * longitud).toFixed(2));
    }

    $('#Unidades_MP, #Longitud_Unidad_MP').on('input', calculateMetrosTotales);
    calculateMetrosTotales();

    // Actualizar el "Detalle de Origen" segÃºn el prefijo de "NÃºmero de Certificado"
    $('#Nro_Certificado_MP').on('input', function() {
        const certificado = $(this).val().trim();
        $('#Detalle_Origen_MP').val(certificado.startsWith('YT') ? 'CHINA' : '');
    });
});
</script>
@stop


