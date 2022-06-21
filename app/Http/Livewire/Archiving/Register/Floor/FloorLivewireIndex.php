<?php

namespace App\Http\Livewire\Archiving\Register\Floor;

use App\Enums\Policy;
use App\Http\Livewire\Traits\Searchable;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Floor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class FloorLivewireIndex extends Component
{
    use AuthorizesRequests;
    use Searchable;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAny->value, Floor::class);
    }

    /**
     * Computed property to list paginated floors.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFloorsProperty()
    {
        return $this->applyPagination(
            Floor::hierarchy()
            ->whereLike('floors.number', $this->term)
            ->orderByWhen($this->sort_column, $this->sort_direction)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.floor.index')->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Floor $floor
     *
     * @return void
     */
    public function markToDelete(Floor $floor)
    {
        $this->askForConfirmation($floor);
    }
}
