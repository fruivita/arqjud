<?php

namespace App\Models;

use FruiVita\Corporate\Models\Department as CorporateDepartment;

/**
 * Department for a given user.
 *
 * @see https://laravel.com/docs/eloquent
 */
class Department extends CorporateDepartment
{
    /**
     * Department ID of users with no department. As a rule, users that exist
     * only on the LDAP server.
     *
     * @var int
     */
    public const DEPARTMENTLESS = 0;
}
