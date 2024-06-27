{{-- resources\views\components\header-card.blade.php --}}

<div class="card card-title-header">
    <h4 class="text-center">{{ $title }}</h4>  
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="info-section">
            <span class="card-title titulo-cantidad">{{ $quantityTitle }}</span>
            <span id="totalCantPiezas" class="total-numero">0</span>
        </div>
        <a href="{{ $buttonRoute }}" class="btn btn-success create-button">{{ $buttonText }}</a>
    </div>
</div>

@include('partials.navigation') <!-- Aquí incluyes la botonera personalizada -->
