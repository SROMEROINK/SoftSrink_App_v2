@extends('adminlte::page')

@section('title', 'Registros de Fabricación')

@section('content_header')
    <h1>Registro de Fabricación</h1>
@stop

@section('content')
    <div class="container-fluid">
        <table id="registro_de_fabricacion" class="display table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID OF</th>
                    <th>Nro OF</th>
                    <th>ID Producto</th>
                    <th>Nro Parcial</th>
                    <th>Cantidad de Piezas</th>
                    <th>Fecha de Fabricación</th>
                    <th>Horario</th>
                    <th>Nombre del Operario</th>
                    <th>Turno</th>
                    <th>Horas Extras</th>
                    <th>Creado el</th>
                    <th>Actualizado el</th>
                    <th>Creado por</th>
                    <th>Actualizado por</th>
                </tr>
            </thead>
            <tbody>
                <!-- Los datos se llenarán automáticamente -->
            </tbody>
        </table>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.2/css/fixedHeader.dataTables.min.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.2.2/js/dataTables.fixedHeader.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#registro_de_fabricacion').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('fabricacion.data') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'Id_OF', name: 'Id_OF' },
                    { data: 'Nro_OF', name: 'Nro_OF' },
                    { data: 'Id_Producto', name: 'Id_Producto' },
                    { data: 'Nro_Parcial', name: 'Nro_Parcial' },
                    { data: 'Cant_Piezas', name: 'Cant_Piezas' },
                    { data: 'Fecha_Fabricacion', name: 'Fecha_Fabricacion' },
                    { data: 'Horario', name: 'Horario' },
                    { data: 'Nombre_Operario', name: 'Nombre_Operario' },
                    { data: 'Turno', name: 'Turno' },
                    { data: 'Cant_Horas_Extras', name: 'Cant_Horas_Extras' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'updated_by', name: 'updated_by' }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                }
            });
        });
    </script>
@stop
