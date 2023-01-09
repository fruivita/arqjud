<?php

namespace App\Ldap;

use App\Models\Perfil;
use App\Models\Usuario;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

/**
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database/configuration/#attribute-handlers
 */
class PerfilAttributeHandler
{
    /**
     * Definição do perfil padrão do usuário quando da primeira autenticação.
     *
     * @param  \LdapRecord\Models\ActiveDirectory\User  $ldap
     * @param  \App\Models\Usuario  $usuario
     * @return void
     */
    public function handle(LdapUser $ldap, Usuario $usuario)
    {
        if (empty($usuario->perfil_id)) {
            $usuario->perfil_id = Perfil::firstWhere('slug', Perfil::PADRAO)->id;
        }
    }
}
