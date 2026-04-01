@php
    $metricClass = function ($value, $mode = 'number') {
        $number = (float) ($value ?? 0);
        if ($mode === 'percentage') {
            if ($number == 0.0) return 'metric-neutral';
            return $number > 100 ? 'metric-danger' : 'metric-success';
        }
        if ($number > 0) return 'metric-success';
        if ($number < 0) return 'metric-danger';
        return 'metric-neutral';
    };
    $displayPercent = function ($value) {
        $number = round((float) ($value ?? 0), 2);
        return $number > 100 ? round($number - 100, 2) : $number;
    };
    $percentSuffix = function ($value) {
        return (float) ($value ?? 0) > 100 ? ' de mas' : '';
    };
    $controlClass = function ($text) {
        $text = (string) $text;
        if (str_contains($text, 'Entregado de mas')) return 'metric-danger';
        if (str_contains($text, 'Restan entregar')) return 'metric-warning';
        if (str_contains($text, 'Entrega completa')) return 'metric-success';
        return 'metric-neutral';
    };
    $controlShort = function ($text) {
        $text = (string) $text;
        if (str_contains($text, 'Sin fabricacion ni entregas')) return 'Sin mov.';
        if (str_contains($text, 'Restan entregar')) return str_replace('Restan entregar: ', 'Restan: ', $text);
        if (str_contains($text, 'Entregado de mas')) return str_replace('Entregado de mas: ', 'De mas: ', $text);
        return $text;
    };
@endphp
<div class="top-horizontal-scroll" id="listado_of_top_scroll"><div></div></div>
<div class="plain-table-wrap" id="listado_of_table_wrap">
    <table id="tabla_listado_of_plain" class="table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>Nro OF</th><th>Planificacion</th><th>Producto</th><th>Descripcion</th><th>Categoria</th><th>Rev. Plano 1</th><th>Rev. Plano 2</th><th>Fecha Pedido</th><th>Nro Maquina</th><th>Familia Maquina</th><th>Nro Ingreso MP</th><th>Codigo MP</th><th>Certificado MP</th><th>Pedido MP</th><th>Proveedor</th><th>Cant. Fabricacion</th><th>Piezas Fabricadas</th><th>% Fabricado OF</th><th>Piezas Entregadas</th><th>Ult. Entrega</th><th>% Entregado OF</th><th>Saldo Entrega</th><th>Control Entrega</th><th>Saldo Fabricacion</th><th>Ult. Fabricacion</th><th>Acciones</th>
            </tr>
            <tr class="filter-row">
                <th><input type="text" name="filtro_nro_of" value="{{ request('filtro_nro_of') }}" class="form-control filtro-texto" placeholder="Filtrar OF"></th>
                <th><select name="filtro_estado_planificacion" class="form-control filtro-select"><option value="">Todos</option>@foreach(($filters['estados_planificacion'] ?? []) as $value)<option value="{{ $value }}" {{ request('filtro_estado_planificacion') == $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach</select></th>
                <th><input type="text" name="filtro_producto" value="{{ request('filtro_producto') }}" class="form-control filtro-texto" placeholder="Filtrar Producto"></th>
                <th><input type="text" name="filtro_descripcion" value="{{ request('filtro_descripcion') }}" class="form-control filtro-texto" placeholder="Filtrar Descripcion"></th>
                <th><select name="filtro_categoria" class="form-control filtro-select"><option value="">Todos</option>@foreach(($filters['categorias'] ?? []) as $value)<option value="{{ $value }}" {{ request('filtro_categoria') == $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach</select></th>
                <th></th><th></th>
                <th><input type="date" name="filtro_fecha_pedido" value="{{ request('filtro_fecha_pedido') }}" class="form-control filtro-texto"></th>
                <th><select name="filtro_nro_maquina" class="form-control filtro-select"><option value="">Todos</option>@foreach(($filters['maquinas'] ?? []) as $value)<option value="{{ $value }}" {{ request('filtro_nro_maquina') == $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach</select></th>
                <th><select name="filtro_familia_maquina" class="form-control filtro-select"><option value="">Todos</option>@foreach(($filters['familias'] ?? []) as $value)<option value="{{ $value }}" {{ request('filtro_familia_maquina') == $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach</select></th>
                <th><input type="text" name="filtro_nro_ingreso_mp" value="{{ request('filtro_nro_ingreso_mp') }}" class="form-control filtro-texto" placeholder="Filtrar Ingreso"></th>
                <th><input type="text" name="filtro_codigo_mp" value="{{ request('filtro_codigo_mp') }}" class="form-control filtro-texto" placeholder="Filtrar Codigo MP"></th>
                <th><input type="text" name="filtro_certificado_mp" value="{{ request('filtro_certificado_mp') }}" class="form-control filtro-texto" placeholder="Filtrar Certificado"></th>
                <th></th>
                <th><select name="filtro_proveedor" class="form-control filtro-select"><option value="">Todos</option>@foreach(($filters['proveedores'] ?? []) as $value)<option value="{{ $value }}" {{ request('filtro_proveedor') == $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach</select></th>
                <th></th>
                <th><input type="text" name="filtro_piezas_fabricadas" value="{{ request('filtro_piezas_fabricadas') }}" class="form-control filtro-texto" placeholder="Filtrar Fabricadas"></th>
                <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td>{{ $row->Nro_OF }}</td><td>{{ $row->Estado_Planificacion }}</td><td>{{ $row->Prod_Codigo }}</td><td>{{ $row->Prod_Descripcion }}</td><td>{{ $row->Nombre_Categoria }}</td><td>{{ $row->Revision_Plano_1 }}</td><td>{{ $row->Revision_Plano_2 }}</td><td>{{ $row->Fecha_del_Pedido ? \Carbon\Carbon::parse($row->Fecha_del_Pedido)->format('d/m/Y') : '' }}</td><td>{{ $row->Nro_Maquina }}</td><td>{{ $row->Familia_Maquinas }}</td><td>{{ $row->Nro_Ingreso_MP }}</td><td>{{ $row->Codigo_MP }}</td><td>{{ $row->Nro_Certificado_MP }}</td><td>{{ $row->Pedido_de_MP }}</td><td>{{ $row->Prov_Nombre }}</td><td>{{ number_format((float) ($row->Cant_Fabricacion ?? 0), 0, ',', '.') }}</td>
                <td><span class="metric-pill {{ $metricClass($row->Piezas_Fabricadas) }}">{{ number_format((float) ($row->Piezas_Fabricadas ?? 0), 0, ',', '.') }}</span></td>
                <td><span class="metric-pill {{ $metricClass($row->Porcentaje_Fabricado, 'percentage') }}">{{ number_format($displayPercent($row->Porcentaje_Fabricado ?? 0), 2, ',', '.') }} %{{ $percentSuffix($row->Porcentaje_Fabricado ?? 0) }}</span></td>
                <td><span class="metric-pill {{ $metricClass($row->Piezas_Entregadas) }}">{{ number_format((float) ($row->Piezas_Entregadas ?? 0), 0, ',', '.') }}</span></td>
                <td>{{ $row->Ultima_Fecha_Entrega ? \Carbon\Carbon::parse($row->Ultima_Fecha_Entrega)->format('d/m/Y') : '' }}{{ !empty($row->Ultimo_Remito_Entrega) ? ' - R ' . $row->Ultimo_Remito_Entrega : '' }}</td>
                <td><span class="metric-pill {{ $metricClass($row->Porcentaje_Entregado, 'percentage') }}">{{ number_format($displayPercent($row->Porcentaje_Entregado ?? 0), 2, ',', '.') }} %{{ $percentSuffix($row->Porcentaje_Entregado ?? 0) }}</span></td>
                <td><span class="metric-pill {{ $metricClass($row->Saldo_Entrega) }}">{{ number_format((float) ($row->Saldo_Entrega ?? 0), 0, ',', '.') }}</span></td>
                <td><span class="metric-pill metric-control {{ $controlClass($row->Control_Entrega) }}" title="{{ $row->Control_Entrega }}">{{ $controlShort($row->Control_Entrega) }}</span></td>
                <td><span class="metric-pill {{ $metricClass($row->Saldo_Fabricacion) }}">{{ number_format((float) ($row->Saldo_Fabricacion ?? 0), 0, ',', '.') }}</span></td>
                <td>{{ $row->Ultima_Fecha_Fabricacion ? \Carbon\Carbon::parse($row->Ultima_Fecha_Fabricacion)->format('d/m/Y') : '' }}</td>
                <td><div class="acciones-listado-of">@if(!empty($row->Id_OF))<a href="{{ route('pedido_cliente.show', $row->Id_OF) }}" class="btn btn-info btn-sm">Ver pedido</a>@endif @if(!empty($row->Id_Pedido_MP))<a href="{{ route('pedido_cliente_mp.editMassive', $row->Id_Pedido_MP) }}" class="btn btn-success btn-sm">MP</a>@endif @if(!empty($row->Id_OF))<a href="{{ route('fabricacion.showByNroOF', $row->Id_OF) }}" class="btn btn-primary btn-sm">Fabricacion</a>@endif</div></td>
            </tr>
            @empty
            <tr><td colspan="26" class="text-center">No se encontraron registros.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="plain-table-footer">
    <div class="plain-table-footer__info">Mostrando {{ $rows->firstItem() ?? 0 }} a {{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }} registros</div>
    <div class="plain-table-footer__pagination">{{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
</div>
