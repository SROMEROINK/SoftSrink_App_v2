@extends('adminlte::page')
{{-- resources\views\Fabricacion\show.blade.php --}}
@section('title', 'Editar - Registros')

<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_show.css') }}">

@section('content_header')
<div class="text-center mb-2">
    <h1 class="text-center text-primary">Total Parciales OF:</h1>
</div>
@stop

@section('content')
<div class="container-fluid d-flex justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="d-flex justify-content-end align-items-center mt-2">
                <span class="mr-2 text-primary">Cantidad de piezas fabricadas:</span>
                <span id="totalCantPiezas" class="total-numero">500</span>
            </div>
            <div class="d-flex justify-content-end align-items-center mt-2">
                <span id="Carga" class="text-primary mr-2">Ir a carga de producción</span>
                <a href="{{ route('fabricacion.create') }}" class="btn btn-primary btn-sm">Carga</a>
            </div>
        </div>
        <!-- Tu contenido va aquí -->
        
        <table id="registro_de_fabricacion" class="display table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Id_OF</th>
                    <th>Nro_OF</th>
                    <th>Nro_Parcial</th>
                    <th>Nro_OF_Parcial</th>
                    <th>Cant_Piezas</th>
                    <th>Fecha_Fabricacion</th>
                    <th>Horario</th>
                    <th>Nombre_Operario</th>
                    <th>Turno</th>
                    <th>Cant_Horas_Extras</th>
                    <th width="150px">Acciones</th>        
                </tr>
            </thead>
            <tbody>
                @php
                    $totalCantPiezas = 0;
                @endphp
                @foreach ($registros as $registro)
                    <tr>
                        <td>{{$registro->Id_OF}}</td>
                        <td>{{$registro->listado_of->Nro_OF}}</td>
                        <td>{{$registro->Nro_Parcial}}</td>
                        <td>{{$registro->Nro_OF_Parcial}}</td>
                        <td>{{$registro->Cant_Piezas}}</td>
                        <td>{{$registro->Fecha_Fabricacion}}</td>
                        <td>{{$registro->Horario}}</td>
                        <td>{{$registro->Nombre_Operario}}</td>
                        <td>{{$registro->Turno}}</td>
                        <td>{{$registro->Cant_Horas_Extras}}</td>
                        <td>
                            @can('editar produccion')
                            <a href="{{ route('fabricacion.edit', $registro->Id_OF) }}" class="btn btn-info btn-sm mb-1">Editar</a>
                            <button type="button" class="btn btn-danger btn-sm trigger-delete" data-id="{{ $registro->Id_OF }}">Eliminar</button>
                            @endcan
                        </td>
                        @php
                            $totalCantPiezas += $registro->Cant_Piezas;
                        @endphp
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.2/css/fixedHeader.dataTables.min.css">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.2.2/js/dataTables.fixedHeader.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#registro_de_fabricacion').DataTable({
        responsive: true,
        orderCellsTop: true,
        fixedHeader: {
            header: true,
            footer: false
        },
        pageLength: 50,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columnDefs: [
            { targets: [10], orderable: false, searchable: false }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
        }
    });

    $('.trigger-delete').on('click', function() {
        var id = $(this).data('id');
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success custom-confirm-button',
                cancelButton: 'btn btn-danger custom-cancel-button',
                actions: 'sweet-alert-actions'
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, deseo eliminar el registro!',
            cancelButtonText: 'No, quiero cancelar!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/fabricacion/' + id,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        swalWithBootstrapButtons.fire({
                            title: 'Eliminado!',
                            text: response.message,
                            icon: 'success',
                            showConfirmButton: true
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    },
                    error: function() {
                        swalWithBootstrapButtons.fire(
                            'Error',
                            'No se pudo eliminar el registro.',
                            'error'
                        );
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                swalWithBootstrapButtons.fire(
                    'Cancelado',
                    'El registro está a salvo :)',
                    'error'
                );
            }
        });
    });

    var totalCantPiezas = 0;
    table.rows().every(function() {
        var rowData = this.data();
        totalCantPiezas += parseFloat(rowData[4]);
    });
    $('#totalCantPiezas').text(totalCantPiezas);
});
</script>
@stop
