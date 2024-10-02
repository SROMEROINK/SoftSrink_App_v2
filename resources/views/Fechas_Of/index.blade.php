@extends('adminlte::page')

@section('title', 'Fechas de Producción - Visualización')

@section('content_header')
    <x-header-card 
        title="Fechas de Producción" 
        quantityTitle="Cantidad total de registros:" 
        buttonRoute="{{ route('fechas_of.create') }}" 
        buttonText="Crear registro" 
    />
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table id="fechas_of_table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nro_OF_fechas</th>
                            <th>Prod_Codigo</th>
                            <th>Prod_Descripcion</th>
                            <th>Nombre_Categoria</th>
                            <th>Nro_Maquina</th>
                            <th>Familia_Maquinas</th>
                            <th>Nro_Programa_H1</th>
                            <th>Nro_Programa_H2</th>
                            <th>Inicio_PAP</th>
                            <th>Hora_Inicio_PAP</th>
                            <th>Fin_PAP</th>
                            <th>Hora_Fin_PAP</th>
                            <th>Inicio_OF</th>
                            <th>Finalizacion_OF</th>
                            <th>Tiempo_Pieza</th>
                        </tr>
                        <!-- Filtros personalizados -->
                        <tr class="filter-row">
                            <th><input type="text" id="filtro_nro_of_fechas" class="form-control filtro-texto" placeholder="Filtrar Nro_OF"></th>
                            <th><input type="text" id="filtro_prod_codigo" class="form-control filtro-texto" placeholder="Filtrar Prod_Codigo"></th>
                            <th><input type="text" id="filtro_prod_descripcion" class="form-control filtro-texto" placeholder="Filtrar Prod_Descripcion"></th>
                            <th><select id="filtro_categoria" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_maquina" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><select id="filtro_familia" class="form-control filtro-select"><option value="">Todos</option></select></th>
                            <th><input type="text" id="filtro_nro_programa_h1" class="form-control filtro-texto" placeholder="Filtrar Nro Programa H1"></th>
                            <th><input type="text" id="filtro_nro_programa_h2" class="form-control filtro-texto" placeholder="Filtrar Nro Programa H2"></th>
                            <th><input type="text" id="filtro_inicio_pap" class="form-control filtro-texto" placeholder="Filtrar Inicio PAP"></th>
                            <th><input type="text" id="filtro_hora_inicio_pap" class="form-control filtro-texto" placeholder="Filtrar Hora Inicio PAP"></th>
                            <th><input type="text" id="filtro_fin_pap" class="form-control filtro-texto" placeholder="Filtrar Fin PAP"></th>
                            <th><input type="text" id="filtro_hora_fin_pap" class="form-control filtro-texto" placeholder="Filtrar Hora Fin PAP"></th>
                            <th><input type="text" id="filtro_inicio_of" class="form-control filtro-texto" placeholder="Filtrar Inicio OF"></th>
                            <th><input type="text" id="filtro_finalizacion_of" class="form-control filtro-texto" placeholder="Filtrar Finalización OF"></th>
                            <th><input type="text" id="filtro_tiempo_pieza" class="form-control filtro-texto" placeholder="Filtrar Tiempo Pieza"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/fechas_of_index.css') }}">
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script>
$(document).ready(function () {
    var table = $('#fechas_of_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('fechas_of.data') }}",
            type: 'GET',
            data: function (d) {
                d.filtro_nro_of_fechas = $('#filtro_nro_of_fechas').val();
                d.filtro_prod_codigo = $('#filtro_prod_codigo').val();
                d.filtro_prod_descripcion = $('#filtro_prod_descripcion').val();
                d.filtro_categoria = $('#filtro_categoria').val();
                d.filtro_maquina = $('#filtro_maquina').val();
                d.filtro_familia = $('#filtro_familia').val();
                d.filtro_nro_programa_h1 = $('#filtro_nro_programa_h1').val();
                d.filtro_nro_programa_h2 = $('#filtro_nro_programa_h2').val();
                d.filtro_inicio_pap = $('#filtro_inicio_pap').val();
                d.filtro_hora_inicio_pap = $('#filtro_hora_inicio_pap').val();
                d.filtro_fin_pap = $('#filtro_fin_pap').val();
                d.filtro_hora_fin_pap = $('#filtro_hora_fin_pap').val();
                d.filtro_inicio_of = $('#filtro_inicio_of').val();
                d.filtro_finalizacion_of = $('#filtro_finalizacion_of').val();
                d.filtro_tiempo_pieza = $('#filtro_tiempo_pieza').val();
            }
        },
        columns: [
            { data: 'Nro_OF_fechas' },
            { data: 'Prod_Codigo' },
            { data: 'Prod_Descripcion' },
            { data: 'Nombre_Categoria' },
            { data: 'Nro_Maquina' },
            { data: 'Familia_Maquinas' },
            { data: 'Nro_Programa_H1' },
            { data: 'Nro_Programa_H2' },
            { data: 'Inicio_PAP' },
            { data: 'Hora_Inicio_PAP' },
            { data: 'Fin_PAP' },
            { data: 'Hora_Fin_PAP' },
            { data: 'Inicio_OF' },
            { data: 'Finalizacion_OF' },
            { data: 'Tiempo_Pieza' }
        ],
        scrollX: true,
        scrollY: '60vh',
        scrollCollapse: true,
        paging: true,
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            lengthMenu: "Mostrar _MENU_ registros por página",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando página _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });

    // Agregar opciones únicas a los filtros dinámicos
    table.on('xhr', function () {
        var json = table.ajax.json();
        var categorias = new Set();
        var maquinas = new Set();
        var familias = new Set();

        $.each(json.data, function (index, item) {
            categorias.add(item.Nombre_Categoria);
            maquinas.add(item.Nro_Maquina);
            familias.add(item.Familia_Maquinas);
        });

        rellenarSelect('#filtro_categoria', categorias);
        rellenarSelect('#filtro_maquina', maquinas);
        rellenarSelect('#filtro_familia', familias);
    });

    // Función para rellenar los selectores de filtro
    function rellenarSelect(selector, data) {
        var select = $(selector);
        select.empty();
        select.append('<option value="">Todos</option>');
        data.forEach(function (value) {
            select.append('<option value="' + value + '">' + value + '</option>');
        });
    }

    // Filtros personalizados
    $('.filtro-texto, .filtro-select').on('keyup change', function () {
        table.ajax.reload();
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
