<?php

namespace App\Http\Livewire\Archiving\Register\Floor;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
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
class FloorLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Editing resource.
     *
     * @var \App\Models\Floor
     */
    public Floor $floor;

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
     * Child buildings based on site id.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $buildings = null;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'floor.number' => [
                'bail',
                'required',
                'integer',
                'between:-100,300',
                "unique:floors,number,{$this->floor->id},id,building_id,{$this->floor->building_id}",
            ],

            'floor.description' => [
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

            'floor.building_id' => [
                'bail',
                'required',
                'integer',
                'exists:buildings,id',
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
            'floor.number' => __('Floor'),
            'floor.description' => __('Description'),
            'site_id' => __('Site'),
            'floor.building_id' => __('Building'),
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
        $this->authorize(Policy::Update->value, Floor::class);
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
        $this->floor = Floor::hierarchy()->findOrFail($id);

        $this->initializeParentProperties();
    }

    /**
     * Computed property to list paged rooms based on floor id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRoomsProperty()
    {
        return $this->applyPagination(
            Room::hierarchy()
            ->orderByWhen($this->sorts)
            ->where('floor_id', $this->floor->id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.floor.edit')->layout('layouts.app');
    }

    /**
     * Runs after a property called $site_id is updated.
     *
     * @return void
     */
    public function updatedSiteId()
    {
        $this->reset(['buildings']);
        $this->floor->building_id = null;

        $this->validateOnly('site_id');

        $this->buildings = $this->buildings();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->floor->save();

        $this->flashSelf($saved);
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Room $room
     *
     * @return void
     */
    public function setToDelete(Room $room)
    {
        $this->askForConfirmation($room);
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
        $this->site_id = $this->floor->site_id;

        $this->buildings = $this->buildings();
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
}
