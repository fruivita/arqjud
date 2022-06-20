<?php

namespace App\Http\Livewire\Archiving\Register\Building;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
use App\Models\Building;
use App\Models\Floor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class BuildingLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Resource on display id.
     *
     * @var int
     */
    public int $building_id;

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
     * @param int $id resource on display id
     *
     * @return void
     */
    public function mount(int $id)
    {
        $this->building_id = $id;
    }

    /**
     * Computed property to get resource on display.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBuildingProperty()
    {
        return Building::hierarchy()->findOrFail($this->building_id);
    }

    /**
     * Computed property to list paged floors based on building id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFloorsProperty()
    {
        return $this->applyPagination(
            Floor::hierarchy()
            ->orderByWhen($this->sort_column, $this->sort_direction)
            ->where('building_id', $this->building->id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.building.show')->layout('layouts.app');
    }
}
