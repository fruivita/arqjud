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
     * @param  \App\Models\Usuario  $usuario
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
     * @param  \App\Models\Usuario  $usuario
     * @param  \App\Models\Usuario  $em_edicao
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(Usuario $usuario, Usuario $em_edicao)
    {
        return
            //Não pode atualizar a si mesmo
            $usuario->id != $em_edicao->id
            // usuário autenticado deve possuir um perfil válido
            && $usuario->perfil_id >= 1
            && $usuario->possuiPermissao(Permissao::USUARIO_UPDATE);
    }

    /**
     * Determina se o usuário pode visualizar ou atualizar um modelo.
     *
     * @param  \App\Models\Usuario  $usuario
     * @param  \App\Models\Usuario  $em_edicao
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewOrUpdate(Usuario $usuario, Usuario $em_edicao)
    {
        return
            $this->view($usuario)
            || $this->update($usuario, $em_edicao);
    }
}
