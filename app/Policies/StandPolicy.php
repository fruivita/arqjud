<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Stand;
use App\Models\User;

/**
 * @see https://laravel.com/docs/authorization
 */
class StandPolicy extends Policy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        return $this->hasAnyPermission($user, [PermissionType::StandViewAny]);
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
        return $this->hasAnyPermission($user, [PermissionType::StandView]);
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
        return $this->hasAnyPermission($user, [PermissionType::StandCreate]);
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
        return $this->hasAnyPermission($user, [PermissionType::StandUpdate]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Stand $stand
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Stand $stand)
    {
        return
            $stand->shelves_count === 0
            && $this->hasAnyPermission($user, [PermissionType::StandDelete]);
    }
}
