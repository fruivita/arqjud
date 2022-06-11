<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Floor;
use App\Models\User;

/**
 * @see https://laravel.com/docs/authorization
 */
class FloorPolicy extends Policy
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
        return $this->hasAnyPermission($user, [PermissionType::FloorViewAny]);
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
        return $this->hasAnyPermission($user, [PermissionType::FloorView]);
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
        return $this->hasAnyPermission($user, [PermissionType::FloorCreate]);
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
        return $this->hasAnyPermission($user, [PermissionType::FloorUpdate]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User  $user
     * @param \App\Models\Floor $floor
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Floor $floor)
    {
        return
            $floor->loadCount('rooms')->rooms_count === 0
            && $this->hasAnyPermission($user, [PermissionType::FloorDelete]);
    }
}
