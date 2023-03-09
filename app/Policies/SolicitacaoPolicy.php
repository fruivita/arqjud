<?php

namespace App\Policies;

use App\Models\Permissao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class SolicitacaoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * Prisma do usuário do arquivo.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::SOLICITACAO_VIEW_ANY)
            && $usuario->habilitado();
    }

    /**
     * Determine whether the user can view any models.
     *
     * Prisma do usuário externo ao arquivo.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function externoViewAny(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::SOLICITACAO_EXTERNA_VIEW_ANY)
            && $usuario->habilitado();
    }

    /**
     * Determine whether the user can create models.
     *
     * Prisma do usuário do arquivo.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::SOLICITACAO_CREATE)
            && $usuario->habilitado();
    }

    /**
     * Determine whether the user can create models.
     *
     * Prisma do usuário externo ao arquivo criado a solicitação para sua
     * própria lotação.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function externoCreate(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE)
            && $usuario->habilitado();
    }

    /**
     * Determine whether the user can update the model.
     *
     * Prisma do usuário do arquivo.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::SOLICITACAO_UPDATE)
            && $usuario->habilitado();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Prisma do usuário do arquivo.
     * Só pode excluir solicitação no status solicitada.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Usuario $usuario, Solicitacao $solicitacao)
    {
        return $usuario->possuiPermissao(Permissao::SOLICITACAO_DELETE)
            && $usuario->habilitado()
            && is_null($solicitacao->entregue_em);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Prisma do usuário externo ao arquivo.
     * Só pode excluir solicitação no status solicitada e destinada à propria
     * lotação.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function externoDelete(Usuario $usuario, Solicitacao $solicitacao)
    {
        return ($usuario->possuiPermissao(Permissao::SOLICITACAO_EXTERNA_DELETE)
            && $usuario->habilitado()
            && $solicitacao->destino_id === $usuario->lotacao_id)
            && is_null($solicitacao->entregue_em);
    }
}
