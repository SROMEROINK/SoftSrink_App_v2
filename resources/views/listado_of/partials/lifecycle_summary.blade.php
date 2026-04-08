@php
    $lifecycle = $ofLifecycle ?? ['rows' => collect(), 'summary' => [], 'filters' => []];
    $rows = $lifecycle['rows'];
    $summary = $lifecycle['summary'] ?? [];
    $filters = $lifecycle['filters'] ?? [];
    $routeName = $routeName ?? 'listado_of.index';
    $skipKeys = ['resumen_of_anio_pedido', 'resumen_of_mes_pedido', 'resumen_of_categoria', 'resumen_of_maquina', 'resumen_of_familia', 'resumen_of_nro_of', 'resumen_of_page'];
@endphp

<div class="listado-of-lifecycle" data-lifecycle-scroll-root>
    <div class="alert alert-secondary listado-of-lifecycle__alert mb-3">
        <strong>Resumen adicional por OF:</strong>
        esta vista cruza pedido, fabricacion y entregas para mostrar en una sola fila el estado consolidado de cada OF fabricada.
    </div>

    <div class="row mb-3 listado-of-lifecycle__summary-row">
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format((int) ($summary['total_of'] ?? 0), 0, ',', '.') }}</h3>
                    <p>OF con fabricacion</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format((int) ($summary['total_pedido'] ?? 0), 0, ',', '.') }}</h3>
                    <p>Piezas pedidas</p>
                </div>
                <div class="icon"><i class="fas fa-list-ol"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format((int) ($summary['total_fabricadas'] ?? 0), 0, ',', '.') }}</h3>
                    <p>Piezas fabricadas</p>
                </div>
                <div class="icon"><i class="fas fa-industry"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format((int) ($summary['total_entregadas'] ?? 0), 0, ',', '.') }}</h3>
                    <p>Piezas entregadas</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route($routeName) }}" class="listado-of-lifecycle__filters mb-3">
        @foreach(request()->query() as $key => $value)
            @continue(in_array($key, $skipKeys, true))
            @if(is_array($value))
                @foreach($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="listado-of-lifecycle__group">
            <label for="resumen_of_anio_pedido">Anio pedido</label>
            <select name="resumen_of_anio_pedido" id="resumen_of_anio_pedido" class="form-control form-control-sm">
                <option value="">Todos</option>
                @foreach(($filters['years'] ?? []) as $anio)
                    <option value="{{ $anio }}" {{ (string) request('resumen_of_anio_pedido') === (string) $anio ? 'selected' : '' }}>{{ $anio }}</option>
                @endforeach
            </select>
        </div>

        <div class="listado-of-lifecycle__group">
            <label for="resumen_of_mes_pedido">Mes pedido</label>
            <select name="resumen_of_mes_pedido" id="resumen_of_mes_pedido" class="form-control form-control-sm">
                <option value="">Todos</option>
                @foreach(($filters['months'] ?? []) as $mes)
                    <option value="{{ $mes }}" {{ (string) request('resumen_of_mes_pedido') === (string) $mes ? 'selected' : '' }}>{{ $meses[$mes] ?? $mes }}</option>
                @endforeach
            </select>
        </div>

        <div class="listado-of-lifecycle__group">
            <label for="resumen_of_categoria">Categoria</label>
            <select name="resumen_of_categoria" id="resumen_of_categoria" class="form-control form-control-sm">
                <option value="">Todas</option>
                @foreach(($filters['categorias'] ?? []) as $categoria)
                    <option value="{{ $categoria }}" {{ request('resumen_of_categoria') === $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                @endforeach
            </select>
        </div>

        <div class="listado-of-lifecycle__group">
            <label for="resumen_of_maquina">Maquina</label>
            <select name="resumen_of_maquina" id="resumen_of_maquina" class="form-control form-control-sm">
                <option value="">Todas</option>
                @foreach(($filters['maquinas'] ?? []) as $maquina)
                    <option value="{{ $maquina }}" {{ request('resumen_of_maquina') === $maquina ? 'selected' : '' }}>{{ $maquina }}</option>
                @endforeach
            </select>
        </div>

        <div class="listado-of-lifecycle__group">
            <label for="resumen_of_familia">Familia</label>
            <select name="resumen_of_familia" id="resumen_of_familia" class="form-control form-control-sm">
                <option value="">Todas</option>
                @foreach(($filters['familias'] ?? []) as $familia)
                    <option value="{{ $familia }}" {{ request('resumen_of_familia') === $familia ? 'selected' : '' }}>{{ $familia }}</option>
                @endforeach
            </select>
        </div>

        <div class="listado-of-lifecycle__group listado-of-lifecycle__group--wide">
            <label for="resumen_of_nro_of">Nro OF</label>
            <input type="number" name="resumen_of_nro_of" id="resumen_of_nro_of" class="form-control form-control-sm" value="{{ request('resumen_of_nro_of') }}" placeholder="Buscar OF">
        </div>

        <div class="listado-of-lifecycle__actions">
            <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
            <a href="{{ route($routeName, collect(request()->query())->except($skipKeys)->all()) }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    <div class="listado-of-lifecycle__top-scroll" data-lifecycle-scroll-top>
        <div class="listado-of-lifecycle__top-scroll-inner"></div>
    </div>

    <div class="table-responsive listado-of-lifecycle__table-wrap" data-lifecycle-scroll-main>
        <table class="table table-bordered table-hover listado-of-lifecycle__table mb-0" data-lifecycle-table>
            <thead>
                <tr>
                    <th class="listado-of-lifecycle__sticky-col listado-of-lifecycle__sticky-col--first">Nro OF</th>
                    <th class="listado-of-lifecycle__sticky-col listado-of-lifecycle__sticky-col--code">Codigo</th>
                    <th>Descripcion</th>
                    <th>Categoria</th>
                    <th>Nro Maquina</th>
                    <th>Familia</th>
                    <th>Fecha Pedido</th>
                    <th>Cant. Pedido</th>
                    <th>Inicio Fabricacion</th>
                    <th>Fin Fabricacion</th>
                    <th>Piezas Fabricadas</th>
                    <th>Primera Entrega</th>
                    <th>Ultima Entrega</th>
                    <th>Piezas Entregadas</th>
                    <th>Saldo Fabricar</th>
                    <th>Saldo Entregar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $saldoFabricar = (float) ($row->Saldo_Fabricar ?? 0);
                        $saldoEntregar = (float) ($row->Saldo_Entregar ?? 0);

                        $saldoFabricarClass = $saldoFabricar < 0 ? 'text-danger font-weight-bold' : ($saldoFabricar > 0 ? 'text-success font-weight-bold' : '');
                        $saldoEntregarClass = $saldoEntregar < 0 ? 'text-danger font-weight-bold' : ($saldoEntregar > 0 ? 'text-success font-weight-bold' : '');

                        $saldoFabricarText = $saldoFabricar > 0
                            ? '+' . number_format($saldoFabricar, 0, ',', '.')
                            : number_format($saldoFabricar, 0, ',', '.');

                        $saldoEntregarText = $saldoEntregar > 0
                            ? '+' . number_format($saldoEntregar, 0, ',', '.')
                            : number_format($saldoEntregar, 0, ',', '.');
                    @endphp
                    <tr>
                        <td class="listado-of-lifecycle__sticky-col listado-of-lifecycle__sticky-col--first">{{ $row->Nro_OF }}</td>
                        <td class="listado-of-lifecycle__sticky-col listado-of-lifecycle__sticky-col--code">{{ $row->Prod_Codigo }}</td>
                        <td>{{ $row->Prod_Descripcion }}</td>
                        <td>{{ $row->Nombre_Categoria }}</td>
                        <td>{{ $row->Nro_Maquina ?: '-' }}</td>
                        <td>{{ $row->Familia_Maquinas ?: '-' }}</td>
                        <td>{{ $row->Fecha_del_Pedido ? \Carbon\Carbon::parse($row->Fecha_del_Pedido)->format('d/m/Y') : '' }}</td>
                        <td>{{ number_format((float) ($row->Cant_Pedido ?? 0), 0, ',', '.') }}</td>
                        <td>{{ $row->Fecha_Inicio_Fabricacion ? \Carbon\Carbon::parse($row->Fecha_Inicio_Fabricacion)->format('d/m/Y') : '' }}</td>
                        <td>{{ $row->Fecha_Fin_Fabricacion ? \Carbon\Carbon::parse($row->Fecha_Fin_Fabricacion)->format('d/m/Y') : '' }}</td>
                        <td>{{ number_format((float) ($row->Piezas_Fabricadas ?? 0), 0, ',', '.') }}</td>
                        <td>{{ $row->Fecha_Primera_Entrega ? \Carbon\Carbon::parse($row->Fecha_Primera_Entrega)->format('d/m/Y') : '' }}</td>
                        <td>{{ $row->Fecha_Ultima_Entrega ? \Carbon\Carbon::parse($row->Fecha_Ultima_Entrega)->format('d/m/Y') : '' }}</td>
                        <td>{{ number_format((float) ($row->Piezas_Entregadas ?? 0), 0, ',', '.') }}</td>
                        <td class="{{ $saldoFabricarClass }}">{{ $saldoFabricarText }}</td>
                        <td class="{{ $saldoEntregarClass }}">{{ $saldoEntregarText }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="text-center">No se encontraron OF para ese filtro.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="listado-of-lifecycle__bottom-scroll" data-lifecycle-scroll-bottom>
        <div class="listado-of-lifecycle__bottom-scroll-inner"></div>
    </div>

    <div class="plain-table-footer">
        <div class="plain-table-footer__info">Mostrando {{ $rows->firstItem() ?? 0 }} a {{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }} OF</div>
        <div class="plain-table-footer__pagination">{{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
</div>
