<?php

namespace App\Http\Livewire\Archiving\Register\Building;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
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
class BuildingLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithSorting;

    /**
     * Editing resource.
     *
     * @var \App\Models\Building
     */
    public Building $building;

    /**
     * All sites.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $sites = null;

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
                "unique:buildings,name,{$this->building->id},id,site_id,{$this->building->site_id}",
            ],

            'building.description' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'building.site_id' => [
                'bail',
                'required',
                'integer',
                'exists:sites,id',
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
            'building.site_id' => __('Site'),
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
        $this->authorize(Policy::Update->value, Building::class);
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
        $this->building = Building::hierarchy()->findOrFail($id);

        $this->sites = Site::orderBy('name', 'asc')->get();
    }

    /**
     * Computed property to list paged floors based on building id.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFloorsProperty()
    {
        return $this->applyPagination(
            Floor::hierarchy()
            ->orderByWhen($this->sort_column, $this->sort_direction)
            ->where('building_id', $this->building->id)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.building.edit')->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Floor $floor
     *
     * @return void
     */
    public function markToDelete(Floor $floor)
    {
        $this->askForConfirmation($floor);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->building->save();

        $this->flashSelf($saved);
    }
}
