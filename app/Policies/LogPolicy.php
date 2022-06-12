<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class LogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any log files.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission(PermissionType::LogViewAny);
    }

    /**
     * Determine whether the user can delete any log files.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user)
    {
        return $user->hasPermission(PermissionType::LogDelete);
    }

    /**
     * Determine whether the user can download any log file.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function download(User $user)
    {
        return $user->hasPermission(PermissionType::LogDownload);
    }
}
