<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HeaderCard extends Component
{
    public $title;
    public $quantityTitle;
    public $quantity;
    public $buttonRoute;
    public $buttonText;
    public $deletedRouteUrl;
    public $deletedButtonText;

    public function __construct(
        $title,
        $buttonRoute,
        $buttonText,
        $quantityTitle = null,
        $quantity = null,
        $deletedRouteUrl = null,
        $deletedButtonText = 'Ver Eliminados'
    ) {
        $this->title = $title;
        $this->quantityTitle = $quantityTitle;
        $this->quantity = $quantity;
        $this->buttonRoute = $buttonRoute;
        $this->buttonText = $buttonText;
        $this->deletedRouteUrl = $deletedRouteUrl;
        $this->deletedButtonText = $deletedButtonText;
    }

    public function render(): View|Closure|string
    {
        return view('components.header-card');
    }
}