@extends('adminlte::page')

@section('title', 'Lista de Productos')

@section('content_header')
<div class="card card-title-header">
    <h4 class="text-center">Listado de Productos:</h4>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table id="listado_productos" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Clasificación Piezas</th>
                            <th>Familia</th>
                            <th>Sub-Familia</th>
                            <th>Grupo-Sub-Familia</th>
                            <th>Código Conjunto</th>
                            <th>Cliente</th>
                            <th>Nº Plano</th>
                            <th>Ult. Revisión Plano</th>
                            <th>Material MP</th>
                            <th>Diámetro MP</th>
                            <th>Código MP</th>
                            <th>Longitud de Pieza</th>
                            <th>Prod Longitud Total</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th><input type="text" placeholder="Filtrar ID" /></th>
                            <th><input type="text" placeholder="Filtrar Código" /></th>
                            <th><input type="text" placeholder="Filtrar Descripción" /></th>
                            <th><input type="text" placeholder="Filtrar Clasificación" /></th>
                            <th><input type="text" placeholder="Filtrar Familia" /></th>
                            <th><input type="text" placeholder="Filtrar Sub-Familia" /></th>
                            <th><input type="text" placeholder="Filtrar Grupo Sub-Familia" /></th>
                            <th><input type="text" placeholder="Filtrar Código Conjunto" /></th>
                            <th><input type="text" placeholder="Filtrar Cliente" /></th>
                            <th><input type="text" placeholder="Filtrar Nº Plano" /></th>
                            <th><input type="text" placeholder="Filtrar Ult. Revisión Plano" /></th>
                            <th><input type="text" placeholder="Filtrar Material MP" /></th>
                            <th><input type="text" placeholder="Filtrar Diámetro MP" /></th>
                            <th><input type="text" placeholder="Filtrar Código MP" /></th>
                            <th><input type="text" placeholder="Filtrar Longitud de Pieza" /></th>
                            <th><input type="text" placeholder="Filtrar Prod Longitud Total" /></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/Productos_Index.css') }}">
@stop

@section('js')
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
    $('#listado_productos tfoot th').each(function () {
        var title = $(this).text();
        $(this).html('<input type="text" placeholder="Filtrar.." />');
    });

    var table = $('#listado_productos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('productos.data') }}",
            data: function (d) {
                $('#listado_productos tfoot input').each(function() {
                    var columnIndex = $(this).parent().index();
                    d.columns[columnIndex].search.value = this.value;
                });
            }
        },
        columns: [
            { data: 'Id_Producto', name: 'Id_Producto' },
            { data: 'Prod_Codigo', name: 'Prod_Codigo' },
            { data: 'Prod_Descripcion', name: 'Prod_Descripcion' },
            { data: 'Nombre_Clasificacion', name: 'Nombre_Clasificacion' },
            { data: 'Nombre_Categoria', name: 'Nombre_Categoria' },
            { data: 'Nombre_SubCategoria', name: 'Nombre_SubCategoria' },
            { data: 'Nombre_GrupoSubCategoria', name: 'Nombre_GrupoSubCategoria' },
            { data: 'Nombre_GrupoConjuntos', name: 'Nombre_GrupoConjuntos' },
            { data: 'Cli_Nombre', name: 'Cli_Nombre' },
            { data: 'Prod_N_Plano', name: 'Prod_N_Plano' },
            { data: 'Prod_Plano_Ultima_Revisión', name: 'Prod_Plano_Ultima_Revisión' },
            { data: 'Prod_Material_MP', name: 'Prod_Material_MP' },
            { data: 'Prod_Diametro_de_MP', name: 'Prod_Diametro_de_MP' },
            { data: 'Prod_Codigo_MP', name: 'Prod_Codigo_MP' },
            { data: 'Prod_Longitud_de_Pieza', name: 'Prod_Longitud_de_Pieza' },
            { data: 'Prod_Longitug_Total', name: 'Prod_Longitug_Total' },
        ],
        scrollY: '50vh',
        scrollCollapse: true,
        paging: true,
        fixedHeader: true,
        responsive: true,
        orderCellsTop: true,
        pageLength: 50,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        language: {
            url: "{{ asset('Spanish.json') }}"
        },
        initComplete: function () {
            this.api().columns().every(function () {
                var that = this;
                $('input', this.footer()).on('keyup change clear', function () {
                    if (that.search() !== this.value) {
                        that.search(this.value).draw();
                    }
                });
            });
        }
    });

    // Script for handling delete button click
    $(document).on('click', '.trigger-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Add your deletion logic here
                Swal.fire(
                    'Eliminado!',
                    'El registro ha sido eliminado.',
                    'success'
                )
            }
        })
    });
});
</script>
@stop
