<?php

namespace App\Http\Livewire\Archiving\Register\Room;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
use App\Models\Room;
use App\Models\Stand;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class RoomLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Resource on display id.
     *
     * @var int
     */
    public int $room_id;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Room::class);
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
        $this->room_id = $id;
    }

    /**
     * Computed property to get resource on display.
     *
     * @return \App\Models\Room
     */
    public function getRoomProperty()
    {
        return Room::hierarchy()->findOrFail($this->room_id);
    }

    /**
     * Computed property to list paged stands based on room id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getStandsProperty()
    {
        return $this->applyPagination(
            Stand::hierarchy()
            ->orderByWhen($this->sort_column, $this->sort_direction)
            ->where('room_id', $this->room_id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.room.show')->layout('layouts.app');
    }
}
