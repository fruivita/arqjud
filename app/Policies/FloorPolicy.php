<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Floor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class FloorPolicy
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
        return $user->hasPermission(PermissionType::FloorViewAny);
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
        return $user->hasPermission(PermissionType::FloorView);
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
        return $user->hasPermission(PermissionType::FloorCreate);
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
        return $user->hasPermission(PermissionType::FloorUpdate);
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
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User  $user
     * @param \App\Models\Floor $floor
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Floor $floor)
    {
        if (isset($floor->rooms_count) !== true) {
            $floor->loadCount('rooms');
        }

        return
            $floor->rooms_count === 0
            && $user->hasPermission(PermissionType::FloorDelete);
    }
}
