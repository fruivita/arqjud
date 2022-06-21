<?php

namespace App\Http\Livewire\Archiving\Register\Shelf;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
use App\Models\Box;
use App\Models\Shelf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ShelfLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Resource on display.
     *
     * @var int
     */
    public int $shelf_id;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Shelf::class);
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
        $this->shelf_id = $id;
    }

    /**
     * Computed property to get resource on display.
     *
     * @return \App\Models\Shelf
     */
    public function getShelfProperty()
    {
        return Shelf::hierarchy()->findOrFail($this->shelf_id);
    }

    /**
     * Computed property to list paged boxes based on shelf id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBoxesProperty()
    {
        return $this->applyPagination(
            Box::hierarchy()
            ->orderByWhen($this->sort_column, $this->sort_direction)
            ->where('shelf_id', $this->shelf_id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.shelf.show')->layout('layouts.app');
    }
}
