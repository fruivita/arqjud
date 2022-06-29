<?php

namespace App\Http\Livewire\Archiving\Register\Shelf;

use App\Enums\Policy;
use App\Http\Livewire\Traits\SalvaColunasDePreferencia;
use App\Http\Livewire\Traits\WithSearching;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Shelf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ShelfLivewireIndex extends Component
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
        'prateleira',
        'apelido',
        'qtd_caixas',
        'localidade',
        'predio',
        'andar',
        'sala',
        'estante',
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
        $this->authorize(Policy::ViewAny->value, Shelf::class);
    }

    /**
     * Computed property to list paginated shelves.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getShelvesProperty()
    {
        return $this->applyPagination(
            Shelf::hierarchy()
            ->whereLike(['shelves.number', 'shelves.alias'], $this->term)
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
        return view('livewire.archiving.register.shelf.index')->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Shelf $shelf
     *
     * @return void
     */
    public function setToDelete(Shelf $shelf)
    {
        $this->askForConfirmation($shelf);
    }
}
