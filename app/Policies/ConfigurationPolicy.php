<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class ConfigurationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view a model.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function view(User $user)
    {
        return $user->hasPermission(PermissionType::ConfigurationView);
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
        return $user->hasPermission(PermissionType::ConfigurationUpdate);
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
}
