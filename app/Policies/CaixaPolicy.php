<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Caixa;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class CaixaPolicy
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
        return $usuario->possuiPermissao(Permissao::CaixaViewAny);
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
        return $usuario->possuiPermissao(Permissao::CaixaView);
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
        return $usuario->possuiPermissao(Permissao::CaixaCreate);
    }

    /**
     * Determina se o usuário pode criar múltiplos modelos de uma vez.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function createMany(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::CaixaCreateMany);
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
        return $usuario->possuiPermissao(Permissao::CaixaUpdate);
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
     * @param \App\Models\Caixa $caixa
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(Usuario $usuario, Caixa $caixa)
    {
        if (isset($caixa->volumes_count) !== true) {
            $caixa->loadCount('volumes');
        }

        return
            $caixa->volumes_count === 0
            && $usuario->possuiPermissao(Permissao::CaixaDelete);
    }
}
