{{-- resources/views/Fabricacion/edit.blade.php --}}

@extends('adminlte::page')

@section('title', 'Editar Registro')

@section('content_header')
<div style="display: flex; justify-content: center; align-items: center;">
    <h1 style="margin-right: 20px;">Actualizar OF {{ $registro_fabricacion->Nro_OF }}</h1>
    <a href="{{ route('fabricacion.create') }}" class="btn btn-success">Ir a Carga de Producción</a>
</div>
@stop

@section('content')
    @if(session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    <form id="updateForm" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="Nro_OF">Número OF:</label>
            <input type="number" class="form-control" id="Nro_OF" name="Nro_OF" value="{{ $registro_fabricacion->Nro_OF }}" required>
        </div>
        <div class="form-group">
            <label for="Nro_Parcial">Nro Parcial:</label>
            <input type="number" class="form-control" id="Nro_Parcial" name="Nro_Parcial" value="{{ $registro_fabricacion->Nro_Parcial }}" required>
        </div>
        <div class="form-group">
            <label for="Nro_OF_Parcial">Nro OF Parcial:</label>
            <input type="text" class="form-control" id="Nro_OF_Parcial" name="Nro_OF_Parcial" value="{{ $registro_fabricacion->Nro_OF_Parcial }}" required>
        </div>
        <div class="form-group">
            <label for="Cant_Piezas">Cantidad de Piezas:</label>
            <input type="number" class="form-control" id="Cant_Piezas" name="Cant_Piezas" value="{{ $registro_fabricacion->Cant_Piezas }}" required>
        </div>
        <div class="form-group">
            <label for="Fecha_Fabricacion">Fecha de Fabricación:</label>
            <input type="date" class="form-control" id="Fecha_Fabricacion" name="Fecha_Fabricacion" value="{{ $registro_fabricacion->Fecha_Fabricacion }}" required>
        </div>
        <div class="form-group">
            <label for="Horario">Horario:</label>
            <select class="form-control" id="Horario" name="Horario" required>
                <option value="">Seleccione</option>
                <option value="H.Normales" {{ $registro_fabricacion->Horario == 'H.Normales' ? 'selected' : '' }}>H.Normales</option>
                <option value="H.Extras" {{ $registro_fabricacion->Horario == 'H.Extras' ? 'selected' : '' }}>H.Extras</option>
                <option value="H.Extras/Sábados" {{ $registro_fabricacion->Horario == 'H.Extras/Sábados' ? 'selected' : '' }}>H.Extras/Sábados</option>
            </select>
        </div>
        <div class="form-group">
            <label for="Nombre_Operario">Nombre del Operario:</label>
            <select class="form-control" id="Nombre_Operario" name="Nombre_Operario" required>
                <!-- Las opciones se cargarán dinámicamente con JavaScript -->
            </select>
        </div>
        <div class="form-group">
            <label for="Turno">Turno:</label>
            <input type="text" class="form-control" id="Turno" name="Turno" value="{{ $registro_fabricacion->Turno }}" required>
        </div>
        <div class="form-group">
            <label for="Cant_Horas_Extras">Cantidad de Horas Extras:</label>
            <input type="number" class="form-control" id="Cant_Horas_Extras" name="Cant_Horas_Extras" value="{{ $registro_fabricacion->Cant_Horas_Extras }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <input type="hidden" name="id" value="{{ $registro_fabricacion->Id_OF }}">
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_edit.css') }}">
@stop

@section('js')
    <script>
        function updateOperarioOptions(horario) {
            var operarioSelect = $('#Nombre_Operario');
            var turnoInput = $('#Turno');
            var horasExtrasInput = $('#Cant_Horas_Extras');
        
            operarioSelect.empty();
        
            var opcionesOperario = {
                "H.Extras": [{ value: "B.Abtt", text: "B.Abtt" }, { value: "G.Silva", text: "G.Silva" }, { value: "T.Berraz", text: "T.Berraz" }],
                "H.Extras/Sábados": [{ value: "B.Abtt", text: "B.Abtt" }, { value: "G.Silva", text: "G.Silva" }, { value: "T.Berraz", text: "T.Berraz" }]
            };
        
            var turnoYHoras = {
                "H.Normales": { turno: "Mañana", horas: 8 },
                "H.Extras": { turno: "Tarde", horas: 3 },
                "H.Extras/Sábados": { turno: "Mañana", horas: 6 }
            };
        
            if (horario === "H.Normales") {
                operarioSelect.empty().append(new Option(" ' ' ", " ' ' ")).val(" ' ' ").prop('disabled', false);
                turnoInput.val(turnoYHoras[horario].turno);
                horasExtrasInput.val(turnoYHoras[horario].horas);
            } else if (horario in opcionesOperario) {
                opcionesOperario[horario].forEach(function(opcion) {
                    operarioSelect.append(new Option(opcion.text, opcion.value));
                });
                turnoInput.val(turnoYHoras[horario].turno);
                horasExtrasInput.val(turnoYHoras[horario].horas);
            } else {
                turnoInput.val('');
                horasExtrasInput.val('');
                operarioSelect.prop('disabled', true);
            }
        }
        
        $('#Horario').change(function() {
            updateOperarioOptions($(this).val());
        });
        
        updateOperarioOptions($('#Horario').val());
    </script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#updateForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: '{{ route('fabricacion.update', ['fabricacion' => $registro_fabricacion->Id_OF]) }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            position: 'center',  // Cambiado a 'center'
                            icon: 'success',
                            title: 'Registro actualizado correctamente',
                            showConfirmButton: false,
                            timer: 1500
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                window.location = '{{ route('fabricacion.showByNroOF', ['nroOF' => $registro_fabricacion->Nro_OF]) }}';
                            }
                        });
                    },
                    error: function(response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo actualizar el registro.',
                            position: 'center'  // Añadido para centrar la alerta de error
                        });
                    }
                });
            });
        });
    </script>
@stop
