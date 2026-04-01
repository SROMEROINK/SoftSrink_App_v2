<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title">Datos de la entrega</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-4">
            Esta vista registra la entrega final al cliente por OF y parcial. Los datos de producto, maquina y MP se completan desde <strong>listado_of_db</strong>.
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="Id_OF">Nro OF</label>
                    <input type="number" name="Id_OF" id="Id_OF" class="form-control" value="{{ old('Id_OF', $entrega->Id_OF ?? '') }}" required>
                    <small class="form-text text-muted">Se vincula con pedido_cliente.Nro_OF.</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="Nro_Parcial_Calidad">Parcial calidad</label>
                    <input type="text" name="Nro_Parcial_Calidad" id="Nro_Parcial_Calidad" class="form-control" value="{{ old('Nro_Parcial_Calidad', $entrega->Nro_Parcial_Calidad ?? '') }}" required>
                    <small class="form-text text-muted">Ejemplo: 000143/1</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="Cant_Piezas_Entregadas">Cant. piezas entregadas</label>
                    <input type="number" name="Cant_Piezas_Entregadas" id="Cant_Piezas_Entregadas" class="form-control" min="1" step="1" value="{{ old('Cant_Piezas_Entregadas', $entrega->Cant_Piezas_Entregadas ?? '') }}" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="Nro_Remito_Entrega_Calidad">Nro remito</label>
                    <input type="number" name="Nro_Remito_Entrega_Calidad" id="Nro_Remito_Entrega_Calidad" class="form-control" min="1" step="1" value="{{ old('Nro_Remito_Entrega_Calidad', $entrega->Nro_Remito_Entrega_Calidad ?? ($proximoRemito ?? '')) }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Fecha_Entrega_Calidad">Fecha de entrega</label>
                    <input type="date" name="Fecha_Entrega_Calidad" id="Fecha_Entrega_Calidad" class="form-control" value="{{ old('Fecha_Entrega_Calidad', isset($entrega) && $entrega->Fecha_Entrega_Calidad ? $entrega->Fecha_Entrega_Calidad->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="Inspector_Calidad">Inspector calidad</label>
                    <input type="text" name="Inspector_Calidad" id="Inspector_Calidad" class="form-control" value="{{ old('Inspector_Calidad', $entrega->Inspector_Calidad ?? auth()->user()?->name ?? '') }}" required>
                </div>
            </div>
            @if(isset($entrega))
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="reg_Status">Estado del registro</label>
                        <select name="reg_Status" id="reg_Status" class="form-control">
                            <option value="1" {{ (int) old('reg_Status', $entrega->reg_Status ?? 1) === 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ (int) old('reg_Status', $entrega->reg_Status ?? 1) === 0 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
            @else
                <input type="hidden" name="reg_Status" value="1">
            @endif
        </div>
    </div>
</div>

<div class="card mt-3 card-outline card-secondary entrega-of-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Detalle consolidado de la OF</h3>
        <span class="badge badge-info" id="badge-of-estado">{{ $detalleOf['Estado_Planificacion'] ?? 'Sin OF seleccionada' }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Producto</span>
                    <div class="detail-value" data-detail="Prod_Codigo">{{ $detalleOf['Prod_Codigo'] ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Descripcion</span>
                    <div class="detail-value" data-detail="Prod_Descripcion">{{ $detalleOf['Prod_Descripcion'] ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Categoria</span>
                    <div class="detail-value" data-detail="Nombre_Categoria">{{ $detalleOf['Nombre_Categoria'] ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Fecha pedido</span>
                    <div class="detail-value" data-detail="Fecha_del_Pedido">{{ !empty($detalleOf['Fecha_del_Pedido']) ? \Carbon\Carbon::parse($detalleOf['Fecha_del_Pedido'])->format('d/m/Y') : '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-3">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Nro maquina</span>
                    <div class="detail-value" data-detail="Nro_Maquina">{{ $detalleOf['Nro_Maquina'] ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Familia maquina</span>
                    <div class="detail-value" data-detail="Familia_Maquinas">{{ $detalleOf['Familia_Maquinas'] ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Nro ingreso MP</span>
                    <div class="detail-value" data-detail="Nro_Ingreso_MP">{{ $detalleOf['Nro_Ingreso_MP'] ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Codigo MP</span>
                    <div class="detail-value" data-detail="Codigo_MP">{{ $detalleOf['Codigo_MP'] ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Certificado MP</span>
                    <div class="detail-value" data-detail="Nro_Certificado_MP">{{ $detalleOf['Nro_Certificado_MP'] ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-3">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Proveedor</span>
                    <div class="detail-value" data-detail="Prov_Nombre">{{ $detalleOf['Prov_Nombre'] ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Pedido de MP</span>
                    <div class="detail-value" data-detail="Pedido_de_MP">{{ $detalleOf['Pedido_de_MP'] ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Cant. fabricacion</span>
                    <div class="detail-value" data-detail="Cant_Fabricacion">{{ isset($detalleOf['Cant_Fabricacion']) ? number_format((int) $detalleOf['Cant_Fabricacion'], 0, ',', '.') : '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Piezas fabricadas</span>
                    <div class="detail-value" data-detail="Piezas_Fabricadas">{{ isset($detalleOf['Piezas_Fabricadas']) ? number_format((int) $detalleOf['Piezas_Fabricadas'], 0, ',', '.') : '-' }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Total entregado</span>
                    <div class="detail-value" data-detail="Total_Entregado">{{ isset($detalleOf['Total_Entregado']) ? number_format((int) $detalleOf['Total_Entregado'], 0, ',', '.') : '-' }}</div>
                </div>
            </div>
        </div>

        <div class="detail-divider"></div>

        <div class="row">
            <div class="col-md-3">
                <div class="detail-item compact-detail highlight-detail">
                    <span class="detail-label">Saldo para entregar</span>
                    <div class="detail-value" data-detail="Saldo_Entrega">{{ isset($detalleOf['Saldo_Entrega']) ? number_format((int) $detalleOf['Saldo_Entrega'], 0, ',', '.') : '-' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="detail-item compact-detail">
                    <span class="detail-label">Ultima fabricacion</span>
                    <div class="detail-value" data-detail="Ultima_Fecha_Fabricacion">{{ !empty($detalleOf['Ultima_Fecha_Fabricacion']) ? \Carbon\Carbon::parse($detalleOf['Ultima_Fecha_Fabricacion'])->format('d/m/Y') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
