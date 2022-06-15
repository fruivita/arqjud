<?php

namespace App\Http\Livewire\Authorization\User;

use App\Enums\Policy;
use App\Http\Livewire\Traits\Searchable;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
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
    use Searchable;
    use WithFeedbackEvents;
    use WithPerPagePagination;

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
                'integer',
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
        $this->authorize(Policy::ViewAny->value, User::class);
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
        return $this->applyPagination(
            User::with('delegator')
            ->whereLike(['name', 'username'], $this->term)
            ->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.user.index', [
            'users' => $this->users,
        ])->layout('layouts.app');
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
                        ->defaultOrder()
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
        $this->validate();

        $this->authorize(Policy::Update->value, $this->editing);

        $saved = $this->editing->updateAndRevokeDelegatedUsers();

        $this->flashSelf($saved);
    }
}
