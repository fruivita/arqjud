<?php

namespace App\Http\Livewire\Archiving\Register\Building;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithLimit;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Building;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class BuildingLivewireIndex extends Component
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
     * @var \App\Models\Building|null
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
        $this->authorize(Policy::ViewAny->value, Building::class);
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
     * @return \App\Models\Building
     */
    private function blankModel()
    {
        return new Building();
    }

    /**
     * Computed property to list paginated buildings.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBuildingsProperty()
    {
        return $this->applyPagination(
            Building::withCount('floors')
            ->with('site')
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
        return view('livewire.archiving.register.building.index', [
            'buildings' => $this->buildings,
        ])->layout('layouts.app');
    }

    /**
     * Displays the modal and defines the resource to be deleted.
     *
     * @param \App\Models\Building $building
     *
     * @return void
     */
    public function markToDelete(Building $building)
    {
        $this->authorize(Policy::Delete->value, Building::class);

        $this->deleting = $building;

        $this->show_delete_modal = true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return void
     */
    public function destroy()
    {
        $this->authorize(Policy::Delete->value, Building::class);

        $deleted = $this->deleting->delete();

        $this->fill([
            'show_delete_modal' => false,
            'deleting' => $this->blankModel(),
        ]);

        $this->notify($deleted);
    }
}
