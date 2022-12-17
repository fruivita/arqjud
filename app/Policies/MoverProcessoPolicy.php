<?php

namespace App\Policies;

use App\Models\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class MoverProcessoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode movimentar processos entre caixas/volumes.
     *
     * @param  \App\Models\Usuario  $usuario
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::MOVER_PROCESSO_CREATE);
    }
}
