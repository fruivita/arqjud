<?php

namespace App\Http\Livewire\Archiving\Register\Floor;

use App\Enums\Policy;
use App\Http\Livewire\Traits\SalvaColunasDePreferencia;
use App\Http\Livewire\Traits\WithSearching;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Floor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class FloorLivewireIndex extends Component
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
        'andar',
        'apelido',
        'qtd_salas',
        'localidade',
        'predio',
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
        $this->authorize(Policy::ViewAny->value, Floor::class);
    }

    /**
     * Computed property to list paginated floors.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFloorsProperty()
    {
        return $this->applyPagination(
            Floor::hierarchy()
            ->whereLike(['floors.number', 'floors.alias'], $this->term)
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
        return view('livewire.archiving.register.floor.index')->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Floor $floor
     *
     * @return void
     */
    public function setToDelete(Floor $floor)
    {
        $this->askForConfirmation($floor);
    }
}
