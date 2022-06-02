<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Room;
use App\Models\User;

/**
 * @see https://laravel.com/docs/authorization
 */
class RoomPolicy extends Policy
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
        return $this->hasAnyPermission($user, [PermissionType::RoomViewAny]);
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
        return $this->hasAnyPermission($user, [PermissionType::RoomView]);
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
        return $this->hasAnyPermission($user, [PermissionType::RoomCreate]);
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
        return $this->hasAnyPermission($user, [PermissionType::RoomUpdate]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Room $room
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Room $room)
    {
        return
            $room->boxes_count === 0
            && $this->hasAnyPermission($user, [PermissionType::RoomDelete]);
    }
}
