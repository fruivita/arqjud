<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class SitePolicy
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
        return $user->hasPermission(PermissionType::SiteViewAny);
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
        return $user->hasPermission(PermissionType::SiteView);
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
        return $user->hasPermission(PermissionType::SiteCreate);
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
        return $user->hasPermission(PermissionType::SiteUpdate);
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
     * @param \App\Models\User $user
     * @param \App\Models\Site $site
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Site $site)
    {
        if (isset($site->buildings_count) !== true) {
            $site->loadCount('buildings');
        }

        return
            $site->buildings_count === 0
            && $user->hasPermission(PermissionType::SiteDelete);
    }
}
