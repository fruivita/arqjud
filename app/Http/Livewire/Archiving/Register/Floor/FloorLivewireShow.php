<?php

namespace App\Http\Livewire\Archiving\Register\Floor;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Floor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class FloorLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;

    /**
     * Resource on display.
     *
     * @var \App\Models\Floor
     */
    public Floor $floor;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Floor::class);
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
        $this->floor->load('building.site');
    }

    /**
     * Computed property to list paged rooms.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRoomsProperty()
    {
        return $this->applyPagination(
            $this->floor->rooms()->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.floor.show', [
            'rooms' => $this->rooms,
        ])->layout('layouts.app');
    }
}
