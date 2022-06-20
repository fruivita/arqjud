<?php

namespace App\Http\Livewire\Archiving\Register\Site;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
use App\Models\Building;
use App\Models\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class SiteLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Resource on display.
     *
     * @var \App\Models\Site
     */
    public Site $site;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Site::class);
    }

    /**
     * Computed property to list paged buildings.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBuildingsProperty()
    {
        return $this->applyPagination(
            Building::hierarchy()
            ->orderByWhen($this->sort_column, $this->sort_direction)
            ->where('site_id', $this->site->id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.site.show', [
            'buildings' => $this->buildings,
        ])->layout('layouts.app');
    }
}
