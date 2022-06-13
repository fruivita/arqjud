<?php

namespace App\Http\Livewire\Authorization\Permission;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PermissionLivewireShow extends Component
{
    use AuthorizesRequests;
    use WithPerPagePagination;

    /**
     * Resource on display.
     *
     * @var \App\Models\Permission
     */
    public Permission $permission;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::View->value, Permission::class);
    }

    /**
     * Computed property to list the paginated roles.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRolesProperty()
    {
        return $this->applyPagination(
            $this->permission->roles()->defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.authorization.permission.show', [
            'roles' => $this->roles,
        ])->layout('layouts.app');
    }
}
