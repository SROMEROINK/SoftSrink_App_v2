@extends('adminlte::page')

@section('title', 'Editar Pedido MP Interno por Grupo')

@section('content_header')
    <div class="show-header">
        <h1>Editar Pedido MP Interno por Grupo</h1>
        <p>Corrección masiva del correlativo interno de pedidos de materia prima.</p>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/datatables.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/show-details.css') }}">
<style>
    .pedido-mp-group-table th,
    .pedido-mp-group-table td {
        text-align: center;
        vertical-align: middle;
    }

    .pedido-mp-group-table input.form-control {
        text-align: center;
    }
</style>
@stop

@section('content')
    @include('components.swal-session')
<div class="container-fluid">

    <div class="alert alert-info mt-3" role="alert">
        Estás editando el grupo del <strong>Pedido MP Interno {{ $pedidoMaterial }}</strong>.
        Aquí puedes corregir el correlativo por fila sin perder el contexto del pedido.
    </div>

    <div class="d-flex justify-content-end mt-3">
        <a href="{{ $addOfUrl }}" class="btn btn-success">Agregar OF a este grupo</a>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-4">
                    <label for="aplicar_todas" class="form-label">Aplicar mismo Pedido MP Interno a todas las filas</label>
                    <input type="text" id="aplicar_todas" class="form-control" placeholder="Ej: 500">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-primary" id="btn_aplicar_todas">Aplicar a todas</button>
                </div>
                <div class="col-md-6 text-md-right mt-2 mt-md-0">
                    <span class="text-muted">Registros cargados: {{ $registros->count() }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('pedido_cliente_mp.updateGroup') }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered table-striped w-100 pedido-mp-group-table">
                        <thead>
                            <tr>
                                <th>Nro OF</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Nro Ingreso MP</th>
                                <th>Código MP</th>
                                <th>Cant. Barras MP</th>
                                <th>Pedido MP Interno</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registros as $registro)
                                <tr>
                                    <td>
                                        {{ $registro->pedido->Nro_OF ?? '-' }}
                                        <input type="hidden" name="ids[]" value="{{ $registro->Id_Pedido_MP }}">
                                    </td>
                                    <td>{{ $registro->pedido->producto->Prod_Codigo ?? '-' }}</td>
                                    <td>{{ $registro->pedido->producto->categoria->Nombre_Categoria ?? '-' }}</td>
                                    <td>{{ $registro->Nro_Ingreso_MP ?? '-' }}</td>
                                    <td>{{ $registro->Codigo_MP ?? '-' }}</td>
                                    <td>{{ $registro->Cant_Barras_MP ?? '-' }}</td>
                                    <td>
                                        <input type="text"
                                               name="pedido_material_nro[]"
                                               class="form-control pedido-material-group-input"
                                               value="{{ $registro->Pedido_Material_Nro }}"
                                               required>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Guardar cambios del grupo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const applyAllInput = document.getElementById('aplicar_todas');
    const applyAllButton = document.getElementById('btn_aplicar_todas');

    applyAllButton.addEventListener('click', function () {
        const value = String(applyAllInput.value || '').trim();
        if (!value) {
            return;
        }

        document.querySelectorAll('.pedido-material-group-input').forEach(function (input) {
            input.value = value;
        });
    });
});
</script>
@stop


