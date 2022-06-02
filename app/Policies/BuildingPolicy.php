<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Building;
use App\Models\User;

/**
 * @see https://laravel.com/docs/authorization
 */
class BuildingPolicy extends Policy
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
        return $this->hasAnyPermission($user, [PermissionType::BuildingViewAny]);
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
        return $this->hasAnyPermission($user, [PermissionType::BuildingView]);
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
        return $this->hasAnyPermission($user, [PermissionType::BuildingCreate]);
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
        return $this->hasAnyPermission($user, [PermissionType::BuildingUpdate]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Building $building
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Building $building)
    {
        return
            $building->floors_count === 0
            && $this->hasAnyPermission($user, [PermissionType::BuildingDelete]);
    }
}
