<?php

namespace App\Http\Livewire\Archiving\Register\Box;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPreviousNext;
use App\Models\Box;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class BoxLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPreviousNext;

    /**
     * Resource on display.
     *
     * @var \App\Models\Box
     */
    public Box $box;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function workingModel()
    {
        return $this->box;
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Box::class);
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mount()
    {
        $this->box
        ->loadCount('volumes')
        ->load(['room.floor.building.site']);
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.box.show')->layout('layouts.app');
    }
}
