<?php

namespace App\Http\Livewire\Archiving\Register\Building;

use App\Enums\Policy;
use App\Http\Livewire\Traits\SalvaColunasDePreferencia;
use App\Http\Livewire\Traits\WithSearching;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Building;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class BuildingLivewireIndex extends Component
{
    use AuthorizesRequests;
    use SalvaColunasDePreferencia;
    use WithDeleteModel;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithSearching;
    use WithSorting;

    /**
     * Nome das colunas que podem ser ocultadas.
     *
     * @var string[]
     */
    public array $colunas = [
        'predio',
        'qtd_andares',
        'localidade',
        'acoes'
    ];

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAny->value, Building::class);
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
            ->whereLike('buildings.name', $this->term)
            ->orderByWhen($this->sorts)
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.archiving.register.building.index')->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Building $building
     *
     * @return void
     */
    public function setToDelete(Building $building)
    {
        $this->askForConfirmation($building);
    }
}
