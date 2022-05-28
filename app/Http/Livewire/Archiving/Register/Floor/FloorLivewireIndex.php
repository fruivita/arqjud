<?php

namespace App\Http\Livewire\Archiving\Register\Floor;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithLimit;
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
    use WithFeedbackEvents;
    use WithLimit;
    use WithPerPagePagination;

    /**
     * Should the modal for deleting the resource be displayed?
     *
     * @var bool
     */
    public $show_delete_modal = false;

    /**
     * Resource that will be deleted.
     *
     * @var \App\Models\Floor|null
     */
    public $deleting = null;

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
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mount()
    {
        $this->deleting = $this->blankModel();
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Floor
     */
    private function blankModel()
    {
        return new Floor();
    }

    /**
     * Computed property to list paginated floors.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFloorsProperty()
    {
        return $this->applyPagination(
            Floor::withCount('rooms')
            ->with('building.site')
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
        return view('livewire.archiving.register.floor.index', [
            'floors' => $this->floors,
        ])->layout('layouts.app');
    }

    /**
     * Displays the modal and defines the resource to be deleted.
     *
     * @param \App\Models\Floor $floor
     *
     * @return void
     */
    public function markToDelete(Floor $floor)
    {
        $this->authorize(Policy::Delete->value, Floor::class);

        $this->deleting = $floor;

        $this->show_delete_modal = true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return void
     */
    public function destroy()
    {
        $this->authorize(Policy::Delete->value, Floor::class);

        $deleted = $this->deleting->delete();

        $this->fill([
            'show_delete_modal' => false,
            'deleting' => $this->blankModel(),
        ]);

        $this->notify($deleted);
    }
}
