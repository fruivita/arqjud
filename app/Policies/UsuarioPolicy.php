<?php

namespace App\Policies;

use App\Models\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class UsuarioPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::USUARIO_VIEW_ANY);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Usuario  $usuario
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::USUARIO_VIEW);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\Usuario $usuario
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(Usuario $usuario)
    {
        return $usuario->perfil_id >= 1
            && $usuario->possuiPermissao(Permissao::USUARIO_UPDATE);
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
