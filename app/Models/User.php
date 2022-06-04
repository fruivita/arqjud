<?php

namespace App\Models;

use App\Enums\PermissionType;
use FruiVita\Corporate\Models\User as CorporateUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

/**
 * Users are synchronized with the LDAP server.
 *
 * @see https://laravel.com/docs/eloquent
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database
 */
class User extends CorporateUser implements LdapAuthenticatable
{
    use AuthenticatesWithLdap;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'guid',
        'domain',
        'department_id',
        'occupation_id',
        'duty_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var string[]
     */
    protected $with = ['role'];

    /**
     * User's role.
     *
     * Relationship user (N:1) role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * User's old role, that is, before delegation. Used to return to the
     * previous role.
     *
     * Relationship user (N:1) role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function oldRole()
    {
        return $this->belongsTo(Role::class, 'old_role_id', 'id');
    }

    /**
     * Check if the user's role was obtained by delegation or if it is an
     * original role.
     *
     * @return bool true if by delegation or false otherwise
     */
    public function roleByDelegation()
    {
        return ! is_null($this->role_granted_by);
    }

    /**
     * User who delegated the role to someone else.
     *
     * Relationship delegatedUsers (N:1) delegator.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delegator()
    {
        return $this->belongsTo(User::class, 'role_granted_by', 'id');
    }

    /**
     * Users with roles delegated by someone else.
     *
     * Relationship delegator (1:N) delegatedUsers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function delegatedUsers()
    {
        return $this->hasMany(User::class, 'role_granted_by', 'id');
    }

    /**
     * Returns the authenticated user for screen display.
     *
     * @return string
     */
    public function forHumans()
    {
        return $this->username;
    }

    /**
     * Delegate role to the informed user.
     *
     * @param \App\Models\User $delegated
     *
     * @return bool
     */
    public function delegate(User $delegated)
    {
        $delegated
            ->delegator()
            ->associate($this);
        $delegated
            ->oldRole()
            ->associate($delegated->role);
        $delegated
            ->role()
            ->associate($this->role);

        return $delegated->save();
    }

    /**
     * Revokes the user's role delegation, restoring his previous role.
     *
     * @return bool
     */
    public function revokeDelegation()
    {
        $this
        ->role()->associate($this->old_role_id)
        ->oldRole()->dissociate()
        ->delegator()->dissociate()
        ->save();
    }

    /**
     * Revokes delegations made by the user restoring the previous role of each
     * user.
     *
     * @return void
     */
    private function revokeDelegatedUsers()
    {
        $this->delegatedUsers()->get()->each(function ($user) {
            $user->role_id = $user->old_role_id;
            $user->role_granted_by = null;
            $user->old_role_id = null;
            $user->save();
        });
    }

    /**
     * Updates user properties and removes their delegations.
     *
     * @return bool
     */
    public function updateAndRevokeDelegatedUsers()
    {
        try {
            DB::beginTransaction();

            $this
            ->delegator()->dissociate()
            ->oldRole()->dissociate()
            ->save();

            $this->revokeDelegatedUsers();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error(
                __('User update failed'),
                [
                    'exception' => $th,
                ]
            );

            return false;
        }
    }

    /**
     * Default ordering of the model.
     *
     * Order:
     * - 1º Name in alphabetical order asc
     * - 2º Names with null value
     * - Tiebreaker: username asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @see https://learnsql.com/blog/how-to-order-rows-with-nulls/
     */
    public function scopeDefaultOrder(Builder $query)
    {
        return $query
                ->orderByRaw('name IS NULL')
                ->orderBy('name', 'asc')
                ->orderBy('username', 'asc');
    }

    /**
     * Checks if the authenticated user is a super administrator.
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        $configuration = Configuration::find(Configuration::MAIN);

        return
            ! empty($configuration)
            && $configuration->superadmin === $this->username
            ? true
            : false;
    }

    /**
     * Returns the id of all user permissions.
     *
     * @return \Illuminate\Support\Collection
     */
    public function permissions()
    {
        if ($this->role === null) {
            $this->refresh();
        }

        $this->load(['role.permissions' => function ($query) {
            $query->select('id');
        }]);

        return $this->role->permissions->pluck('id');
    }

    /**
     * Checks if the user has the given permission.
     *
     * @param \App\Enums\PermissionType $permission
     *
     * @return bool
     */
    public function hasPermission(PermissionType $permission)
    {
        if ($this->role === null) {
            $this->refresh();
        }

        $this->load(['role.permissions' => function ($query) use ($permission) {
            $query->select('id')->where('id', $permission->value);
        }]);

        return
            $this->role instanceof Role
            && $this->role->permissions->isNotEmpty()
            && $this->role->permissions->first()->id === $permission->value
            ? true
            : false;
    }

    /**
     * Checks if the user has one of the given permissions.
     *
     * @param \App\Enums\PermissionType[] $permissions
     *
     * @return bool
     */
    public function hasAnyPermission(array $permissions)
    {
        if ($this->role === null) {
            $this->refresh();
        }

        $this->load(['role.permissions' => function ($query) use ($permissions) {
            $query->select('id')->whereIn('id', $permissions);
        }]);

        return
            $this->role instanceof Role
            && $this->role->permissions->isNotEmpty()
            && in_array(PermissionType::from($this->role->permissions->first()->id), $permissions)
            ? true
            : false;
    }

    /**
     * Records filtered by the term entered.
     *
     * The filter applies to the name and the username through the OR clause.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null                           $term
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeSearch(Builder $query, string $term = null)
    {
        return $query->when($term, function ($query, $term) {
            $query
                ->where('username', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }
}
