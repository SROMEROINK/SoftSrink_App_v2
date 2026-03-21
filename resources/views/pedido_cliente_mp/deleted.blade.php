@extends('adminlte::page')

@section('title', 'Definiciones de MP Eliminadas')

@section('content_header')
<x-header-card
    title="Definiciones de MP Eliminadas"
    buttonRoute="{{ route('pedido_cliente_mp.index') }}"
    buttonText="Volver al listado"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tabla_eliminados_pedido_mp">
                    <thead>
                        <tr>
                            <th>Nro OF</th>
                            <th>Producto</th>
                            <th>Estado MP</th>
                            <th>Codigo MP</th>
                            <th>Materia Prima</th>
                            <th>Eliminado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eliminados as $item)
                            <tr>
                                <td>{{ $item->pedido->Nro_OF ?? '-' }}</td>
                                <td>{{ $item->pedido->producto->Prod_Codigo ?? '-' }}</td>
                                <td>{{ $item->estadoPlanificacion->Nombre_Estado ?? '-' }}</td>
                                <td>{{ $item->Codigo_MP ?: '-' }}</td>
                                <td>{{ $item->Materia_Prima ?: '-' }}</td>
                                <td>{{ optional($item->deleted_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm" onclick="restorePedidoMp({{ $item->Id_Pedido_MP }})">Restaurar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_mp_index.css') }}">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script>
        function restorePedidoMp(id) {
            SwalUtils.confirmRestore('La definicion de materia prima volvera al listado principal.').then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/pedido_cliente_mp/${id}/restore`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        SwalUtils.restored('Definicion restaurada correctamente.').then(() => {
                            window.location.href = '{{ route('pedido_cliente_mp.index') }}';
                        });
                    },
                    error: function () {
                        SwalUtils.error('No se pudo restaurar la definicion de materia prima.');
                    }
                });
            });
        }
    </script>
@stop


