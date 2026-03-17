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
    <h4 class="text-center">{{ $title }}</h4>  
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