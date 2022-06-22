<?php

namespace App\Http\Livewire\Archiving\Register\Room;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
use App\Models\Stand;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class RoomLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Editing resource.
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
                "unique:rooms,number,{$this->room->id},id,floor_id,{$this->room->floor_id}",
            ],

            'room.description' => [
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

            'room.floor_id' => [
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
            'room.floor_id' => __('Floor'),
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
        $this->authorize(Policy::Update->value, Room::class);
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @param int $id editing resource id
     *
     * @return void
     */
    public function mount(int $id)
    {
        $this->room = Room::hierarchy()->findOrFail($id);

        $this->initializeParentProperties();
    }

    /**
     * Computed property to list paged stands based on room id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getStandsProperty()
    {
        return $this->applyPagination(
            Stand::hierarchy()
            ->orderByWhen($this->sorts)
            ->where('room_id', $this->room->id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.room.edit')->layout('layouts.app');
    }

    /**
     * Runs after a property called $site_id is updated.
     *
     * @return void
     */
    public function updatedSiteId()
    {
        $this->reset(['building_id', 'buildings', 'floors']);
        $this->room->floor_id = null;

        $this->validateOnly('site_id');

        $this->buildings = $this->buildings();
    }

    /**
     * Runs after a property called $building_id is updated.
     *
     * @return void
     */
    public function updatedBuildingId()
    {
        $this->reset(['floors']);
        $this->room->floor_id = null;

        $this->validateOnly('building_id');

        $this->floors = $this->floors();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->room->save();

        $this->flashSelf($saved);
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Stand $stand
     *
     * @return void
     */
    public function setToDelete(Stand $stand)
    {
        $this->askForConfirmation($stand);
    }

    /**
     * Initializes the parent properties/relationships of the item being
     * edited.
     *
     * @return void
     */
    private function initializeParentProperties()
    {
        $this->sites = Site::orderBy('name', 'asc')->get();
        $this->site_id = $this->room->site_id;

        $this->buildings = $this->buildings();
        $this->building_id = $this->room->building_id;

        $this->floors = $this->floors();
    }

    /**
     * Child buildings based on site id.
     *
     * @return \Illuminate\Support\Collection
     */
    private function buildings()
    {
        return Building::where('site_id', $this->site_id)->orderBy('name', 'asc')->get();
    }

    /**
     * Child floors based on building id.
     *
     * @return \Illuminate\Support\Collection
     */
    private function floors()
    {
        return Floor::where('building_id', $this->building_id)->orderBy('number', 'asc')->get();
    }
}
