<?php

namespace App\Http\Livewire\Archiving\Register\Room;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class RoomLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;

    /**
     * Resource that will be created.
     *
     * @var \App\Models\Room
     */
    public Room $room;

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
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'room.number' => [
                'bail',
                'required',
                'integer',
                'between:1,100000',
                "unique:rooms,number,null,id,floor_id,{$this->floor_id}",
            ],

            'room.description' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

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
                'required',
                'integer',
                'exists:floors,id',
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
            'room.number' => __('Room'),
            'room.description' => __('Description'),
            'site_id' => __('Site'),
            'building_id' => __('Building'),
            'floor_id' => __('Floor'),
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
        $this->authorize(Policy::Create->value, Room::class);
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
        $this->room = $this->blankModel();
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Room
     */
    private function blankModel()
    {
        return new Room();
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.room.create')->layout('layouts.app');
    }

    /**
     * Runs after a property called $site_id is updated.
     *
     * @return void
     */
    public function updatedSiteId()
    {
        $this->reset(['building_id', 'buildings', 'floor_id', 'floors']);

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
        $this->reset(['floor_id', 'floors']);

        $this->validateOnly('building_id');

        $this->floors = Floor::where('building_id', $this->building_id)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $this->room->floor_id = $this->floor_id;

        $saved = $this->room->save();

        $this->room = $this->blankModel();
        $this->reset(['site_id', 'building_id', 'buildings', 'floor_id', 'floors']);

        $this->flashSelf($saved);
    }
}
