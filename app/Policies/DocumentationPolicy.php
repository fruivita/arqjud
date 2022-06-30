<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class DocumentationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission(PermissionType::DocumentationViewAny);
    }

    /**
     * Determine whether the user can view a model.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function view(User $user)
    {
        return $user->hasPermission(PermissionType::DocumentationView);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user)
    {
        return $user->hasPermission(PermissionType::DocumentationCreate);
    }

    /**
     * Determine whether the user can update a model.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(User $user)
    {
        return $user->hasPermission(PermissionType::DocumentationUpdate);
    }

    /**
     * Determine whether the user can view or update a model.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewOrUpdate(User $user)
    {
        return
        $this->view($user)
        || $this->update($user);
    }

    /**
     * Determine whether the user can delete a model.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user)
    {
        return $user->hasPermission(PermissionType::DocumentationDelete);
    }
}
