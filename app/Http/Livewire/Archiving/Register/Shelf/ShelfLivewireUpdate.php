<?php

namespace App\Http\Livewire\Archiving\Register\Shelf;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Box;
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
class ShelfLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;

    /**
     * Editing resource.
     *
     * @var \App\Models\Shelf
     */
    public Shelf $shelf;

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
                "unique:shelves,number,{$this->shelf->id},id,stand_id,{$this->shelf->stand_id}",
            ],

            'shelf.description' => [
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

            'room_id' => [
                'bail',
                'required',
                'integer',
                'exists:rooms,id',
            ],
            'shelf.stand_id' => [
                'bail',
                'required',
                'integer',
                'exists:stands,id',
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
            'site_id' => __('Site'),
            'building_id' => __('Building'),
            'floor_id' => __('Floor'),
            'room_id' => __('Room'),
            'shelf.stand_id' => __('Stand'),
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
        $this->authorize(Policy::Update->value, Shelf::class);
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

        $this->sites = Site::defaultOrder()->get();
        $this->site_id = $this->shelf->stand->room->floor->building->site->id;

        $this->buildings = Building::where('site_id', $this->site_id)->defaultOrder()->get();
        $this->building_id = $this->shelf->stand->room->floor->building->id;

        $this->floors = Floor::where('building_id', $this->building_id)->defaultOrder()->get();
        $this->floor_id = $this->shelf->stand->room->floor->id;

        $this->rooms = Room::where('floor_id', $this->floor_id)->defaultOrder()->get();
        $this->room_id = $this->shelf->stand->room->id;

        $this->stands = Stand::where('room_id', $this->room_id)->defaultOrder()->get();
    }

    /**
     * Computed property to list paged boxes.
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
        return view('livewire.archiving.register.shelf.edit', [
            'boxes' => $this->boxes,
        ])->layout('layouts.app');
    }

    /**
     * Runs after a property called $site_id is updated.
     *
     * @return void
     */
    public function updatedSiteId()
    {
        $this->reset(['building_id', 'buildings', 'floor_id', 'floors', 'room_id', 'rooms', 'stands']);
        $this->shelf->stand_id = null;

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
        $this->reset(['floor_id', 'floors', 'room_id', 'rooms', 'stands']);
        $this->shelf->stand_id = null;

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
        $this->reset(['room_id', 'rooms', 'stands']);
        $this->shelf->stand_id = null;

        $this->validateOnly('floor_id');

        $this->rooms = Room::where('floor_id', $this->floor_id)->defaultOrder()->get();
    }

    /**
     * Runs after a property called $room_id is updated.
     *
     * @return void
     */
    public function updatedRoomId()
    {
        $this->reset(['stands']);
        $this->shelf->stand_id = null;

        $this->validateOnly('room_id');

        $this->stands = Stand::where('room_id', $this->room_id)->defaultOrder()->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->shelf->save();

        $this->shelf->refresh();

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
}
