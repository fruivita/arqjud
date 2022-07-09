<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Prateleira;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class PrateleiraPolicy
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
        return $usuario->possuiPermissao(Permissao::PrateleiraViewAny);
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
        return $usuario->possuiPermissao(Permissao::PrateleiraView);
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
        return $usuario->possuiPermissao(Permissao::PrateleiraCreate);
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
        return $usuario->possuiPermissao(Permissao::PrateleiraUpdate);
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
     * @param \App\Models\Usuario    $usuario
     * @param \App\Models\Prateleira $prateleira
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(Usuario $usuario, Prateleira $prateleira)
    {
        if (isset($prateleira->caixas_count) !== true) {
            $prateleira->loadCount('caixas');
        }

        return
            $prateleira->caixas_count === 0
            && $usuario->possuiPermissao(Permissao::PrateleiraDelete);
    }
}
