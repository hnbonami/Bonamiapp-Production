<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public string $containerMaxW;
    public string $containerH;
    public string $imageFlex;
    public string $formPadding;
    public string $formTop;

    public function __construct(
        string $containerMaxW = '650px',
        string $containerH = '450px',
        string $imageFlex = '1 1 0%',
        string $formPadding = 'p-8',
        string $formTop = '80px'
    ) {
        $this->containerMaxW = $containerMaxW;
        $this->containerH = $containerH;
        $this->imageFlex = $imageFlex;
        $this->formPadding = $formPadding;
        $this->formTop = $formTop;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
