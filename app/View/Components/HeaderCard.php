<?php
// resources\views\components\HeaderCard.php
namespace App\View\Components;

use Illuminate\View\Component;

class HeaderCard extends Component
{
    public $title;
    public $quantityTitle;
    public $buttonRoute;
    public $buttonText;

    public function __construct($title, $quantityTitle, $buttonRoute, $buttonText)
    {
        $this->title = $title;
        $this->quantityTitle = $quantityTitle;
        $this->buttonRoute = $buttonRoute;
        $this->buttonText = $buttonText;
    }

    public function render()
    {
        return view('components.header-card');
    }
}

