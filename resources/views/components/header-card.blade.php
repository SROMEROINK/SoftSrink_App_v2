@props([
    'title',
    'quantityTitle' => null,
    'quantity' => null,
    'buttonRoute',
    'buttonText',
    'deletedRouteUrl' => null,
    'deletedButtonText' => 'Ver Eliminados'
])

<div class="card card-title-header">
    <div class="card-title-header__inner">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm card-title-header__home">
            <i class="fas fa-home mr-1"></i>Inicio
        </a>
        <h4 class="text-center mb-0">{{ $title }}</h4>
        <div class="card-title-header__spacer"></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">

        @if(!is_null($quantityTitle))
            <div class="info-section">
                <span class="card-title titulo-cantidad">{{ $quantityTitle }}</span>
                <span id="totalCantPiezas" class="total-numero">{{ $quantity ?? 0 }}</span>
            </div>
        @else
            <div></div>
        @endif

        <div class="button-group">
            <a href="{{ $buttonRoute }}" class="btn btn-success create-button">{{ $buttonText }}</a>

            @if(!is_null($deletedRouteUrl))
                <a href="{{ $deletedRouteUrl }}" class="btn btn-secondary">{{ $deletedButtonText }}</a>
            @endif
        </div>
    </div>

    @isset($slot)
        {{ $slot }}
    @endisset
</div>

@include('partials.navigation')

<style>
    .card-title-header__inner {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 12px;
        padding: 4px 0;
    }

    .card-title-header__home {
        justify-self: start;
        border-radius: 999px;
        padding: 6px 14px;
        font-weight: 700;
        white-space: nowrap;
    }

    .card-title-header__spacer {
        width: 78px;
    }

    .card-title-header h4 {
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .card-title-header__inner {
            grid-template-columns: 1fr;
        }

        .card-title-header__home,
        .card-title-header__spacer {
            display: none;
        }
    }
</style>
