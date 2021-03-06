<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;

/**
 * @see https://laravel.com/docs/blade#components
 */
class App extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('layouts.app');
    }
}
