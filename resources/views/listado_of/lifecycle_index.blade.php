@extends('adminlte::page')

@section('title', 'Resumen Ciclo OF')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/listado_of_plain.css') }}">
@stop

@section('content_header')
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h1 class="m-0 text-center flex-grow-1">Resumen Ciclo OF - Pedido, Fabricacion y Entrega</h1>
                <a href="{{ route('listado_of.index') }}" class="btn btn-success ml-3">Volver a Listado OF</a>
            </div>
        </div>
    </div>
@stop

@section('content')
    @php
        $meses = $meses ?? [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    @endphp

    <div class="container-fluid">
        <div class="card listado-of-lifecycle-card">
            <div class="card-body">
                <div id="listado-of-lifecycle-container">
                    @include('listado_of.partials.lifecycle_summary', [
                        'ofLifecycle' => $ofLifecycle,
                        'meses' => $meses,
                        'routeName' => 'listado_of.lifecycle',
                    ])
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    function initLifecycleScrollSync(container) {
        const root = (container || document).querySelector('[data-lifecycle-scroll-root]');
        if (!root) {
            return;
        }

        const main = root.querySelector('[data-lifecycle-scroll-main]');
        const top = root.querySelector('[data-lifecycle-scroll-top]');
        const bottom = root.querySelector('[data-lifecycle-scroll-bottom]');
        const table = root.querySelector('[data-lifecycle-table]');
        const topInner = root.querySelector('.listado-of-lifecycle__top-scroll-inner');
        const bottomInner = root.querySelector('.listado-of-lifecycle__bottom-scroll-inner');

        if (!main || !top || !bottom || !table || !topInner || !bottomInner) {
            return;
        }

        const syncWidths = () => {
            const width = table.scrollWidth;
            topInner.style.width = width + 'px';
            bottomInner.style.width = width + 'px';
        };

        let syncing = false;
        const syncScroll = (source) => {
            if (syncing) {
                return;
            }

            syncing = true;
            const left = source.scrollLeft;
            main.scrollLeft = left;
            top.scrollLeft = left;
            bottom.scrollLeft = left;
            syncing = false;
        };

        top.addEventListener('scroll', () => syncScroll(top));
        bottom.addEventListener('scroll', () => syncScroll(bottom));
        main.addEventListener('scroll', () => syncScroll(main));

        syncWidths();
        window.addEventListener('resize', syncWidths);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('listado-of-lifecycle-container');
        const endpoint = @json(route('listado_of.lifecycle'));

        if (!container) {
            return;
        }

        const buildUrl = (source) => {
            if (!source) {
                return endpoint;
            }

            if (typeof source === 'string') {
                return source;
            }

            const action = source.getAttribute('action') || endpoint;
            const formData = new FormData(source);
            const params = new URLSearchParams();

            formData.forEach((value, key) => {
                if (value !== null && String(value).trim() !== '') {
                    params.append(key, value);
                }
            });

            const query = params.toString();
            return query ? action + '?' + query : action;
        };

        const refreshLifecycle = async (source) => {
            const url = buildUrl(source);
            container.classList.add('listado-of-lifecycle--loading');

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('No se pudo actualizar el resumen.');
                }

                const data = await response.json();
                container.innerHTML = data.content_html || '';

                const nextUrl = new URL(url, window.location.origin);
                window.history.replaceState({}, '', nextUrl.pathname + nextUrl.search);
                initLifecycleScrollSync(container);
            } catch (error) {
                console.error(error);
                window.location.href = url;
            } finally {
                container.classList.remove('listado-of-lifecycle--loading');
            }
        };

        container.addEventListener('submit', function (event) {
            const form = event.target.closest('.listado-of-lifecycle__filters');
            if (!form) {
                return;
            }

            event.preventDefault();
            refreshLifecycle(form);
        });

        container.addEventListener('change', function (event) {
            const trigger = event.target.closest('.listado-of-lifecycle__filters select');
            if (!trigger) {
                return;
            }

            const form = trigger.form;
            if (form) {
                refreshLifecycle(form);
            }
        });

        container.addEventListener('click', function (event) {
            const link = event.target.closest('.plain-table-footer__pagination a');
            if (!link) {
                return;
            }

            event.preventDefault();
            refreshLifecycle(link.href);
        });

        initLifecycleScrollSync(container);
    });
</script>
@stop
