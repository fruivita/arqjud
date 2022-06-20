<?php

namespace App\Http\Livewire\Archiving\Register\Building;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Building;
use App\Models\Site;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class BuildingLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Parent resource id.
     *
     * @var int
     */
    public int $site_id;

    /**
     * Resource that will be created.
     *
     * @var \App\Models\Building
     */
    public Building $building;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'building.name' => [
                'bail',
                'required',
                'string',
                'max:100',
                "unique:buildings,name,null,id,site_id,{$this->site->id}",
            ],

            'building.description' => [
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
            'building.name' => __('Name'),
            'building.description' => __('Description'),
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
        $this->authorize(Policy::Create->value, Building::class);
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @param int $id parent resource id
     *
     * @return void
     */
    public function mount(int $id)
    {
        $this->site_id = $id;

        $this->building = $this->blankModel();
    }

    /**
     * Computed propertyto get parent model.
     *
     * @return \App\Models\Site
     */
    public function getSiteProperty()
    {
        return Site::hierarchy()->findOrFail($this->site_id);
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Building
     */
    private function blankModel()
    {
        return new Building();
    }

    /**
     * Computed property to list paginated buildings.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBuildingsProperty()
    {
        return $this->applyPagination(
            Building::hierarchy()
            ->where('buildings.site_id', $this->site->id)
            ->orderByWhen($this->sort_column, $this->sort_direction)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.building.create')->layout('layouts.app');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $saved = $this->site->buildings()->save($this->building)
        ? true
        : false;

        $this->building = $this->blankModel();

        $this->resetPage();

        $this->flashSelf($saved);
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Building $building
     *
     * @return void
     */
    public function markToDelete(Building $building)
    {
        $this->askForConfirmation($building);
    }
}
