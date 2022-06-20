<?php

namespace App\Http\Livewire\Archiving\Register\Site;

use App\Enums\Policy;
use App\Http\Livewire\Traits\Searchable;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class SiteLivewireIndex extends Component
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
        $this->authorize(Policy::ViewAny->value, Site::class);
    }

    /**
     * Computed property to list paginated sites.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSitesProperty()
    {
        return $this->applyPagination(
            Site::withCount('buildings')
            ->whereLike('sites.name', $this->term)
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
        return view('livewire.archiving.register.site.index', [
            'sites' => $this->sites,
        ])->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Site $site
     *
     * @return void
     */
    public function markToDelete(Site $site)
    {
        $this->askForConfirmation($site);
    }
}
