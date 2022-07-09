<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class ImportacaoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode realizar uma importação.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::ImportacaoCreate);
    }
}
