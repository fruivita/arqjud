<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Sala;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class SalaPolicy
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
        return $usuario->possuiPermissao(Permissao::SalaViewAny);
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
        return $usuario->possuiPermissao(Permissao::SalaView);
    }

    /**
     * Determina se o usuário pode criar modelos.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::SalaCreate);
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
        return $usuario->possuiPermissao(Permissao::SalaUpdate);
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

    /**
     * Determina se o usuário pode excluir o modelo.
     *
     * @param \App\Models\Usuario $usuario
     * @param \App\Models\Sala $sala
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(Usuario $usuario, Sala $sala)
    {
        if (isset($sala->estantes_count) !== true) {
            $sala->loadCount('estantes');
        }

        return
            $sala->estantes_count === 0
            && $usuario->possuiPermissao(Permissao::SalaDelete);
    }
}
