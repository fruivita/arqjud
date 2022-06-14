<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Box;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class BoxPolicy
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
        return $user->hasPermission(PermissionType::BoxViewAny);
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
        return $user->hasPermission(PermissionType::BoxView);
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
        return $user->hasPermission(PermissionType::BoxCreate);
    }

    /**
     * Determine whether the user can create many models at once.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function createMany(User $user)
    {
        return $user->hasPermission(PermissionType::BoxCreateMany);
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
        return $user->hasPermission(PermissionType::BoxUpdate);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Box $box
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Box $box)
    {
        if (isset($box->volumes_count) !== true) {
            $box->loadCount('volumes');
        }

        return
            $box->volumes_count === 0
            && $user->hasPermission(PermissionType::BoxDelete);
    }
}
