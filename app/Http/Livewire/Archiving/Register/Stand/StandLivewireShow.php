<?php

namespace App\Http\Livewire\Archiving\Register\Stand;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Stand;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class StandLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;

    /**
     * Resource on display.
     *
     * @var \App\Models\Stand
     */
    public Stand $stand;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Stand::class);
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
        $this->stand->load('room.floor.building.site');
    }

    /**
     * Computed property to list paged shelves.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getShelvesProperty()
    {
        return $this->applyPagination(
            $this->stand->shelves()->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.stand.show', [
            'shelves' => $this->shelves,
        ])->layout('layouts.app');
    }
}
