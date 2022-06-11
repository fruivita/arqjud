<?php

namespace App\Http\Livewire\Archiving\Register\Box;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Models\Box;
use App\Models\BoxVolume;
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
class BoxLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;

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
     * All sites.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $sites = null;

    /**
     * Selected site id.
     *
     * @var int|null
     */
    public $site_id = null;

    /**
     * Selected site buildings.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $buildings = null;

    /**
     * Selected building id.
     *
     * @var int|null
     */
    public $building_id = null;

    /**
     * Selected building floors.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $floors = null;

    /**
     * Selected floor id.
     *
     * @var int|null
     */
    public $floor_id = null;

    /**
     * Selected floor rooms.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $rooms = null;

    /**
     * Selected room id.
     *
     * @var int|null
     */
    public $room_id = null;

    /**
     * Selected room stands.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $stands = null;

    /**
     * Selected stand id.
     *
     * @var int|null
     */
    public $stand_id = null;

    /**
     * Selected stand shelves.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $shelves = null;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'site_id' => [
                'bail',
                'nullable',
                'integer',
                'exists:sites,id',
            ],

            'building_id' => [
                'bail',
                'nullable',
                'integer',
                'exists:buildings,id',
            ],

            'floor_id' => [
                'bail',
                'nullable',
                'integer',
                'exists:floors,id',
            ],

            'room_id' => [
                'bail',
                'nullable',
                'integer',
                'exists:rooms,id',
            ],

            'stand_id' => [
                'bail',
                'nullable',
                'integer',
                'exists:stands,id',
            ],

            'box.shelf_id' => [
                'bail',
                'required',
                'integer',
                'exists:shelves,id',
            ],

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
            'site_id' => __('Site'),
            'building_id' => __('Building'),
            'floor_id' => __('Floor'),
            'room_id' => __('Room'),
            'stand_id' => __('Stand'),
            'box.shelf_id' => __('Shelf'),
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
        $this->sites = Site::defaultOrder()->get();
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
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.box.create')->layout('layouts.app');
    }

    /**
     * Runs after a property called $site_id is updated.
     *
     * @return void
     */
    public function updatedSiteId()
    {
        $this->reset([
            'building_id', 'buildings',
            'floor_id', 'floors',
            'room_id', 'rooms',
            'stand_id', 'stands',
            'shelves'
        ]);
        $this->box->shelf_id = null;

        $this->validateOnly('site_id');

        $this->buildings = Building::where('site_id', $this->site_id)->get();
    }

    /**
     * Runs after a property called $building_id is updated.
     *
     * @return void
     */
    public function updatedBuildingId()
    {
        $this->reset([
            'floor_id', 'floors',
            'room_id', 'rooms',
            'stand_id', 'stands',
            'shelves'
        ]);
        $this->box->shelf_id = null;

        $this->validateOnly('building_id');

        $this->floors = Floor::where('building_id', $this->building_id)->get();
    }

    /**
     * Runs after a property called $floor_id is updated.
     *
     * @return void
     */
    public function updatedFloorId()
    {
        $this->reset([
            'room_id', 'rooms',
            'stand_id', 'stands',
            'shelves'
        ]);
        $this->box->shelf_id = null;

        $this->validateOnly('floor_id');

        $this->rooms = Room::where('floor_id', $this->floor_id)->get();
    }

    /**
     * Runs after a property called $room_id is updated.
     *
     * @return void
     */
    public function updatedRoomId()
    {
        $this->reset([
            'stand_id', 'stands',
            'shelves'
        ]);
        $this->box->shelf_id = null;

        $this->validateOnly('room_id');

        $this->stands = Stand::where('room_id', $this->room_id)->get();
    }

    /**
     * Runs after a property called $stand_id is updated.
     *
     * @return void
     */
    public function updatedStandId()
    {
        $this->reset(['shelves']);
        $this->box->shelf_id = null;

        $this->validateOnly('stand_id');

        $this->shelves = Shelf::where('stand_id', $this->stand_id)->get();
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
            Shelf::find($this->box->shelf_id),
        );

        $this->flashSelf($saved);
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
