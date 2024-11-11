    {{--  resources\views\components\header-card.blade.php --}}

@props(['title', 'quantityTitle', 'quantity', 'buttonRoute', 'buttonText', 'deletedRouteUrl' => null, 'deletedButtonText' => 'Ver Eliminados'])

<div class="card card-title-header">
    <h4 class="text-center">{{ $title }}</h4>  
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="info-section">
            <span class="card-title titulo-cantidad">{{ $quantityTitle }}</span>
            <span id="totalCantPiezas" class="total-numero">{{ $quantity ?? 0 }}</span>
        </div>
        <div class="button-group">
            <a href="{{ $buttonRoute }}" class="btn btn-success create-button">{{ $buttonText }}</a>

            {{-- Verifica si el deletedRouteUrl está definido y muestra el botón de eliminados --}}
            @if(!is_null($deletedRouteUrl))
                <a href="{{ $deletedRouteUrl }}" class="btn btn-secondary">{{ $deletedButtonText }}</a>
            @else
                <span class="text-danger">Ruta de eliminados no configurada</span>
            @endif
        </div>
    </div>
    @isset($slot)
        {{ $slot }}
    @endisset
</div>

@include('partials.navigation')
