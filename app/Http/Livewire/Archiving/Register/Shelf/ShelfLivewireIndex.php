<?php

namespace App\Http\Livewire\Archiving\Register\Shelf;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithLimit;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Shelf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ShelfLivewireIndex extends Component
{
    use AuthorizesRequests;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithLimit;
    use WithPerPagePagination;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAny->value, Shelf::class);
    }

    /**
     * Computed property to list paginated Shelves.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getShelvesProperty()
    {
        return $this->applyPagination(
            Shelf::with('stand.room.floor.building.site')->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.shelf.index', [
            'shelves' => $this->shelves,
        ])->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Shelf $shelf
     *
     * @return void
     */
    public function markToDelete(Shelf $shelf)
    {
        $this->askForConfirmation($shelf);
    }
}
