@extends('adminlte::page')

@section('title', 'Total Parciales OF')

@section('content_header')
<div class="card card-title-header">
    <h4 class="text-center">Total Parciales OF:</h4>  
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="info-section">
            <span class="card-title titulo-cantidad">Cantidad de piezas fabricadas:</span>
            <span id="totalCantPiezas" class="total-numero">{{ $totalCantPiezas }}</span>
        </div>
        <a href="{{ route('fabricacion.create') }}" class="btn btn-primary create-button">Ir a carga de producción</a>
    </div>
</div>
@stop

@section('content')
<div class="table-responsive">
    <table id="registro_de_fabricacion" class="table table-striped table-bordered" style="width:100%">
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
                <th>Creado el</th>
                <th>Actualizado el</th>
                <th>Acciones</th>        
            </tr>
        </thead>
        <tbody>
            @foreach ($registros as $registro)
                <tr>
                    <td>{{ $registro->Id_OF }}</td>
                    <td>{{ $registro->listado_of->Nro_OF }}</td>
                    <td>{{ $registro->Nro_Parcial }}</td>
                    <td>{{ $registro->Nro_OF_Parcial }}</td>
                    <td>{{ $registro->Cant_Piezas }}</td>
                    <td>{{ $registro->Fecha_Fabricacion }}</td>
                    <td>{{ $registro->Horario }}</td>
                    <td>{{ $registro->Nombre_Operario }}</td>
                    <td>{{ $registro->Turno }}</td>
                    <td>{{ $registro->Cant_Horas_Extras }}</td>
                    <td>{{ $registro->created_at }}</td>
                    <td>{{ $registro->updated_at }}</td>
                    <td>
                        <div class="d-flex justify-content-center">
                            @can('editar produccion')
                            <a href="{{ route('fabricacion.edit', $registro->Id_OF) }}" class="btn btn-info btn-sm">Editar</a>
                            @endcan
                            @can('eliminar registros')
                            <button type="button" class="btn btn-danger btn-sm trigger-delete" data-id="{{ $registro->Id_OF }}">Eliminar</button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fabricacion_show.css') }}">
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#registro_de_fabricacion').DataTable({
        scrollY: '70vh',
        scrollCollapse: true,
        paging: true,
        fixedHeader: true,
        responsive: true,
        orderCellsTop: true,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
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
    $('#totalCantPiezas').text(totalCantPiezas.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
});
</script>
@stop
