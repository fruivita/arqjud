<?php

namespace App\Http\Livewire\Archiving\Register\Box;

use App\Enums\Policy;
use App\Http\Livewire\Traits\SalvaColunasDePreferencia;
use App\Http\Livewire\Traits\WithSearching;
use App\Http\Livewire\Traits\WithSorting;
use App\Http\Livewire\Traits\WithDeleteModel;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Box;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class BoxLivewireIndex extends Component
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
        'caixa',
        'ano',
        'qtd_volumes',
        'localidade',
        'predio',
        'andar',
        'sala',
        'estante',
        'prateleira',
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
        $this->authorize(Policy::ViewAny->value, Box::class);
    }

    /**
     * Computed property to list the paginated boxes.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBoxesProperty()
    {
        return $this->applyPagination(
            Box::hierarchy()
            ->whereLike(['boxes.number', 'boxes.year'], $this->term)
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
        return view('livewire.archiving.register.box.index')->layout('layouts.app');
    }

    /**
     * Triggers the modal to confirm the deletion.
     *
     * @param \App\Models\Box $box
     *
     * @return void
     */
    public function setToDelete(Box $box)
    {
        $this->askForConfirmation($box);
    }
}
