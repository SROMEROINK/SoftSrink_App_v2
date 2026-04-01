<table border="1">
    <thead>
        <tr>
            <th>Nro OF</th><th>Planificacion</th><th>Producto</th><th>Descripcion</th><th>Categoria</th><th>Rev. Plano 1</th><th>Rev. Plano 2</th><th>Fecha Pedido</th><th>Nro Maquina</th><th>Familia Maquina</th><th>Nro Ingreso MP</th><th>Codigo MP</th><th>Certificado MP</th><th>Pedido MP</th><th>Proveedor</th><th>Cant. Fabricacion</th><th>Piezas Fabricadas</th><th>% Fabricado OF</th><th>Piezas Entregadas</th><th>Ult. Entrega</th><th>% Entregado OF</th><th>Saldo Entrega</th><th>Control Entrega</th><th>Saldo Fabricacion</th><th>Ult. Fabricacion</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>{{ $row->Nro_OF }}</td><td>{{ $row->Estado_Planificacion }}</td><td>{{ $row->Prod_Codigo }}</td><td>{{ $row->Prod_Descripcion }}</td><td>{{ $row->Nombre_Categoria }}</td><td>{{ $row->Revision_Plano_1 }}</td><td>{{ $row->Revision_Plano_2 }}</td><td>{{ $row->Fecha_del_Pedido ? \Carbon\Carbon::parse($row->Fecha_del_Pedido)->format('d/m/Y') : '' }}</td><td>{{ $row->Nro_Maquina }}</td><td>{{ $row->Familia_Maquinas }}</td><td>{{ $row->Nro_Ingreso_MP }}</td><td>{{ $row->Codigo_MP }}</td><td>{{ $row->Nro_Certificado_MP }}</td><td>{{ $row->Pedido_de_MP }}</td><td>{{ $row->Prov_Nombre }}</td><td>{{ number_format((float) ($row->Cant_Fabricacion ?? 0), 0, ',', '.') }}</td><td>{{ number_format((float) ($row->Piezas_Fabricadas ?? 0), 0, ',', '.') }}</td><td>{{ $controller->formatDisplayPercent($row->Porcentaje_Fabricado ?? 0) }}</td><td>{{ number_format((float) ($row->Piezas_Entregadas ?? 0), 0, ',', '.') }}</td><td>{{ $controller->formatLatestDelivery($row->Ultima_Fecha_Entrega ?? null, $row->Ultimo_Remito_Entrega ?? null) }}</td><td>{{ $controller->formatDisplayPercent($row->Porcentaje_Entregado ?? 0) }}</td><td>{{ number_format((float) ($row->Saldo_Entrega ?? 0), 0, ',', '.') }}</td><td>{{ $row->Control_Entrega }}</td><td>{{ number_format((float) ($row->Saldo_Fabricacion ?? 0), 0, ',', '.') }}</td><td>{{ $row->Ultima_Fecha_Fabricacion ? \Carbon\Carbon::parse($row->Ultima_Fecha_Fabricacion)->format('d/m/Y') : '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
