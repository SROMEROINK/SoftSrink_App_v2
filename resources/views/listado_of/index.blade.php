@extends('adminlte::page')

@section('title', 'Listado OF')

@section('content_header')
<x-header-card
    title="Listado OF - Resumen de Produccion"
    buttonRoute="{{ route('pedido_cliente.index') }}"
    buttonText="Ir a Pedidos"
/>
@stop

@section('content')
@php
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];
@endphp
<div class="container-fluid">
    <div class="alert alert-info">
        <strong>Listado OF:</strong>
        resumen vivo de una vista consolidada optimizada para pedido, maquina, MP, fabricacion y entregas.
    </div>

    <div id="listado_of_summary_container">
        @include('listado_of.partials.summary', ['summary' => $summary])
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form method="GET" action="{{ route('listado_of.index') }}" id="listado_of_filters_form">
                <input type="hidden" name="filtro_solo_restan_entregar" id="filtro_solo_restan_entregar" value="{{ request('filtro_solo_restan_entregar') ? 1 : 0 }}">
                <input type="hidden" name="filtro_solo_con_ultima_fabricacion" id="filtro_solo_con_ultima_fabricacion" value="{{ request('filtro_solo_con_ultima_fabricacion') ? 1 : 0 }}">

                <div class="plain-toolbar plain-toolbar--top mb-3">
                    <div class="plain-toolbar__left">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="listado_of_btn_print">Imprimir</button>
                        <a href="{{ route('listado_of.exportExcel', request()->query()) }}" class="btn btn-sm btn-success" id="listado_of_btn_excel">Excel</a>
                        <a href="{{ route('listado_of.exportCsv', request()->query()) }}" class="btn btn-sm btn-info" id="listado_of_btn_csv">CSV</a>
                        <button type="button" class="btn btn-sm {{ request('filtro_solo_restan_entregar') ? 'btn-warning' : 'btn-outline-warning' }}" id="listado_of_btn_restan">Restan entregar</button>
                        <button type="button" class="btn btn-sm {{ request('filtro_solo_con_ultima_fabricacion') ? 'btn-dark' : 'btn-outline-dark' }}" id="listado_of_btn_ultima_fabricacion">Con ultima fabricacion</button>
                    </div>
                    <div class="plain-toolbar__right">
                        <div class="d-flex align-items-center gap-2">
                            <label for="buscar" class="mb-0">Buscar:</label>
                            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" class="form-control form-control-sm plain-search-input">
                            <button type="submit" class="btn btn-sm btn-primary" id="listado_of_btn_filtrar">Filtrar</button>
                            <button type="button" class="btn btn-sm btn-secondary" id="listado_of_btn_limpiar">Limpiar</button>
                        </div>
                    </div>
                </div>
                <div class="plain-toolbar plain-toolbar--filters mb-3">
                    <div class="plain-filter-group">
                        <span class="plain-filter-group__label">Pedido</span>
                        <select name="filtro_anio_pedido" class="form-control form-control-sm filtro-select plain-toolbar-select">
                            <option value="">A&ntilde;o pedido</option>
                            @foreach(($filters['years_pedido'] ?? []) as $anio)
                                <option value="{{ $anio }}" {{ (string) request('filtro_anio_pedido') === (string) $anio ? 'selected' : '' }}>{{ $anio }}</option>
                            @endforeach
                        </select>
                        <select name="filtro_mes_pedido" class="form-control form-control-sm filtro-select plain-toolbar-select">
                            <option value="">Mes pedido</option>
                            @foreach($meses as $numero => $nombre)
                                <option value="{{ $numero }}" {{ (string) request('filtro_mes_pedido') === (string) $numero ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="plain-filter-group">
                        <span class="plain-filter-group__label">Fabricacion</span>
                        <select name="filtro_anio_fabricacion" class="form-control form-control-sm filtro-select plain-toolbar-select">
                            <option value="">A&ntilde;o fabricacion</option>
                            @foreach(($filters['years_fabricacion'] ?? []) as $anio)
                                <option value="{{ $anio }}" {{ (string) request('filtro_anio_fabricacion') === (string) $anio ? 'selected' : '' }}>{{ $anio }}</option>
                            @endforeach
                        </select>
                        <select name="filtro_mes_fabricacion" class="form-control form-control-sm filtro-select plain-toolbar-select">
                            <option value="">Mes fabricacion</option>
                            @foreach($meses as $numero => $nombre)
                                <option value="{{ $numero }}" {{ (string) request('filtro_mes_fabricacion') === (string) $numero ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="plain-filter-group">
                        <span class="plain-filter-group__label">Entrega</span>
                        <select name="filtro_anio_entrega" class="form-control form-control-sm filtro-select plain-toolbar-select">
                            <option value="">A&ntilde;o entrega</option>
                            @foreach(($filters['years_entrega'] ?? []) as $anio)
                                <option value="{{ $anio }}" {{ (string) request('filtro_anio_entrega') === (string) $anio ? 'selected' : '' }}>{{ $anio }}</option>
                            @endforeach
                        </select>
                        <select name="filtro_mes_entrega" class="form-control form-control-sm filtro-select plain-toolbar-select">
                            <option value="">Mes entrega</option>
                            @foreach($meses as $numero => $nombre)
                                <option value="{{ $numero }}" {{ (string) request('filtro_mes_entrega') === (string) $numero ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2 plain-toolbar">
                    <label for="page_length" class="mb-0">Mostrar</label>
                    <select name="page_length" id="page_length" class="form-control form-control-sm plain-page-length">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ (string) $pageLength === (string) $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                        <option value="all" {{ (string) $pageLength === 'all' ? 'selected' : '' }}>ALL</option>
                    </select>
                    <span>registros</span>
                </div>

                <div id="listado_of_table_container">
                    @include('listado_of.partials.table', ['rows' => $rows, 'filters' => $filters])
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/summary-boxes.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/listado_of_plain.css') }}">
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('listado_of_filters_form');
    const summaryContainer = document.getElementById('listado_of_summary_container');
    const tableContainer = document.getElementById('listado_of_table_container');
    const pageLength = document.getElementById('page_length');
    const buscar = document.getElementById('buscar');
    const limpiar = document.getElementById('listado_of_btn_limpiar');
    const btnUltimaFabricacion = document.getElementById('listado_of_btn_ultima_fabricacion');
    const btnRestan = document.getElementById('listado_of_btn_restan');
    const inputUltimaFabricacion = document.getElementById('filtro_solo_con_ultima_fabricacion');
    const inputRestan = document.getElementById('filtro_solo_restan_entregar');
    const btnCsv = document.getElementById('listado_of_btn_csv');
    const btnExcel = document.getElementById('listado_of_btn_excel');
    const btnPrint = document.getElementById('listado_of_btn_print');
    let filterTimer = null;
    let syncing = false;

    function currentUrl(params) {
        const url = new URL(form.action, window.location.origin);
        url.search = params.toString();
        return url;
    }

    function syncExportLinks(params) {
        const query = params.toString();
        if (btnCsv) btnCsv.href = `{{ route('listado_of.exportCsv') }}${query ? '?' + query : ''}`;
        if (btnExcel) btnExcel.href = `{{ route('listado_of.exportExcel') }}${query ? '?' + query : ''}`;
    }

    function refreshQuickButtons() {
        const activeRestan = inputRestan && inputRestan.value === '1';
        if (btnRestan) {
            btnRestan.classList.toggle('btn-warning', activeRestan);
            btnRestan.classList.toggle('btn-outline-warning', !activeRestan);
        }

        const activeUltimaFabricacion = inputUltimaFabricacion && inputUltimaFabricacion.value === '1';
        if (btnUltimaFabricacion) {
            btnUltimaFabricacion.classList.toggle('btn-dark', activeUltimaFabricacion);
            btnUltimaFabricacion.classList.toggle('btn-outline-dark', !activeUltimaFabricacion);
        }
    }

    function bindScrollSync() {
        const top = document.getElementById('listado_of_top_scroll');
        const topInner = top ? top.querySelector('div') : null;
        const wrap = document.getElementById('listado_of_table_wrap');
        const table = document.getElementById('tabla_listado_of_plain');
        if (!top || !topInner || !wrap || !table) return;
        const syncWidths = function () {
            topInner.style.width = table.scrollWidth + 'px';
            top.scrollLeft = wrap.scrollLeft;
        };
        if (!wrap.dataset.syncBound) {
            wrap.addEventListener('scroll', function () {
                if (syncing) return;
                syncing = true;
                top.scrollLeft = wrap.scrollLeft;
                syncing = false;
            });
            wrap.dataset.syncBound = '1';
        }
        if (!top.dataset.syncBound) {
            top.addEventListener('scroll', function () {
                if (syncing) return;
                syncing = true;
                wrap.scrollLeft = top.scrollLeft;
                syncing = false;
            });
            top.dataset.syncBound = '1';
        }
        syncWidths();
        window.requestAnimationFrame(syncWidths);
    }

    async function fetchListadoOf(url, pushState = true) {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('No se pudo actualizar el listado');
        const data = await response.json();
        summaryContainer.innerHTML = data.summary_html;
        tableContainer.innerHTML = data.table_html;
        bindScrollSync();
        syncExportLinks(new URL(url).searchParams);
        refreshQuickButtons();
        if (pushState) window.history.replaceState({}, '', url.toString());
    }

    function submitAjax(resetPage = true) {
        const params = new URLSearchParams(new FormData(form));
        if (resetPage) params.delete('page');
        syncExportLinks(params);
        fetchListadoOf(currentUrl(params)).catch(console.error);
    }

    function printCurrentView() {
        const printWindow = window.open('', '_blank', 'width=1200,height=800');
        if (!printWindow) return;
        printWindow.document.write(`
            <html><head><title>Listado OF</title>
            <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/listado_of_plain.css') }}">
            <style>body{font-family:Arial,sans-serif;padding:16px}.top-horizontal-scroll,.plain-toolbar,.plain-table-footer__pagination,.acciones-listado-of{display:none!important}.plain-table-wrap{overflow:visible;max-height:none}#tabla_listado_of_plain th,#tabla_listado_of_plain td{position:static!important}</style>
            </head><body>${summaryContainer.innerHTML}${tableContainer.innerHTML}</body></html>`);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function () { printWindow.print(); }, 300);
    }

    form.addEventListener('submit', function (event) { event.preventDefault(); submitAjax(false); });
    if (pageLength) pageLength.addEventListener('change', function () { submitAjax(true); });
    if (btnRestan) btnRestan.addEventListener('click', function () { inputRestan.value = inputRestan.value === '1' ? '0' : '1'; refreshQuickButtons(); submitAjax(true); });
    if (btnUltimaFabricacion) btnUltimaFabricacion.addEventListener('click', function () { inputUltimaFabricacion.value = inputUltimaFabricacion.value === '1' ? '0' : '1'; refreshQuickButtons(); submitAjax(true); });
    if (btnPrint) btnPrint.addEventListener('click', function (event) { event.preventDefault(); printCurrentView(); });

    document.addEventListener('change', function (event) {
        if (event.target.closest('#listado_of_filters_form .filtro-select') || event.target.closest('#listado_of_filters_form input[type="date"]')) submitAjax(true);
    });

    document.addEventListener('keydown', function (event) {
        if (event.target.closest('#listado_of_filters_form .filtro-texto') && event.key === 'Enter') { event.preventDefault(); submitAjax(true); }
    });

    document.addEventListener('input', function (event) {
        if (event.target === buscar || event.target.closest('#listado_of_filters_form .filtro-texto')) {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(function () { submitAjax(true); }, 350);
        }
    });

    document.addEventListener('click', function (event) {
        const pageLink = event.target.closest('#listado_of_table_container .pagination a');
        if (pageLink) {
            event.preventDefault();
            fetchListadoOf(new URL(pageLink.href), true).catch(console.error);
            return;
        }
        if (event.target === limpiar) {
            event.preventDefault();
            form.reset();
            if (buscar) buscar.value = '';
            if (inputRestan) inputRestan.value = '0';
            if (inputUltimaFabricacion) inputUltimaFabricacion.value = '0';
            refreshQuickButtons();
            syncExportLinks(new URLSearchParams());
            fetchListadoOf(new URL(form.action, window.location.origin)).catch(console.error);
        }
    });

    window.addEventListener('resize', bindScrollSync);
    syncExportLinks(new URLSearchParams(new FormData(form)));
    refreshQuickButtons();
    bindScrollSync();
});
</script>
@stop

