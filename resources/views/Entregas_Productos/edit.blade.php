@extends('adminlte::page')

@section('title', 'Editar Entrega de Producto')

@section('content_header')
    <h1>Editar Entrega de Producto</h1>
@stop

@section('content')
    @include('components.swal-session')

    <form method="POST"
          action="{{ route('entregas_productos.update', $entrega->Id_List_Entreg_Prod) }}"
          id="form-editar-entrega-producto"
          data-ajax="true"
          data-edit-check="true"
          data-exclude-fields="_token,_method">
        @csrf
        @method('PUT')

        @include('entregas_productos.partials.form', ['detalleOf' => $detalleOf ?? null])

        <div class="card mt-3">
            <div class="card-body text-right">
                <a href="{{ route('entregas_productos.show', $entrega->Id_List_Entreg_Prod) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar entrega</button>
            </div>
        </div>
    </form>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/entregas_productos_form.css') }}">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
<script>
(function () {
    const endpointTemplate = @json(route('entregas_productos.ofData', ['nroOf' => '__OF__']));
    const entregaId = @json($entrega->Id_List_Entreg_Prod);

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('es-AR');
    }

    function formatDate(value) {
        if (!value) return '-';
        const parts = String(value).split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : value;
    }

    function clearDetails() {
        $('[data-detail]').text('-');
        $('#badge-of-estado').text('Sin OF seleccionada');
    }

    function paintDetails(data) {
        if (!data) {
            clearDetails();
            return;
        }

        $('[data-detail="Prod_Codigo"]').text(data.Prod_Codigo || '-');
        $('[data-detail="Prod_Descripcion"]').text(data.Prod_Descripcion || '-');
        $('[data-detail="Nombre_Categoria"]').text(data.Nombre_Categoria || '-');
        $('[data-detail="Fecha_del_Pedido"]').text(formatDate(data.Fecha_del_Pedido));
        $('[data-detail="Nro_Maquina"]').text(data.Nro_Maquina || '-');
        $('[data-detail="Familia_Maquinas"]').text(data.Familia_Maquinas || '-');
        $('[data-detail="Nro_Ingreso_MP"]').text(data.Nro_Ingreso_MP || '-');
        $('[data-detail="Codigo_MP"]').text(data.Codigo_MP || '-');
        $('[data-detail="Nro_Certificado_MP"]').text(data.Nro_Certificado_MP || '-');
        $('[data-detail="Prov_Nombre"]').text(data.Prov_Nombre || '-');
        $('[data-detail="Pedido_de_MP"]').text(data.Pedido_de_MP || '-');
        $('[data-detail="Cant_Fabricacion"]').text(formatNumber(data.Cant_Fabricacion));
        $('[data-detail="Piezas_Fabricadas"]').text(formatNumber(data.Piezas_Fabricadas));
        $('[data-detail="Total_Entregado"]').text(formatNumber(data.Total_Entregado));
        $('[data-detail="Saldo_Entrega"]').text(formatNumber(data.Saldo_Entrega));
        $('[data-detail="Ultima_Fecha_Fabricacion"]').text(formatDate(data.Ultima_Fecha_Fabricacion));
        $('#badge-of-estado').text(data.Estado_Planificacion || 'Sin estado');
    }

    function fetchOfData(showAlert) {
        const nroOf = ($('#Id_OF').val() || '').trim();
        if (!nroOf) {
            clearDetails();
            return;
        }

        $.get(endpointTemplate.replace('__OF__', encodeURIComponent(nroOf)), {
            exclude_entrega_id: entregaId
        })
            .done(function (response) {
                paintDetails(response.data || null);
            })
            .fail(function (xhr) {
                clearDetails();
                if (showAlert !== false) {
                    SwalUtils.error(xhr.responseJSON?.message || 'No se pudo cargar la OF seleccionada.');
                }
            });
    }

    $(document).ready(function () {
        paintDetails(@json($detalleOf));
        $('#Id_OF').on('change blur', function () {
            fetchOfData(true);
        });
    });
})();
</script>
@stop
