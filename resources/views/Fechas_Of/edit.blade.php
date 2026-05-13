@extends('adminlte::page')

@section('title', 'Editar Fechas OF')

@section('content_header')
    <h1>Editar OF {{ $fechasOf->Nro_OF_fechas }}</h1>
@stop

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <strong>{{ $fechasOf->pedido->producto->Prod_Codigo ?? 'Sin codigo' }}</strong>
            <span class="text-muted ml-2">{{ $fechasOf->pedido->producto->Prod_Descripcion ?? '' }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('fechas_of.update', $fechasOf->Id_Fechas) }}"
                  method="POST"
                  data-edit-check="true"
                  data-exclude-fields="_token,_method"
                  data-redirect-url="{{ route('fechas_of.index') }}"
                  data-success-message="Registro actualizado correctamente.">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>N° O.F</label>
                            <input type="text" class="form-control" value="{{ $fechasOf->Nro_OF_fechas }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Categoria</label>
                            <input type="text" class="form-control" value="{{ $fechasOf->pedido->producto->categoria->Nombre_Categoria ?? '' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Maquina</label>
                            <input type="text" class="form-control" value="{{ $fechasOf->pedido->definicionMp->Nro_Maquina ?? '' }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label for="Nro_Programa_H1">N° Programa H1</label><input type="text" id="Nro_Programa_H1" name="Nro_Programa_H1" class="form-control" value="{{ $fechasOf->Nro_Programa_H1 }}"></div></div>
                    <div class="col-md-6"><div class="form-group"><label for="Nro_Programa_H2">N° Programa H2</label><input type="text" id="Nro_Programa_H2" name="Nro_Programa_H2" class="form-control" value="{{ $fechasOf->Nro_Programa_H2 }}"></div></div>
                </div>

                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label for="Inicio_PAP">Inicio P.A.P</label><input type="date" id="Inicio_PAP" name="Inicio_PAP" class="form-control" value="{{ $inicioPapForm }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label for="Hora_Inicio_PAP">Hora Inicio P.A.P</label><input type="time" id="Hora_Inicio_PAP" name="Hora_Inicio_PAP" class="form-control" value="{{ $horaInicioPapForm }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label for="Fin_PAP">Fin P.A.P</label><input type="date" id="Fin_PAP" name="Fin_PAP" class="form-control" value="{{ $finPapForm }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label for="Hora_Fin_PAP">Hora Fin P.A.P</label><input type="time" id="Hora_Fin_PAP" name="Hora_Fin_PAP" class="form-control" value="{{ $horaFinPapForm }}"></div></div>
                </div>

                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label for="Inicio_OF">Inicio Produccion</label><input type="date" id="Inicio_OF" name="Inicio_OF" class="form-control" value="{{ $inicioOfForm }}"></div></div>
                    <div class="col-md-4"><div class="form-group"><label for="Finalizacion_OF">Fin Produccion</label><input type="date" id="Finalizacion_OF" name="Finalizacion_OF" class="form-control" value="{{ $finalizacionOfForm }}"></div></div>
                    <div class="col-md-2"><div class="form-group"><label for="Tiempo_Pieza">Tiempo de Pieza</label><input type="text" id="Tiempo_Pieza" name="Tiempo_Pieza" class="form-control" value="{{ $tiempoPiezaForm }}" placeholder="Ej: 2.49"></div></div>
                    <div class="col-md-2"><div class="form-group"><label for="Tiempo_Seg_Display">Cant.Seg x Pieza</label><input type="text" id="Tiempo_Seg_Display" class="form-control" value="{{ number_format((int) $fechasOf->Tiempo_Seg, 0, ',', '.') }}" readonly></div></div>
                </div>

                <div class="d-flex justify-content-end" style="gap:8px;">
                    <a href="{{ route('fechas_of.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
<script>
$(document).ready(function () {
    function calcularTiempoSeg(valor) {
        const raw = String(valor || '').trim().replace(',', '.');

        if (raw === '') return 0;
        if (!/^\d+(\.\d{1,2})?$/.test(raw)) return null;

        const partes = raw.split('.');
        const minutos = parseInt(partes[0], 10) || 0;
        const segundos = partes.length > 1 ? parseInt((partes[1] + '00').slice(0, 2), 10) : 0;

        if (segundos > 59) return null;

        return (minutos * 60) + segundos;
    }

    $('#Tiempo_Pieza').on('input change', function () {
        const totalSeg = calcularTiempoSeg($(this).val());

        $(this).removeClass('is-invalid');

        if (totalSeg === null) {
            $('#Tiempo_Seg_Display').val('Formato invalido');
            $(this).addClass('is-invalid');
            return;
        }

        $('#Tiempo_Seg_Display').val(totalSeg.toLocaleString('es-AR'));
    });
});
</script>
@stop
