<?php

namespace App\Http\Livewire\Archiving\Register\Floor;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class FloorLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;

    /**
     * Resource that will be created.
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
     * Selected site buildings.
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
                "unique:floors,number,null,id,building_id,{$this->floor->building_id}",
            ],

            'floor.description' => [
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
        $this->authorize(Policy::Create->value, Floor::class);
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
        $this->floor = $this->blankModel();
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Floor
     */
    private function blankModel()
    {
        return new Floor();
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.floor.create')->layout('layouts.app');
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

        $this->buildings = Building::where('site_id', $this->site_id)->defaultOrder()->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $saved = $this->floor->save();

        $this->flashSelf($saved);
    }
}
