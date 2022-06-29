<?php

namespace App\Http\Livewire\Archiving\Register\Box;

use App\Enums\Policy;
use App\Http\Livewire\Traits\SalvaColunasDePreferencia;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
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
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class BoxLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use SalvaColunasDePreferencia;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Se o componente deve ser renderizado no modo edição.
     *
     * @var bool
     */
    public bool $modo_edicao = false;

    /**
     * Nome das colunas que podem ser ocultadas.
     *
     * @var string[]
     */
    public array $colunas = [
        'volume',
        'apelido',
        'acoes',
    ];

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

            'stand_id' => [
                'bail',
                'required',
                'integer',
                'exists:stands,id',
            ],

            'box.shelf_id' => [
                'bail',
                'required',
                'integer',
                'exists:shelves,id',
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
            'room_id' => __('Room'),
            'stand_id' => __('Stand'),
            'box.shelf_id' => __('Shelf'),
            'box.year' => __('Year'),
            'box.number' => __('Number'),
            'box.description' => __('Description'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Box::class);
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
        $this->box = Box::hierarchy()->findOrFail($id);

        $this->initializeParentProperties();
    }

    /**
     * Computed property to list paged box volumes based on box id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getVolumesProperty()
    {
        return $this->applyPagination(
            BoxVolume::hierarchy()
            ->orderByWhen($this->sorts)
            ->where('box_id', $this->box->id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.box.edit')->layout('layouts.app');
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
            'shelves',
        ]);
        $this->box->shelf_id = null;

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
        $this->reset([
            'floor_id', 'floors',
            'room_id', 'rooms',
            'stand_id', 'stands',
            'shelves',
        ]);
        $this->box->shelf_id = null;

        $this->validateOnly('building_id');

        $this->floors = $this->floors();
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
            'shelves',
        ]);
        $this->box->shelf_id = null;

        $this->validateOnly('floor_id');

        $this->rooms = $this->rooms();
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
            'shelves',
        ]);
        $this->box->shelf_id = null;

        $this->validateOnly('room_id');

        $this->stands = $this->stands();
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

        $this->shelves = $this->shelves();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Box::class);

        $this->validate();

        $saved = $this->box->save();

        $this->reset('modo_edicao');

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
        $new_volume->alias = "Vol. {$next_number}";

        $saved = $this->box->volumes()->save($new_volume)
        ? true
        : false;

        $this->notify($saved, (string) $new_volume->number);
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\BoxVolume $box_volume
     *
     * @return void
     */
    public function setToDelete(BoxVolume $box_volume)
    {
        $this->askForConfirmation($box_volume);
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
        $this->site_id = $this->box->site_id;

        $this->buildings = $this->buildings();
        $this->building_id = $this->box->building_id;

        $this->floors = $this->floors();
        $this->floor_id = $this->box->floor_id;

        $this->rooms = $this->rooms();
        $this->room_id = $this->box->room_id;

        $this->stands = $this->stands();
        $this->stand_id = $this->box->stand_id;

        $this->shelves = $this->shelves();
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

    /**
     * Child rooms based on floor id.
     *
     * @return \Illuminate\Support\Collection
     */
    private function rooms()
    {
        return Room::where('floor_id', $this->floor_id)->orderBy('number', 'asc')->get();
    }

    /**
     * Child stands based on room id.
     *
     * @return \Illuminate\Support\Collection
     */
    private function stands()
    {
        return Stand::where('room_id', $this->room_id)->orderBy('number', 'asc')->get();
    }

    /**
     * Child shelves based on stand id.
     *
     * @return \Illuminate\Support\Collection
     */
    private function shelves()
    {
        return Shelf::where('stand_id', $this->stand_id)->orderBy('number', 'asc')->get();
    }
}
