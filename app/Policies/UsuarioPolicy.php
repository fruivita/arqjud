<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class UsuarioPolicy
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
        return $usuario->possuiPermissao(Permissao::UsuarioViewAny);
    }

    /**
     * Determina se o usuário pode atualizar um modelo.
     *
     * @param \App\Models\Usuario      $usuario
     * @param \App\Models\Usuario|null $em_edicao
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(Usuario $usuario, Usuario $em_edicao = null)
    {
        return (
                // Está carregando a página
                $em_edicao === null
                // Está atualizando o usuário
                || $usuario->perfil_id >= $em_edicao->perfil_id
            )
            && $usuario->possuiPermissao(Permissao::UsuarioUpdate);
    }

    /**
     * Determina se o usuário pode visualisar quaisquer modelos or update a model.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAnyOrUpdate(Usuario $usuario)
    {
        return
        $this->viewAny($usuario)
        || $this->update($usuario);
    }
}
