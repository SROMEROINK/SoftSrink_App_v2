{{--resources\views\materia_prima\diametro\index.blade.php --}}


@extends('adminlte::page')

@section('title', 'Listado de Diámetros')

@section('content_header')
<x-header-card 
    title="Listado de Diámetros" 
    quantityTitle="Cantidad de Diámetros:" 
    quantity="{{ $totalDiametros }}" 
    buttonRoute="{{ route('mp_diametro.create') }}" 
    buttonText="Crear Diámetro" 
    deletedRouteUrl="{{ route('mp_diametro.deleted') }}"
    deletedButtonText="Ver Diámetros Eliminados" 
/>
@stop
@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-bordered" id="diametros-table">
            <thead>
                <tr>
                    <th>Id_Diametro</th>
                    <th><input type="text" id="filtro_diametro" placeholder="Filtrar por Diámetro" class="form-control filtro-texto"></th>
                    <th>
                        <select id="filtro_estado" class="form-control filtro-select">
                            <option value="">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </th>
                    <th class="acciones">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/mp_diametro_index.css') }}">
@endsection


@section('js')
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables Responsive -->
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>

function deleteDiametro(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarlo'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/mp_diametro/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}' // Incluye el token CSRF aquí
                },
                success: function(response) {
                    $('#diametros-table').DataTable().ajax.reload();
                    Swal.fire('¡Eliminado!', 'El diámetro ha sido eliminado.', 'success');
                },
                error: function() {
                    Swal.fire('¡Error!', 'Ha ocurrido un error al intentar eliminar.', 'error');
                }
            });
        }
    });
}
    
    $(document).ready(function() {
        var table = $('#diametros-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: "{{ route('mp_diametro.data') }}",
                data: function (d) {
                    d.filtro_diametro = $('#filtro_diametro').val();
                    d.filtro_estado = $('#filtro_estado').val();
                }
            },
            columns: [
                { data: 'Id_Diametro', name: 'Id_Diametro' },
                { data: 'Valor_Diametro', name: 'Valor_Diametro' },
                {
                    data: 'reg_Status',
                    render: function (data) {
                        return data === 1 ? 'Activo' : 'Inactivo';
                    },
                    name: 'reg_Status'
                },
                {
                    data: 'Id_Diametro',
                    render: function(data) {
                        return `
                            <a href="/mp_diametro/${data}/edit" class="btn btn-xs btn-primary">Editar</a>
                            <button onclick="deleteDiametro(${data})" class="btn btn-xs btn-danger">Eliminar</button>
                        `;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
            scrollX: true,
            language: { url: "{{ asset('Spanish.json') }}" }
        });
    
        // Filtros
        $('#filtro_diametro, #filtro_estado').on('keyup change', function() {
            table.ajax.reload(null, false);
        });
    
        // Actualizar el contador de diámetros
        table.on('xhr', function () {
            var json = table.ajax.json();
            var totalDiametros = json.recordsTotal; // Verifica si 'recordsTotal' está en la respuesta
            $('#totalCantPiezas').text(totalDiametros);
        });
    
        // Funcionalidad para limpiar filtros
        $('#clearFilters').click(function() {
            $('.filtro-select, .filtro-texto').val('');
            table.ajax.reload();
        });
    });
    </script>
    @stop
    
