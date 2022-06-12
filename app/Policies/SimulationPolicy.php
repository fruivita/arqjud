<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class SimulationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create simulations.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user)
    {
        return
            session()->missing('simulated')
            && $user->hasPermission(PermissionType::SimulationCreate);
    }

    /**
     * Determine whether the user can delete a simulation.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user)
    {
        return session()->has('simulator');
    }
}
