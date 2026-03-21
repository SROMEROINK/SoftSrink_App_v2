<div class="card show-card mt-3 pedido-mp-form-card">
    <div class="show-card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">{{ $formTitle }}</h3>
            <div class="pedido-show-subtitle">Definicion de materia prima para la orden de fabricacion.</div>
        </div>
    </div>

    <form method="POST"
          action="{{ $formAction }}"
          @if($isEdit)
              data-edit-check="true"
              data-exclude-fields="_token,_method"
              data-redirect-url="{{ route('pedido_cliente_mp.index') }}"
              data-success-message="Definicion de materia prima actualizada correctamente."
          @else
              data-ajax="true"
              data-redirect-url="{{ route('pedido_cliente_mp.index') }}"
          @endif>
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="card-body">
            @if(!empty($legacyMaxNroOf))
                <div class="alert alert-info pedido-mp-legacy-alert" role="alert">
                    Ya existen OF historicas en <strong>listado_of</strong> hasta la OF
                    <strong>#{{ number_format($legacyMaxNroOf, 0, ',', '.') }}</strong>.
                    En esta etapa solo se muestran OF nuevas pendientes de definicion de MP.
                </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="Id_OF">OF</label>
                        <select name="Id_OF" id="Id_OF" class="form-control filtro-select" required>
                            <option value="">Seleccionar OF</option>
                            @foreach($pedidos as $pedido)
                                <option value="{{ $pedido->Id_OF }}"
                                        data-nro-of="{{ $pedido->Nro_OF }}"
                                        data-producto="{{ $pedido->producto->Prod_Codigo ?? '' }}"
                                        data-categoria="{{ $pedido->producto->categoria->Nombre_Categoria ?? '' }}"
                                        data-subcategoria="{{ $pedido->producto->subCategoria->Nombre_SubCategoria ?? '' }}"
                                        data-descripcion="{{ $pedido->producto->Prod_Descripcion ?? '' }}"
                                        data-producto-material-mp="{{ $pedido->producto->Prod_Material_MP ?? '' }}"
                                        data-producto-diametro-mp="{{ $pedido->producto->Prod_Diametro_de_MP ?? '' }}"
                                        data-producto-codigo-mp="{{ $pedido->producto->Prod_Codigo_MP ?? '' }}"
                                        data-producto-largo-pieza="{{ $pedido->producto->Prod_Longitud_de_Pieza ?? '' }}"
                                        data-fecha="{{ $pedido->Fecha_del_Pedido ? \Carbon\Carbon::parse($pedido->Fecha_del_Pedido)->format('d/m/Y') : '' }}"
                                        data-cantidad="{{ $pedido->Cant_Fabricacion ?? 0 }}"
                                        {{ (string) old('Id_OF', $pedidoMp->Id_OF ?? ($selectedOf ?? '')) === (string) $pedido->Id_OF ? 'selected' : '' }}>
                                    OF #{{ $pedido->Nro_OF }} - {{ $pedido->producto->Prod_Codigo ?? 'Sin producto' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="Estado_Plani_Id">Estado de MP</label>
                        <select name="Estado_Plani_Id" id="Estado_Plani_Id" class="form-control filtro-select" required>
                            <option value="">Seleccionar estado</option>
                            @foreach($estadosPlanificacion as $estado)
                                <option value="{{ $estado->Estado_Plani_Id }}" {{ (string) old('Estado_Plani_Id', $pedidoMp->Estado_Plani_Id ?? 11) === (string) $estado->Estado_Plani_Id ? 'selected' : '' }}>
                                    {{ $estado->Nombre_Estado }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="reg_Status">Estado del Registro</label>
                        <select name="reg_Status" id="reg_Status" class="form-control filtro-select" required>
                            <option value="1" {{ (string) old('reg_Status', $pedidoMp->reg_Status ?? 1) === '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ (string) old('reg_Status', $pedidoMp->reg_Status ?? 1) === '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row pedido-of-resumen">
                <div class="col-md-3"><div class="detail-item"><span class="detail-label">Nro OF</span><div class="detail-value" id="detalle_nro_of">-</div></div></div>
                <div class="col-md-3"><div class="detail-item"><span class="detail-label">Producto</span><div class="detail-value" id="detalle_producto">-</div></div></div>
                <div class="col-md-3"><div class="detail-item"><span class="detail-label">Categoria</span><div class="detail-value" id="detalle_categoria">-</div></div></div>
                <div class="col-md-3"><div class="detail-item"><span class="detail-label">Subcategoria</span><div class="detail-value" id="detalle_subcategoria">-</div></div></div>
                <div class="col-md-6"><div class="detail-item"><span class="detail-label">Descripcion</span><div class="detail-value" id="detalle_descripcion">-</div></div></div>
                <div class="col-md-3"><div class="detail-item"><span class="detail-label">Fecha Pedido</span><div class="detail-value" id="detalle_fecha">-</div></div></div>
                <div class="col-md-3"><div class="detail-item"><span class="detail-label">Cant. Fabricacion</span><div class="detail-value" id="detalle_cantidad">-</div></div></div>
            </div>

            <div class="detail-divider"></div>

            <div class="pedido-mp-sticky-zone">
                <div class="alert alert-light border pedido-mp-compat-alert" role="alert">
                    <div>
                        <strong>Compatibilidad de MP del producto</strong>
                        <div id="compatibilidad_producto_texto" class="pedido-mp-compat-text">
                            Selecciona una OF para ver la compatibilidad de materia prima, la maquina y los ingresos sugeridos.
                        </div>
                    </div>
                </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="Id_Maquina">Maquina</label>
                        <select name="Id_Maquina" id="Id_Maquina" class="form-control filtro-select">
                            <option value="">Seleccionar maquina</option>
                            @foreach($maquinas as $maquina)
                                <option value="{{ $maquina->id_maquina }}"
                                        data-nro="{{ $maquina->Nro_maquina }}"
                                        data-familia="{{ $maquina->familia_maquina }}"
                                        data-scrap="{{ $maquina->scrap_maquina }}"
                                        {{ (string) old('Id_Maquina', $pedidoMp->Id_Maquina ?? '') === (string) $maquina->id_maquina ? 'selected' : '' }}>
                                    {{ $maquina->Nro_maquina }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3"><div class="form-group"><label for="Nro_Maquina">Nro de Maquina</label><input type="text" name="Nro_Maquina" id="Nro_Maquina" class="form-control" value="{{ old('Nro_Maquina', $pedidoMp->Nro_Maquina ?? '') }}" readonly></div></div>
                <div class="col-md-3"><div class="form-group"><label for="Familia_Maquina">Familia de Maquina</label><input type="text" name="Familia_Maquina" id="Familia_Maquina" class="form-control" value="{{ old('Familia_Maquina', $pedidoMp->Familia_Maquina ?? '') }}" readonly></div></div>
                <div class="col-md-3"><div class="form-group"><label for="Scrap_Maquina">Scrap por Maquina (mm)</label><input type="number" min="0" step="0.01" name="Scrap_Maquina" id="Scrap_Maquina" class="form-control js-calc" value="{{ old('Scrap_Maquina', $pedidoMp->Scrap_Maquina ?? '') }}" readonly></div></div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="Materia_Prima">Materia Prima</label>
                        <select name="Materia_Prima" id="Materia_Prima" class="form-control filtro-select">
                            <option value="">Sin definir</option>
                            @foreach($materiasPrimas as $materiaPrima)
                                <option value="{{ $materiaPrima }}" {{ old('Materia_Prima', $pedidoMp->Materia_Prima ?? '') === $materiaPrima ? 'selected' : '' }}>
                                    {{ $materiaPrima }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="Diametro_MP">Diametro MP</label>
                        <select name="Diametro_MP" id="Diametro_MP" class="form-control filtro-select">
                            <option value="">Sin definir</option>
                            @foreach($diametros as $diametro)
                                <option value="{{ $diametro }}" {{ old('Diametro_MP', $pedidoMp->Diametro_MP ?? '') === $diametro ? 'selected' : '' }}>
                                    {{ $diametro }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="Codigo_MP">Codigo MP</label>
                        <input type="text" name="Codigo_MP" id="Codigo_MP" class="form-control" value="{{ old('Codigo_MP', $pedidoMp->Codigo_MP ?? '') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="pedido-mp-stock-summary-inline">
                <div class="detail-item"><span class="detail-label">Metros requeridos</span><div class="detail-value" id="stock_metros_requeridos">-</div></div>
                <div class="detail-item"><span class="detail-label">Barras requeridas</span><div class="detail-value" id="stock_barras_requeridas">-</div></div>
                <div class="detail-item"><span class="detail-label">Barras disponibles</span><div class="detail-value" id="stock_barras_disponibles">-</div></div>
                <div class="detail-item"><span class="detail-label">Metros disponibles</span><div class="detail-value" id="stock_metros_disponibles">-</div></div>
            </div>

            <div class="pedido-mp-sticky-actions">
                <button type="button" id="consultar_stock_mp" class="btn btn-outline-primary btn-sm">
                    Consultar ingresos compatibles
                </button>
            </div>
            </div>

            <div class="pedido-mp-stock-panel">
                <div class="pedido-mp-stock-panel__header">
                    <h4>Ingresos compatibles sugeridos</h4>
                    <div id="stock_faltante_texto" class="pedido-mp-stock-panel__hint">Sin consulta todavia.</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered pedido-mp-stock-table mb-0">
                        <thead>
                            <tr>
                                <th>Nro Ingreso</th>
                                <th>Codigo MP</th>
                                <th>Certificado</th>
                                <th>Barras</th>
                                <th>Longitud x Un.</th>
                                <th>Metros Totales</th>
                                <th>Sugerencia</th>
                            </tr>
                        </thead>
                        <tbody id="stock_ingresos_body">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Selecciona una OF y consulta ingresos compatibles.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4"><div class="form-group"><label for="Nro_Ingreso_MP">Ingreso MP Seleccionado</label><input type="number" min="0" step="1" name="Nro_Ingreso_MP" id="Nro_Ingreso_MP" class="form-control" value="{{ old('Nro_Ingreso_MP', $pedidoMp->Nro_Ingreso_MP ?? '') }}"></div></div>
                <div class="col-md-4"><div class="form-group"><label for="Pedido_Material_Nro">Pedido de Material Nro</label><input type="text" name="Pedido_Material_Nro" id="Pedido_Material_Nro" class="form-control" value="{{ old('Pedido_Material_Nro', $pedidoMp->Pedido_Material_Nro ?? '') }}"></div></div>
                <div class="col-md-4"><div class="form-group"><label for="Nro_Certificado_MP">Nro de Certificado MP</label><input type="text" name="Nro_Certificado_MP" id="Nro_Certificado_MP" class="form-control" value="{{ old('Nro_Certificado_MP', $pedidoMp->Nro_Certificado_MP ?? '') }}"></div></div>
            </div>

            <div class="detail-divider"></div>

            <div class="row">
                <div class="col-md-3"><div class="form-group"><label for="Longitud_Un_MP">Longitud x Un.(MP)</label><input type="number" min="0" step="0.01" name="Longitud_Un_MP" id="Longitud_Un_MP" class="form-control js-calc" value="{{ old('Longitud_Un_MP', $pedidoMp->Longitud_Un_MP ?? '') }}"></div></div>
                <div class="col-md-3"><div class="form-group"><label for="Largo_Pieza">Largo de pieza</label><input type="number" min="0" step="0.01" name="Largo_Pieza" id="Largo_Pieza" class="form-control js-calc" value="{{ old('Largo_Pieza', $pedidoMp->Largo_Pieza ?? '') }}"></div></div>
                <div class="col-md-3"><div class="form-group"><label for="Frenteado">Frenteado</label><input type="number" min="0" step="0.01" name="Frenteado" id="Frenteado" class="form-control js-calc" value="{{ old('Frenteado', $pedidoMp->Frenteado ?? '0.50') }}"></div></div>
                <div class="col-md-3"><div class="form-group"><label for="Ancho_Cut_Off">Ancho Cut Off</label><input type="number" min="0" step="0.01" name="Ancho_Cut_Off" id="Ancho_Cut_Off" class="form-control js-calc" value="{{ old('Ancho_Cut_Off', $pedidoMp->Ancho_Cut_Off ?? '1.00') }}"></div></div>
            </div>

            <div class="row">
                <div class="col-md-3"><div class="form-group"><label for="Sobrematerial_Promedio">Sobrematerial-Promedio</label><input type="number" min="0" step="0.01" name="Sobrematerial_Promedio" id="Sobrematerial_Promedio" class="form-control js-calc" value="{{ old('Sobrematerial_Promedio', $pedidoMp->Sobrematerial_Promedio ?? '0.50') }}"></div></div>
                <div class="col-md-3"><div class="form-group"><label for="Largo_Total_Pieza">Largo total de Pieza</label><input type="number" min="0" step="0.01" name="Largo_Total_Pieza" id="Largo_Total_Pieza" class="form-control" value="{{ old('Largo_Total_Pieza', $pedidoMp->Largo_Total_Pieza ?? '') }}" readonly></div></div>
                <div class="col-md-3"><div class="form-group"><label for="MM_Totales">mm Totales</label><input type="number" min="0" step="0.01" name="MM_Totales" id="MM_Totales" class="form-control" value="{{ old('MM_Totales', $pedidoMp->MM_Totales ?? '') }}" readonly></div></div>
                <div class="col-md-3"><div class="form-group"><label for="Longitud_Barra_Sin_Scrap">Longitud de Barra - SCRAP(mm)</label><input type="number" min="0" step="0.01" name="Longitud_Barra_Sin_Scrap" id="Longitud_Barra_Sin_Scrap" class="form-control" value="{{ old('Longitud_Barra_Sin_Scrap', $pedidoMp->Longitud_Barra_Sin_Scrap ?? '') }}" readonly></div></div>
            </div>

            <div class="row">
                <div class="col-md-6"><div class="form-group"><label for="Cant_Barras_MP">Cant. de Barras MP</label><input type="number" min="0" step="1" name="Cant_Barras_MP" id="Cant_Barras_MP" class="form-control" value="{{ old('Cant_Barras_MP', $pedidoMp->Cant_Barras_MP ?? '') }}" readonly></div></div>
                <div class="col-md-6"><div class="form-group"><label for="Cant_Piezas_Por_Barra">Cant. de Piezas por Barra</label><input type="number" min="0" step="0.01" name="Cant_Piezas_Por_Barra" id="Cant_Piezas_Por_Barra" class="form-control" value="{{ old('Cant_Piezas_Por_Barra', $pedidoMp->Cant_Piezas_Por_Barra ?? '') }}" readonly></div></div>
            </div>

            <div class="form-group">
                <label for="Observaciones">Observaciones</label>
                <textarea name="Observaciones" id="Observaciones" rows="4" class="form-control">{{ old('Observaciones', $pedidoMp->Observaciones ?? '') }}</textarea>
            </div>
        </div>

        <div class="show-card-footer">
            <div class="show-actions">
                <a href="{{ route('pedido_cliente_mp.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-primary">{{ $submitText }}</button>
            </div>
        </div>
    </form>
</div>
