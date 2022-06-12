<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class DelegationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any delegations in their department.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission(PermissionType::DelegationViewAny);
    }

    /**
     * Determine whether the user can delegate his role.
     *
     * @param \App\Models\User $user
     * @param \App\Models\User $delegated
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user, User $delegated)
    {
        return
            $user->roleByDelegation() === false
            // authenticated user has more permissions than the receiver
            && $user->role_id > $delegated->role_id
            // same department
            && $user->department_id == $delegated->department_id
            && $user->hasPermission(PermissionType::DelegationCreate);
    }

    /**
     * Determine whether the user can revoke his delegation.
     *
     * @param \App\Models\User $user
     * @param \App\Models\User $delegated
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, User $delegated)
    {
        return
            $delegated->roleByDelegation()
            // authenticated user has more or the same level of permissions as the receiver
            && $user->role_id >= $delegated->role_id
            // same department
            && $user->department_id == $delegated->department_id;
    }
}
