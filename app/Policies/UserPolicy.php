<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class UserPolicy
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
        return $user->hasPermission(PermissionType::UserViewAny);
    }

    /**
     * Determine whether the user can update a model.
     *
     * @param \App\Models\User      $user
     * @param \App\Models\User|null $editing
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(User $user, User $editing = null)
    {
        return (
                $editing === null // Loading the page
                || $user->role_id >= $editing->role_id // performing the update
            )
            && $user->hasPermission(PermissionType::UserUpdate);
    }

    /**
     * Determine whether the user can view any models or update a model.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAnyOrUpdate(User $user)
    {
        return
        $this->viewAny($user)
        || $this->update($user);
    }
}
