<?php

namespace App\Http\Livewire\Archiving\Register\Box;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithPreviousNext;
use App\Models\Box;
use App\Models\BoxVolume;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class BoxLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithPreviousNext;

    /**
     * Editing resource.
     *
     * @var \App\Models\Box
     */
    public Box $box;

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

            'box.room_id' => [
                'bail',
                'required',
                'integer',
                'exists:rooms,id',
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
                "unique:boxes,number,{$this->box->id},id,year,{$this->box->year}",
            ],

            'box.stand' => [
                'bail',
                'nullable',
                'integer',
                'between:1,1000',
            ],

            'box.shelf' => [
                'bail',
                'nullable',
                'integer',
                'between:1,1000',
            ],

            'box.description' => [
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
            'site_id' => __('Site'),
            'building_id' => __('Building'),
            'floor_id' => __('Floor'),
            'box.room_id' => __('Room'),
            'box.year' => __('Year'),
            'box.number' => __('Number'),
            'box.shelf' => __('Shelf'),
            'box.stand' => __('Stand'),
            'box.description' => __('Description'),
        ];
    }

    /**
     * Base resource that will be used to define the ids of the previous record
     * of the next one.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function workingModel()
    {
        return $this->box;
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::Update->value, Box::class);
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
        $this->box->load('room.floor.building.site');

        $this->sites = Site::defaultOrder()->get();
        $this->site_id = $this->box->room->floor->building->site->id;

        $this->buildings = Building::where('site_id', $this->site_id)->get();
        $this->building_id = $this->box->room->floor->building->id;

        $this->floors = Floor::where('building_id', $this->building_id)->get();
        $this->floor_id = $this->box->room->floor->id;

        $this->rooms = Room::where('floor_id', $this->floor_id)->get();
    }

    /**
     * Computed property to list paged box volumes.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getVolumesProperty()
    {
        return $this->applyPagination(
            $this->box->volumes()->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.box.edit', [
            'volumes' => $this->volumes
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
        $this->box->room_id = null;

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
        $this->reset(['floor_id', 'floors', 'rooms']);
        $this->box->room_id = null;

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
        $this->reset(['rooms']);
        $this->box->room_id = null;

        $this->validateOnly('floor_id');

        $this->rooms = Room::where('floor_id', $this->floor_id)->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->box->save();

        $this->flashSelf($saved);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function storeVolume()
    {
        $this->authorize(Policy::Create->value, BoxVolume::class);

        $next_number = $this->box->nextVolumeNumber();

        $this->validateVolume($next_number);

        $new_volume = new BoxVolume();
        $new_volume->number = $next_number;

        $saved = $this->box->volumes()->save($new_volume)
        ? true
        : false;

        $this->notify($saved, (string) $new_volume->number);
    }

    /**
     * Validate the box volume number.
     *
     * @param int $value
     *
     * @return void
     */
    private function validateVolume(int $value)
    {
        Validator::make(
            data: ['volume' => $value],
            rules: ['volume' => ['required', 'integer', 'between:1,1000']],
            customAttributes: ['volume' => __('Volume')]
        )->validate();
    }
}
