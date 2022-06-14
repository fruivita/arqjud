<?php

namespace App\Http\Livewire\Archiving\Register\Box;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Box;
use App\Models\BoxVolume;
use App\Models\Shelf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class BoxLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;

    /**
     * Parent resource.
     *
     * @var \App\Models\Shelf
     */
    public Shelf $shelf;

    /**
     * Resource that will be created.
     *
     * @var \App\Models\Box
     */
    public Box $box;

    /**
     * Amount of boxes to create at once.
     *
     * @var int
     */
    public $amount = 1;

    /**
     * Number of box volumes.
     *
     * @var int
     */
    public $volumes = 1;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'amount' => [
                'bail',
                'required',
                'integer',
                'between:1,1000',
            ],

            'box.year' => [
                'bail',
                'required',
                'integer',
                'between:1900,' . now()->format('Y'),
            ],

            'box.number' => [
                'bail',
                'required',
                'integer',
                'min:1',
                "unique:boxes,number,null,id,year,{$this->box->year}",
            ],

            'box.description' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'volumes' => [
                'bail',
                'required',
                'integer',
                'between:1,1000',
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
            'amount' => __('Amount'),
            'box.year' => __('Year'),
            'box.number' => __('Number'),
            'box.description' => __('Description'),
            'volumes' => __('Volumes'),
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
        $this->authorize(Policy::Create->value, Box::class);
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
        $this->shelf->load('stand.room.floor.building.site');
        $this->box = $this->blankModel();
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Box
     */
    private function blankModel()
    {
        return new Box();
    }

    /**
     * Computed property to list paginated boxes.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBoxesProperty()
    {
        return $this->applyPagination(
            $this
                ->shelf
                ->boxes()
                ->withCount('volumes')
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
        return view('livewire.archiving.register.box.create', [
            'boxes' => $this->boxes,
        ])->layout('layouts.app');
    }

    /**
     * Runs after a property called $year is updated.
     *
     * @return void
     */
    public function updatedBoxYear()
    {
        $this->validateOnly('box.year');

        $this->box->number = Box::where('year', $this->box->year)->max('number') + 1;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $saved = Box::createMany(
            $this->box,
            $this->amount(),
            $this->volumes(),
            $this->shelf,
        );

        $this->box = $this->blankModel();

        $this->resetPage();

        $this->flashSelf($saved);
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

    /**
     * Check if the user has authorization to create multiple boxes and returns
     * the appropriate value.
     *
     * @return int 1 if the current user don't have authorization or $amount if
     *             user do
     */
    private function amount()
    {
        return auth()->user()->can(Policy::CreateMany->value, Box::class)
        ? $this->amount
        : 1;
    }

    /**
     * Check if the user has authorization to create box volumes and returns
     * the appropriate value.
     *
     * @return int 1 if the current user don't have authorization or $volumes
     *             if user do
     */
    private function volumes()
    {
        return auth()->user()->can(Policy::Create->value, BoxVolume::class)
        ? $this->volumes
        : 1;
    }
}
