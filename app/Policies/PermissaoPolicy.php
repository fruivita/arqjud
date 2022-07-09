<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class PermissaoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualisar quaisquer modelos.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::PermissaoViewAny);
    }

    /**
     * Determina se o usuário pode visualisar um modelo.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function view(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::PermissaoView);
    }

    /**
     * Determina se o usuário pode atualizar um modelo.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::PermissaoUpdate);
    }

    /**
     * Determina se o usuário pode visualizar ou atualizar um modelo.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewOrUpdate(Usuario $usuario)
    {
        return
        $this->view($usuario)
        || $this->update($usuario);
    }
}
