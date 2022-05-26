<?php

namespace App\Http\Livewire\Archiving\Register\Box;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Models\Box;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
use App\Rules\RouteExists;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class BoxLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;


    /**
     * Amount of boxes to create at once.
     *
     * @var int
     */
    public $amount = 1;

    /**
     * Year of the boxes.
     *
     * @var int
     */
    public $year;

    /**
     * First box number.
     *
     * @var int
     */
    public $number;

    /**
     * Stand of the boxes.
     *
     * @var int
     */
    public $stand;

    /**
     * Shelf of the boxes.
     *
     * @var int
     */
    public $shelf;

    /**
     * All sites.
     *
     * @var null|\Illuminate\Support\Collection
     */
    public ?Collection $sites = null;

    /**
     * Selected site id.
     *
     * @var null|int
     */
    public $site_id = null;

    /**
     * Selected site buildings.
     *
     * @var null|\Illuminate\Support\Collection
     */
    public ?Collection $buildings = null;

    /**
     * Selected building id.
     *
     * @var null|int
     */
    public $building_id = null;

    /**
     * Selected building floors.
     *
     * @var null|\Illuminate\Support\Collection
     */
    public ?Collection $floors = null;

    /**
     * Selected floor id.
     *
     * @var null|int
     */
    public $floor_id = null;

    /**
     * Selected floor rooms.
     *
     * @var null|\Illuminate\Support\Collection
     */
    public ?Collection $rooms = null;

    /**
     * Selected room id.
     *
     * @var null|int
     */
    public $room_id = null;

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
                'required',
                'integer',
                'exists:rooms,id',
            ],

            'amount' => [
                'bail',
                'required',
                'integer',
                'between:1,1000',
            ],

            'year' => [
                'bail',
                'required',
                'integer',
                'between:1900,' . now()->format('Y'),
            ],

            'number' => [
                'bail',
                'required',
                'integer',
                'min:1',
                // 'unique:boxes,number,' . $this->id . ',id,colum_2,' . $this->column_2 . ',colum_3,' . $this->column_3,
            ],

            'stand' => [
                'bail',
                'nullable',
                'integer',
                'between:1,1000',
            ],

            'shelf' => [
                'bail',
                'nullable',
                'integer',
                'between:1,1000',
            ],

            // 'volumes' => [
            //     'bail',
            //     'required',
            //     'integer',
            //     'between:1,1000',
            // ],
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
            'amount' => __('Amount'),
            'year' => __('Year'),
            'number' => __('Number'),
            'shelf' => __('Shelf'),
            'stand' => __('Stand'),
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
     * Runs after a property called $site is updated.
     *
     * @return void
     */
    public function updatedSiteId()
    {
        $this->reset(['building_id', 'buildings', 'floor_id', 'floors', 'room_id', 'rooms']);

        $this->validateOnly('site_id');

        $this->buildings = Building::where('site_id', $this->site_id)->get();
    }

    /**
     * Runs after a property called $building is updated.
     *
     * @return void
     */
    public function updatedBuildingId()
    {
        $this->reset(['floor_id', 'floors', 'room_id', 'rooms']);

        $this->validateOnly('building_id');

        $this->floors = Floor::where('building_id', $this->building_id)->get();
    }

    /**
     * Runs after a property called $floor is updated.
     *
     * @return void
     */
    public function updatedFloorId()
    {
        $this->reset(['room_id', 'rooms']);

        $this->validateOnly('floor_id');

        $this->rooms = Room::where('floor_id', $this->floor_id)->get();
    }

    /**
     * Runs after a property called $room is updated.
     *
     * @return void
     */
    public function updatedRoomId()
    {
        $this->validateOnly('room_id');
    }

    /**
     * Runs after a property called $year is updated.
     *
     * @return void
     */
    public function updatedYear()
    {
        $this->validateOnly('year');

        $this->number = Box::where('year', $this->year)->max('number') + 1;
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
            new Box([
                'year' => $this->year,
                'number' => $this->number,
                'stand' => $this->stand,
                'shelf' => $this->shelf,
            ]),
            $this->amount(),
            Room::find($this->room_id),
        );

        $this->flashSelf($saved);
    }


    /**
     * Check if the user has authorization to create multiple boxes and returns
     * the appropriate value.
     *
     * @return int 1 if the current user don't have authorization or $amount if
     * user do.
     */
    private function amount()
    {
        return auth()->user()->can(Policy::CreateMany->value, Box::class)
        ? $this->amount
        : 1;
    }
}
