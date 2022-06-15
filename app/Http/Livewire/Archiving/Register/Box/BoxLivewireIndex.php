<?php

namespace App\Http\Livewire\Archiving\Register\Box;

use App\Enums\Policy;
use App\Http\Livewire\Traits\Searchable;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Box;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class BoxLivewireIndex extends Component
{
    use AuthorizesRequests;
    use Searchable;
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
        $this->authorize(Policy::ViewAny->value, Box::class);
    }

    /**
     * Computed property to list the paginated boxes.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBoxesProperty()
    {
        return $this->applyPagination(
            Box::with(['shelf.stand.room.floor.building.site'])
            ->withCount('volumes')
            ->whereLike(['number', 'year'], $this->term)
            ->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.box.index', [
            'boxes' => $this->boxes,
        ])->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Box $box
     *
     * @return void
     */
    public function markToDelete(Box $box)
    {
        $this->askForConfirmation($box);
    }
}
