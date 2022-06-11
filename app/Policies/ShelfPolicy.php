<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Shelf;
use App\Models\User;

/**
 * @see https://laravel.com/docs/authorization
 */
class ShelfPolicy extends Policy
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
        return $this->hasAnyPermission($user, [PermissionType::ShelfViewAny]);
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
        return $this->hasAnyPermission($user, [PermissionType::ShelfView]);
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
        return $this->hasAnyPermission($user, [PermissionType::ShelfCreate]);
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
        return $this->hasAnyPermission($user, [PermissionType::ShelfUpdate]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Shelf $shelf
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Shelf $shelf)
    {
        return
            $shelf->loadCount('boxes')->boxes_count === 0
            && $this->hasAnyPermission($user, [PermissionType::ShelfDelete]);
    }
}
