<?php

namespace App\Http\Livewire\Archiving\Register\Stand;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Stand;
use App\Models\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class StandLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;
    use WithPerPagePagination;

    /**
     * Editing resource.
     *
     * @var \App\Models\Stand
     */
    public Stand $stand;

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
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'stand.number' => [
                'bail',
                'required',
                'integer',
                'between:1,100000',
                "unique:stands,number,{$this->stand->id},id,room_id,{$this->stand->room_id}",
            ],

            'stand.description' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'site_id' => [
                'bail',
                'required',
                'integer',
                'exists:sites,id',
            ],

            'building_id' => [
                'bail',
                'required',
                'integer',
                'exists:buildings,id',
            ],

            'floor_id' => [
                'bail',
                'required',
                'integer',
                'exists:floors,id',
            ],

            'stand.room_id' => [
                'bail',
                'required',
                'integer',
                'exists:rooms,id',
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
            'stand.number' => __('Stand'),
            'stand.description' => __('Description'),
            'site_id' => __('Site'),
            'building_id' => __('Building'),
            'floor_id' => __('Floor'),
            'stand.room_id' => __('Room'),
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
        $this->authorize(Policy::Update->value, Stand::class);
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

        $this->sites = Site::defaultOrder()->get();
        $this->site_id = $this->stand->room->floor->building->site->id;

        $this->buildings = Building::where('site_id', $this->site_id)->defaultOrder()->get();
        $this->building_id = $this->stand->room->floor->building->id;

        $this->floors = Floor::where('building_id', $this->building_id)->defaultOrder()->get();
        $this->floor_id = $this->stand->room->floor->id;

        $this->rooms = Room::where('floor_id', $this->floor_id)->defaultOrder()->get();
    }

    /**
     * Computed property to list paged shelves.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getShelvesProperty()
    {
        return $this->applyPagination(
            $this->stand->shelves()->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.stand.edit', [
            'shelves' => $this->shelves
        ])->layout('layouts.app');
    }

    /**
     * Runs after a property called $site_id is updated.
     *
     * @return void
     */
    public function updatedSiteId()
    {
        $this->reset(['building_id', 'buildings', 'floor_id', 'floors', 'rooms']);
        $this->stand->room_id = null;

        $this->validateOnly('site_id');

        $this->buildings = Building::where('site_id', $this->site_id)->defaultOrder()->get();
    }

    /**
     * Runs after a property called $building_id is updated.
     *
     * @return void
     */
    public function updatedBuildingId()
    {
        $this->reset(['floor_id', 'floors', 'rooms']);
        $this->stand->room_id = null;

        $this->validateOnly('building_id');

        $this->floors = Floor::where('building_id', $this->building_id)->defaultOrder()->get();
    }

    /**
     * Runs after a property called $floor_id is updated.
     *
     * @return void
     */
    public function updatedFloorId()
    {
        $this->reset(['rooms']);
        $this->stand->room_id = null;

        $this->validateOnly('floor_id');

        $this->rooms = Room::where('floor_id', $this->floor_id)->defaultOrder()->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->stand->save();

        $this->flashSelf($saved);
    }
}
