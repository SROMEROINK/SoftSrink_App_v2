@extends('adminlte::page')

@section('title', 'Pedido del Cliente - Vista Simple')

@section('content_header')
<x-header-card
    title="Pedido del Cliente - Vista Simple"
    buttonRoute="{{ route('pedido_cliente.index') }}"
    buttonText="Volver a DataTables"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="alert alert-warning mt-3" role="alert">
        Vista de diagnostico sin DataTables. Si esta tabla renderiza bien, el problema probablemente esta en el plugin o en la respuesta JSON usada por la grilla original.
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Pedidos cargados: {{ number_format($pedidos->count(), 0, ',', '.') }}</h5>
                <a href="{{ route('pedido_cliente.create') }}" class="btn btn-success">Crear Pedido</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm w-100">
                    <thead>
                        <tr>
                            <th>Nro OF</th>
                            <th>Producto</th>
                            <th>Descripcion</th>
                            <th>Categoria</th>
                            <th>Subcategoria</th>
                            <th>Fecha del Pedido</th>
                            <th>Cant. Fabricacion</th>
                            <th>Estado Pedido</th>
                            <th>Estado MP</th>
                            <th>Estado Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                            <tr>
                                <td>{{ $pedido->Nro_OF ?? '-' }}</td>
                                <td>{{ $pedido->producto->Prod_Codigo ?? '-' }}</td>
                                <td>{{ $pedido->producto->Prod_Descripcion ?? '-' }}</td>
                                <td>{{ $pedido->producto->categoria->Nombre_Categoria ?? '-' }}</td>
                                <td>{{ $pedido->producto->subCategoria->Nombre_SubCategoria ?? '-' }}</td>
                                <td>{{ optional($pedido->Fecha_del_Pedido)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ number_format((int) $pedido->Cant_Fabricacion, 0, ',', '.') }}</td>
                                <td>{{ $pedido->estadoPlanificacion->Nombre_Estado ?? '-' }}</td>
                                <td>{{ $pedido->definicionMp?->estadoPlanificacion?->Nombre_Estado ?? 'SIN DEFINIR MP' }}</td>
                                <td>{{ (int) $pedido->reg_Status === 1 ? 'Activo' : 'Inactivo' }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('pedido_cliente.show', $pedido->Id_OF) }}" class="btn btn-info btn-sm">Ver</a>
                                    <a href="{{ route('pedido_cliente.edit', $pedido->Id_OF) }}" class="btn btn-primary btn-sm">Editar</a>
                                    @if($pedido->definicionMp?->Id_Pedido_MP)
                                        <a href="{{ route('pedido_cliente_mp.editMassive', $pedido->definicionMp->Id_Pedido_MP) }}" class="btn btn-success btn-sm">Editar MP</a>
                                    @else
                                        <a href="{{ route('pedido_cliente_mp.createMassive', ['of' => $pedido->Id_OF]) }}" class="btn btn-success btn-sm">Definir MP</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No hay pedidos para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
@stop
