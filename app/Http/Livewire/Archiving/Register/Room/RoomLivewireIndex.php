<?php

namespace App\Http\Livewire\Archiving\Register\Room;

use App\Enums\Policy;
use App\Http\Livewire\Traits\Searchable;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Room;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class RoomLivewireIndex extends Component
{
    use AuthorizesRequests;
    use Searchable;
    use WithSorting;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAny->value, Room::class);
    }

    /**
     * Computed property to list paginated rooms.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRoomsProperty()
    {
        return
            Room::hierarchy()
            ->whereLike('rooms.number', $this->term)
            ->orderByWhen($this->sort_column, $this->sort_direction)
            ->paginate(30);
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.room.index', [
            'rooms' => $this->rooms,
        ])->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Room $room
     *
     * @return void
     */
    public function markToDelete(Room $room)
    {
        $this->askForConfirmation($room);
    }
}
