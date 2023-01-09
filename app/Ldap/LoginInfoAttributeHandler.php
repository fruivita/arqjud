<?php

namespace App\Ldap;

use App\Models\Usuario;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

/**
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database/configuration/#attribute-handlers
 */
class LoginInfoAttributeHandler
{
    /**
     * Armazena os metadados de login do usuário.
     *
     * @param  \LdapRecord\Models\ActiveDirectory\User  $ldap
     * @param  \App\Models\Usuario  $usuario
     * @return void
     */
    public function handle(LdapUser $ldap, Usuario $usuario)
    {
        $usuario->ip = request()->ip();
        $usuario->ultimo_login = now();
    }
}
