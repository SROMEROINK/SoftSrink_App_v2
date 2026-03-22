@extends('adminlte::page')

@section('title', 'Salidas Iniciales Eliminadas')

@section('content_header')
    <h1 class="text-center">Salidas Iniciales Eliminadas</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nro Ingreso MP</th>
                            <th>Codigo MP</th>
                            <th>Materia Prima</th>
                            <th>Preparadas</th>
                            <th>Total Salidas</th>
                            <th>Eliminado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salidasEliminadas as $salida)
                            <tr>
                                <td>{{ $salida->ingresoMp->Nro_Ingreso_MP ?? '-' }}</td>
                                <td>{{ $salida->ingresoMp->Codigo_MP ?? '-' }}</td>
                                <td>{{ $salida->ingresoMp->materiaPrima->Nombre_Materia ?? '-' }}</td>
                                <td>{{ number_format((int) $salida->Cantidad_Unidades_MP_Preparadas, 0, ',', '.') }}</td>
                                <td>{{ number_format((int) $salida->Total_Salidas_MP, 0, ',', '.') }}</td>
                                <td>{{ optional($salida->deleted_at)->format('d/m/Y H:i:s') ?? '-' }}</td>
                                <td>
                                    <form action="{{ route('mp_salidas_iniciales.restore', $salida->Id_Ingreso_MP) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Restaurar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay salidas iniciales eliminadas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-right">
                <a href="{{ route('mp_salidas_iniciales.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>
@stop
