<?php

namespace App\Http\Livewire\Authorization\Delegation;

use App\Enums\Policy;
use App\Http\Livewire\Traits\SalvaColunasDePreferencia;
use App\Http\Livewire\Traits\WithSearching;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class DelegationLivewireIndex extends Component
{
    use AuthorizesRequests;
    use SalvaColunasDePreferencia;
    use WithPerPagePagination;
    use WithSearching;
    use WithSorting;

    /**
     * Preferências do usuário.
     *
     * @var array<string, mixed>
     */
    public array $preferencias = [
        // Nome das colunas da tabela que podem ser ocultadas
        'colunas' => [
            'nome',
            'usuario',
            'perfil',
            'delegante',
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::DelegationViewAny->value);
    }

    /**
     * Computed property to list delegable users.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUsersProperty()
    {
        return
        User::with('delegator')
            ->whereLike(['name', 'username'], $this->term)
            ->where('department_id', auth()->user()->department_id)
            ->orderByWhen($this->sorts)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.delegation.index')->layout('layouts.app');
    }

    /**
     * Creates a delegation, giving the informed user the same role as the
     * authenticated user.
     *
     * @param \App\Models\User $delegated
     *
     * @return void
     */
    public function create(User $delegated)
    {
        $this->authorize(Policy::DelegationCreate->value, [$delegated]);

        auth()->user()->delegate($delegated);
    }

    /**
     * Undo a delegation, attributing to the informed user the default role of
     * the common user of the application.
     *
     * @param \App\Models\User $delegated
     *
     * @return void
     */
    public function destroy(User $delegated)
    {
        $this->authorize(Policy::DelegationDelete->value, [$delegated]);

        $delegated->revokeDelegation();
    }
}
