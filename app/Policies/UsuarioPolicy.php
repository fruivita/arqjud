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
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::USUARIO_VIEW_ANY);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::USUARIO_VIEW);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(Usuario $usuario, Usuario $em_edicao)
    {
        return
            //Não pode atualizar a si mesmo
            $usuario->id != $em_edicao->id
            // usuário autenticado deve possuir um perfil válido
            && $usuario->perfil_id >= 1
            && $usuario->possuiPermissao(Permissao::USUARIO_UPDATE)
            && $em_edicao->pertenceLotacaoAdministravel();
    }

    /**
     * Determina se o usuário pode visualizar ou atualizar um modelo.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewOrUpdate(Usuario $usuario, Usuario $em_edicao)
    {
        return
            $this->view($usuario)
            || $this->update($usuario, $em_edicao);
    }
}
