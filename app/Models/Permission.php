<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Permission extends Model
{
    use HasEagerLimit;
    use HasFactory;

    protected $table = 'permissions';

    public $incrementing = false;

    /**
     * Relationship permission (M:N) role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id')->withTimestamps();
    }

    /**
     * Default ordering of the model.
     *
     * ORder: Id asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder(Builder $query)
    {
        return $query->orderBy('id', 'asc');
    }

    /**
     * Saves the permission in the database and syncs the roles in an atomic
     * operation i.e. all or nothing.
     *
     * @param array|int|null $roles roles id
     *
     * @return bool
     */
    public function atomicSaveWithRoles(mixed $roles)
    {
        try {
            DB::transaction(function () use ($roles) {
                $this->save();

                $this->roles()->sync($roles);
            });

            return true;
        } catch (\Throwable $th) {
            Log::error(
                __('Permission update failed'),
                [
                    'roles' => $roles,
                    'exception' => $th,
                ]
            );

            return false;
        }
    }
}
