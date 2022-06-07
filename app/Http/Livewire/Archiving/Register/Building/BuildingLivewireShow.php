<?php

namespace App\Http\Livewire\Archiving\Register\Building;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Building;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class BuildingLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;

    /**
     * Resource on display.
     *
     * @var \App\Models\Building
     */
    public Building $building;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Building::class);
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
        $this->building->load('site');
    }

    /**
     * Computed property to list paged floors.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFloorsProperty()
    {
        return $this->applyPagination(
            $this->building->floors()->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.building.show', [
            'floors' => $this->floors,
        ])->layout('layouts.app');
    }
}
