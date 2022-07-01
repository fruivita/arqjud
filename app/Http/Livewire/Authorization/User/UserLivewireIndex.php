<?php

namespace App\Http\Livewire\Authorization\User;

use App\Enums\Policy;
use App\Http\Livewire\Traits\SalvaColunasDePreferencia;
use App\Http\Livewire\Traits\WithSearching;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithSorting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class UserLivewireIndex extends Component
{
    use AuthorizesRequests;
    use SalvaColunasDePreferencia;
    use WithFeedbackEvents;
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
     * Editing resource.
     *
     * @var \App\Models\User
     */
    public User $editing;

    /**
     * Avaiable roles.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $roles;

    /**
     * Should the modal for editing the resouce be displayed?
     *
     * @var bool
     */
    public $show_edit_modal = false;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'editing.role_id' => [
                'bail',
                'required',
                'exists:roles,id',
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
            'editing.role_id' => __('Role'),
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
        $this->authorize(Policy::ViewAnyOrUpdate->value, User::class);
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
        $this->editing = new User();
    }

    /**
     * Computed property to list paged users and their roles.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUsersProperty()
    {
        return
        User::with('delegator')
            ->whereLike(['name', 'username'], $this->term)
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
        return view('livewire.authorization.user.index')->layout('layouts.app');
    }

    /**
     * Displays the editing modal and defines the resource that will be edited.
     *
     * @param \App\Models\User $user
     *
     * @return void
     */
    public function edit(User $user)
    {
        $this->authorize(Policy::Update->value, $user);

        $this->editing = $user;

        $this->roles = Role::select('id', 'name')
                        ->avaiableToAssign()
                        ->orderBy('name', 'asc')
                        ->get();

        $this->show_edit_modal = true;
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->show_edit_modal !== true, 403);

        $this->authorize(Policy::Update->value, $this->editing);

        $this->validate();

        $saved = $this->editing->updateAndRevokeDelegatedUsers();

        $this->flashSelf($saved);
    }
}
