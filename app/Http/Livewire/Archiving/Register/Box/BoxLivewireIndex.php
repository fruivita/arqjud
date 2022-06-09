<?php

namespace App\Http\Livewire\Archiving\Register\Box;

use App\Enums\Policy;
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
    use WithPerPagePagination;

    /**
     * Searchable term entered by the user.
     *
     * @var string
     */
    public $term;

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
            ->search($this->term)
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
     * Get custom attributes for query strings.
     *
     * @return array<string, mixed>
     */
    protected function queryString()
    {
        return [
            'term' => [
                'except' => '',
                'as' => 's',
            ],
        ];
    }

    /**
     * Returns the pagination to the initial pagination.
     *
     * Runs before a property called $term is updated.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function updatingTerm($value)
    {
        Validator::make(
            data: ['term' => $value],
            rules: ['term' => ['nullable', 'string', 'max:50']],
            customAttributes: ['term' => __('Searchable term')]
        )->validate();

        $this->resetPage();
    }
}
