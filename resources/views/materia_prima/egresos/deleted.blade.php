@extends('adminlte::page')

@section('title', 'Salidas de MP Eliminadas')

@section('content_header')
    <h1>Salidas de Materia Prima Eliminadas</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nro OF</th>
                        <th>Producto</th>
                        <th>Ingreso MP</th>
                        <th>Total salidas</th>
                        <th>Total metros</th>
                        <th>Eliminado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($egresosEliminados as $egreso)
                        <tr>
                            <td>{{ $egreso->pedidoMp->pedido->Nro_OF ?? $egreso->Id_OF_Salidas_MP }}</td>
                            <td>{{ $egreso->pedidoMp->pedido->producto->Prod_Codigo ?? '-' }}</td>
                            <td>{{ $egreso->pedidoMp->Nro_Ingreso_MP ?? '-' }}</td>
                            <td>{{ number_format((int) $egreso->Total_Salidas_MP, 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $egreso->Total_Mtros_Utilizados, 2, ',', '.') }}</td>
                            <td>{{ optional($egreso->deleted_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                <form action="{{ route('mp_egresos.restore', $egreso->Id_Egresos_MP) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Restaurar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No hay salidas eliminadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('mp_egresos.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</div>
@stop
