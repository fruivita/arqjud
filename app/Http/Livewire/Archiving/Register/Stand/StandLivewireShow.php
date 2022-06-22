<?php

namespace App\Http\Livewire\Archiving\Register\Stand;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
use App\Models\Shelf;
use App\Models\Stand;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class StandLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Resource on display.
     *
     * @var int
     */
    public int $stand_id;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Stand::class);
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
        $this->stand_id = $id;
    }

    /**
     * Computed property to get resource on display.
     *
     * @return \App\Models\Stand
     */
    public function getStandProperty()
    {
        return Stand::hierarchy()->findOrFail($this->stand_id);
    }

    /**
     * Computed property to list paged shelves based on stand id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getShelvesProperty()
    {
        return $this->applyPagination(
            Shelf::hierarchy()
            ->orderByWhen($this->sorts)
            ->where('stand_id', $this->stand_id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.stand.show')->layout('layouts.app');
    }
}
