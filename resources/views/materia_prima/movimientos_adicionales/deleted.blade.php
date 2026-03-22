@extends('adminlte::page')

@section('title', 'Movimientos Adicionales Eliminados')

@section('content_header')
    <h1>Movimientos Adicionales Eliminados</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Ingreso</th>
                        <th>OF</th>
                        <th>Producto</th>
                        <th>Maquina</th>
                        <th>Metros Netos</th>
                        <th>Eliminado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientosEliminados as $movimiento)
                        <tr>
                            <td>{{ $movimiento->Id_Movimiento_MP }}</td>
                            <td>{{ optional($movimiento->Fecha_Movimiento)->format('d/m/Y') }}</td>
                            <td>{{ $movimiento->Nro_Ingreso_MP }}</td>
                            <td>{{ $movimiento->Nro_OF }}</td>
                            <td>{{ $movimiento->Codigo_Producto }}</td>
                            <td>{{ $movimiento->Nro_Maquina }}</td>
                            <td>{{ number_format((float) $movimiento->Total_Mtros_Movimiento, 2, ',', '.') }}</td>
                            <td>{{ optional($movimiento->deleted_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <form action="{{ route('mp_movimientos_adicionales.restore', $movimiento->Id_Movimiento_MP) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Restaurar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No hay movimientos eliminados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
