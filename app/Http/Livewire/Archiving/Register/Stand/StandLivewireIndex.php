<?php

namespace App\Http\Livewire\Archiving\Register\Stand;

use App\Enums\Policy;
use App\Http\Livewire\Traits\Searchable;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Stand;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class StandLivewireIndex extends Component
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
        $this->authorize(Policy::ViewAny->value, Stand::class);
    }

    /**
     * Computed property to list paginated stands.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getStandsProperty()
    {
        return $this->applyPagination(
            Stand::withcount('shelves')
            ->with('room.floor.building.site')
            ->whereLike('number', $this->term)
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
        return view('livewire.archiving.register.stand.index', [
            'stands' => $this->stands,
        ])->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Stand $stand
     *
     * @return void
     */
    public function markToDelete(Stand $stand)
    {
        $this->askForConfirmation($stand);
    }
}
