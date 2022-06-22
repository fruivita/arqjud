<?php

namespace App\Http\Livewire\Archiving\Register\Floor;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class FloorLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Resource on display id.
     *
     * @var int
     */
    public int $floor_id;

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
     * @param int $id resource on display id
     *
     * @return void
     */
    public function mount(int $id)
    {
        $this->floor_id = $id;
    }

    /**
     * Computed property to get resource on display.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFloorProperty()
    {
        return Floor::hierarchy()->findOrFail($this->floor_id);
    }

    /**
     * Computed property to list paged rooms based on floor id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRoomsProperty()
    {
        return $this->applyPagination(
            Room::hierarchy()
            ->orderByWhen($this->sorts)
            ->where('floor_id', $this->floor->id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.floor.show')->layout('layouts.app');
    }
}
