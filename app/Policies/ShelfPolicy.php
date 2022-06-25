<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class ShelfPolicy
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
        return $user->hasPermission(PermissionType::ShelfViewAny);
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
        return $user->hasPermission(PermissionType::ShelfView);
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
        return $user->hasPermission(PermissionType::ShelfCreate);
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
        return $user->hasPermission(PermissionType::ShelfUpdate);
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
     * @param \App\Models\Shelf $shelf
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Shelf $shelf)
    {
        if (isset($shelf->boxes_count) !== true) {
            $shelf->loadCount('boxes');
        }

        return
            $shelf->boxes_count === 0
            && $user->hasPermission(PermissionType::ShelfDelete);
    }
}
