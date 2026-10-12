<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    // Optional page metadata for Blade-view pages (<x-app-layout og-title="…">);
    // layouts.app falls back to site defaults for anything left null.
    public function __construct(
        public ?string $ogTitle = null,
        public ?string $ogDescription = null,
        public ?string $robots = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
