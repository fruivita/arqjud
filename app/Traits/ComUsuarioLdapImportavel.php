<?php

namespace App\Traits;

use App\Models\Usuario;
use Illuminate\Support\Facades\Artisan;

/**
 * Trait para importar usuários do servidor LDAP.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 */
trait ComUsuarioLdapImportavel
{
    /**
     * Importa o usuário do servidor LDAP para o database da aplicação e o
     * retorna como um usúario da aplicação.
     *
     * A importação se opera a partir de seu **samaccountname**.
     *
     * @param string $username
     *
     * @return \App\Models\Usuario|null
     */
    private function importarUsuarioLdap(string $username)
    {
        Artisan::call('ldap:import', [
            'provider' => 'users',
            '--no-interaction',
            '--filter' => "(samaccountname={$username})",
            '--attributes' => 'cn,samaccountname',
        ]);

        return Usuario::where('username', $username)->first();
    }
}
