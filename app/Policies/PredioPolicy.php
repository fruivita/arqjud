<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Predio;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class PredioPolicy
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
        return $usuario->possuiPermissao(Permissao::PredioViewAny);
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
        return $usuario->possuiPermissao(Permissao::PredioView);
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
        return $usuario->possuiPermissao(Permissao::PredioCreate);
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
        return $usuario->possuiPermissao(Permissao::PredioUpdate);
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
     * @param \App\Models\Predio  $predio
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(Usuario $usuario, Predio $predio)
    {
        if (isset($predio->andares_count) !== true) {
            $predio->loadCount('andares');
        }

        return
            $predio->andares_count === 0
            && $usuario->possuiPermissao(Permissao::PredioDelete);
    }
}
