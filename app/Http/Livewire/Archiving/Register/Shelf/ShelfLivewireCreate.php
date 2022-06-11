<?php

namespace App\Http\Livewire\Archiving\Register\Shelf;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Site;
use App\Models\Stand;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class ShelfLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;

    /**
     * Parent resource.
     *
     * @var \App\Models\Stand
     */
    public Stand $stand;

    /**
     * Resource that will be created.
     *
     * @var \App\Models\Shelf
     */
    public Shelf $shelf;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'shelf.number' => [
                'bail',
                'required',
                'integer',
                'between:1,100000',
                "unique:shelves,number,null,id,stand_id,{$this->stand->id}",
            ],

            'shelf.description' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, mixed>
     */
    protected function validationAttributes()
    {
        return [
            'shelf.number' => __('Shelf'),
            'shelf.description' => __('Description'),
        ];
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::Create->value, Shelf::class);
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
        $this->stand->load('room.floor.building.site');
        $this->shelf = $this->blankModel();
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Shelf
     */
    private function blankModel()
    {
        return new Shelf();
    }

    /**
     * Computed property to list paginated shelves.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getShelvesProperty()
    {
        return $this->applyPagination(
            $this
                ->stand
                ->shelves()
                ->withCount('boxes')
                ->latest()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.shelf.create', [
            'shelves' => $this->shelves,
        ])->layout('layouts.app');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $saved = $this->stand->shelves()->save($this->shelf)
        ? true
        : false;

        $this->shelf = $this->blankModel();

        $this->resetPage();

        $this->flashSelf($saved);
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Shelf $shelf
     *
     * @return void
     */
    public function markToDelete(Shelf $shelf)
    {
        $this->askForConfirmation($shelf);
    }
}
