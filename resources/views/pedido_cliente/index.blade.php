{{-- resources\views\pedido_cliente\index.blade.php --}}
@extends('adminlte::page')
@section('title', 'Programación de la Producción - Pedido del Cliente')

@section('content_header')
<x-header-card 
    title="Programación de la Producción - Pedido del Cliente" 
    quantityTitle="Cantidad de piezas solicitadas:" 
    buttonRoute="{{ route('pedido_cliente.create') }}" 
    buttonText="Crear registro" 
/>
@stop




@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <table id="pedido_cliente" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Nro_OF</th>
                            <th style="text-align: center;">Producto</th>
                            <th style="text-align: center;">Descripción</th>
                            <th style="text-align: center;">Nombre_Categoria</th>
                            <th style="text-align: center;">Fecha_del_Pedido</th>
                            <th style="text-align: center;">Cant_Fabricacion</th>
                            <th style="text-align: center;">Planificación</th>
                            <th>Creado</th>
                            <th>Por</th>
                            <th>Actualizado</th>
                            <th>Por</th>
                            <th style="text-align: center;">Estado</th>
                            <th style="text-align: center;">Acciones</th>

                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_nro_of" placeholder="Filtrar Nro_OF" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_producto" placeholder="Filtrar Producto" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_descripción" placeholder="Filtrar descripción" class="form-control filtro-texto" /></th>
                            <th><select id="filtro_nombre_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_fecha_pedido" placeholder="Filtrar Fecha_del_Pedido" class="form-control filtro-texto" /></th>
                            <th><input type="text" id="filtro_cant_fabricacion" placeholder="Filtrar Cant_Fabricacion" class="form-control filtro-texto" /></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">



<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_index.css') }}">
@endsection

@section('js')
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- Responsive Extension -->
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<!-- Buttons Extension -->
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
function deletePedido(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Este pedido se eliminará permanentemente si no tiene piezas fabricadas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/pedido_cliente/${id}`;

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(method);
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

 

@if(session('success'))
<script>
    Swal.fire({
        title: '¡Éxito!',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        title: 'Error',
        text: '{{ session('error') }}',
        icon: 'error',
        confirmButtonText: 'Entendido',
        timer: 4000,
        timerProgressBar: true
    });
@endif

</script>  


<script>
$(document).ready(function() {
    var table = $('#pedido_cliente').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true, // Optimiza la carga inicial
    searching: false,  // Deshabilita la búsqueda instantánea
    ajax: {
        url: "{{ route('pedido_cliente.data') }}",
        type: 'GET',
        cache: false,              // ← evita cache de la request
        //headers: { 'Cache-Control': 'no-cache' }
        data: function (d) {
            d.filtro_nro_of = $('#filtro_nro_of').val();
            d.filtro_producto = $('#filtro_producto').val();
            d.filtro_descripción = $('#filtro_descripción').val();
            d.filtro_nombre_categoria = $('#filtro_nombre_categoria').val();
            d.filtro_fecha_pedido = $('#filtro_fecha_pedido').val();
            d.filtro_cant_fabricacion = $('#filtro_cant_fabricacion').val();
            d.filtro_estado = $('#filtro_estado').val();
        }
    },
    columns: [
    { data: 'Nro_OF', name: 'Nro_OF' },
    { data: 'Producto_Nombre', name: 'Producto_Nombre' },
    { data: 'Descripción', name: 'Descripción' },
    { data: 'Nombre_Categoria', name: 'Nombre_Categoria' },
    { data: 'Fecha_del_Pedido', name: 'Fecha_del_Pedido' },
    { data: 'Cant_Fabricacion', name: 'Cant_Fabricacion' },
    // Mostrar badge de planificación (HTML)
    { data: 'created_at', name: 'created_at' },
    { data: 'creator', name: 'creator' },
    { data: 'updated_at', name: 'updated_at' },
    { data: 'updater', name: 'updater' },

    {
        data: 'Id_OF',
        className: 'acciones',
        orderable: false,
        searchable: false,
        render: function (data, type, row, meta) {
    let eliminarBtn = row.Estado.includes('Fabricado')
        ? `<button class="btn btn-secondary btn-sm" disabled>🔒</button>`
        : `<button onclick="deletePedido(${data})" class="btn btn-danger btn-sm">Eliminar</button>`;

    return `
        <a href="/pedido_cliente/${data}" class="btn btn-info btn-sm">Ver</a>
        <a href="/pedido_cliente/${data}/edit" class="btn btn-primary btn-sm">Editar</a>
        ${eliminarBtn}
    `;
}

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



    // Llenar los selects con valores únicos de las columnas especificadas
    table.on('xhr', function () {
        var json = table.ajax.json();
        var uniqueValues = {
            'Nombre_Categoria': new Set()
        };

        var totalCantPiezas = 0;

        $.each(json.data, function (index, item) {

            uniqueValues['Nombre_Categoria'].add(item.Nombre_Categoria);

            totalCantPiezas += parseFloat(item.Cant_Fabricacion);
        });

        $('#totalCantPiezas').text(totalCantPiezas.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));

        // Llenar los selectores con los valores únicos

        fillSelect('#filtro_nombre_categoria', uniqueValues['Nombre_Categoria']);
    });

    function fillSelect(selector, data) {
        var select = $(selector);
        select.empty();
        select.append('<option value="">Todos</option>');
        data.forEach(function (value) {
            select.append('<option value="' + value + '">' + value + '</option>');
        });
    }

    // Recargar la tabla al cambiar los selectores y campos de texto
    $('.filtro-select, .filtro-texto').on('change keyup', function () {
        table.ajax.reload(null, false); // El segundo parámetro asegura que la tabla no se resetee
    });

    // Funcionalidad para limpiar filtros
    $('#clearFilters').click(function() {
        $('.filtro-select').val('');
        $('.filtro-texto').val('');
        table.ajax.reload();
    });
});
</script>
@stop
