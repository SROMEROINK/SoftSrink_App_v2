{{-- resources\views\proveedores\index.blade.php --}}

@extends('adminlte::page')

@section('title', 'Listado de Proveedores')

@section('content_header')
<x-header-card 
    title="Listado de Proveedores" 
    quantityTitle="Cantidad de Proveedores:" 
    quantity="{{ $totalProveedores }}" 
    buttonRoute="{{ route('proveedores.create') }}" 
    buttonText="Crear Proveedor" 
    :deletedRouteUrl="$deletedRouteUrl" 
    deletedButtonText="Ver Proveedores Eliminados" 
/>
@stop




@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-bordered" id="proveedores-table">
            <thead>
                <tr>
                    <th>Id_Proveedor</th>
                    <th><input type="text" id="filtro_nombre" placeholder="Filtrar por Nombre" class="form-control filtro-texto"></th>
                    <th><input type="text" id="filtro_detalle" placeholder="Filtrar por Detalle" class="form-control filtro-texto"></th>
                    <th>
                        <select id="filtro_proveedor_mp" class="form-control filtro-select">
                            <option value="">Todos</option>
                            <option value="1">proveedor_mp</option>
                            <option value="0">''</option>
                        </select>
                    </th>
                    <th>
                        <select id="filtro_proveedor_herramientas" class="form-control filtro-select">
                            <option value="">Todos</option>
                            <option value="1">proveedor_herramientas</option>
                            <option value="0">''</option>
                        </select>
                    </th>
                    <th>Nombre Contacto</th>
                    <th>Nro Telefono</th>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/proveedores_index.css') }}">
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


// Defina deleteProveedor fuera del $(document).ready() para que sea accesible globalmente.

    function deleteProveedor(Prov_Id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/proveedores/${Prov_Id}`, // Asegúrate de que esta es la URL correcta
                type: 'DELETE', // Método HTTP para la eliminación
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: function(response) {
                    Swal.fire(
                        'Eliminado!',
                        'El proveedor ha sido eliminado.',
                        'success'
                    );
                    $('#proveedores-table').DataTable().ajax.reload();
                },
                error: function(response) {
                    Swal.fire(
                        'Error!',
                        'No se pudo eliminar el proveedor.',
                        'error'
                    );
                }
            });
        }
    });
}


$(document).ready(function() {
    var table = $('#proveedores-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: {
            url: "{{ route('proveedores.data') }}",
            data: function (d) {
                d.filtro_nombre = $('#filtro_nombre').val();
                d.filtro_detalle = $('#filtro_detalle').val();
                d.filtro_proveedor_mp = $('#filtro_proveedor_mp').val();
                d.filtro_proveedor_herramientas = $('#filtro_proveedor_herramientas').val();
                d.filtro_estado = $('#filtro_estado').val();// Nuevo filtro de estado
            }
        },
        columns: [
            { data: 'Prov_Id', name: 'Prov_Id' },
            { data: 'Prov_Nombre', name: 'Prov_Nombre' },
            { data: 'Prov_Detalle', name: 'Prov_Detalle' },
            { 
                data: 'Es_Proveedor_MP', 
                render: function (data) {
                    return data === 1 ? 'proveedor_mp' : '';
                },
                name: 'Es_Proveedor_MP' 
            },
            { 
                data: 'Es_Proveedor_Herramientas', 
                render: function (data) {
                    return data === 1 ? 'proveedor_herramientas' : '';
                },
                name: 'Es_Proveedor_Herramientas' 
            },
            { data: 'Nombre_Contacto', name: 'Nombre_Contacto' },
            { data: 'Nro_Telefono', name: 'Nro_Telefono' },
            {
                data: 'reg_Status',
                render: function (data, type, row) {
                    return data === 1 ? 'Activo' : 'Inactivo';
                },
                name: 'reg_Status'
            },
            {
                data: 'Prov_Id',
                render: function(data) {
                    return `
                        <a href="/proveedores/${data}/edit" class="btn btn-xs btn-primary">Editar</a>
                        <button onclick="deleteProveedor(${data})" class="btn btn-xs btn-danger">Eliminar</button>
                    `;
                },
                orderable: false,
                searchable: false
            }
        ],
        scrollX: true,
        scrollY: '60vh',
        scrollCollapse: true,
        paging: true,
        fixedHeader: true,
        responsive: true,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            url: "{{ asset('Spanish.json') }}"
        }
    });

    table.on('xhr', function () {
        var json = table.ajax.json();
        var totalProveedores = json.recordsTotal; // Asume que la respuesta incluye 'recordsTotal' que contiene el total de proveedores
        $('#totalCantPiezas').text(totalProveedores);
    });

    // Recargar la tabla al cambiar los filtros
    $('#filtro_nombre, #filtro_detalle, #filtro_proveedor_mp, #filtro_proveedor_herramientas, #filtro_estado').on('keyup change', function() {
        table.ajax.reload(null, false); // false para mantener la paginación actual
    });

    // Funcionalidad para limpiar filtros
    $('#clearFilters').click(function() {
        $('.filtro-select, .filtro-texto').val('');
        table.ajax.reload();
    });
});
</script>
@stop
