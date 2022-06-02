<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Site;
use App\Models\User;

/**
 * @see https://laravel.com/docs/authorization
 */
class SitePolicy extends Policy
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
        return $this->hasAnyPermission($user, [PermissionType::SiteViewAny]);
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
        return $this->hasAnyPermission($user, [PermissionType::SiteView]);
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
        return $this->hasAnyPermission($user, [PermissionType::SiteCreate]);
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
        return $this->hasAnyPermission($user, [PermissionType::SiteUpdate]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Site $site
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Site $site)
    {
        return
            $site->buildings_count === 0
            && $this->hasAnyPermission($user, [PermissionType::SiteDelete]);
    }
}
