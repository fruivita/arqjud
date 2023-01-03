<?php

namespace App\Policies;

use App\Models\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class DelegacaoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * Para delegar, o delegante deve possuir perfil com poder maior que o
     * delegado, ambos precisam possuir a mesma lotação válida e deve possuir
     * permissão específica para delegação.
     *
     * @param  \App\Models\Usuario  $usuario
     * @param  \App\Models\Usuario  $delegado
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Usuario $usuario, Usuario $delegado)
    {
        return
            // Ambos possuem lotação válida
            !empty($usuario->lotacao_id)
            && !empty($delegado->lotacao_id)
            // Ambos pertentem a mesma lotação
            && $usuario->lotacao_id === $delegado->lotacao_id
            && $usuario->possuiPermissao(Permissao::DELEGACAO_CREATE)
            && $usuario->perfilSuperior($delegado);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Para remover a delegação, o usuário deve possuir perfil com poder maior
     * que o delegado e ambos devem possuir a mesma lotação.
     *
     * O usuário com permissão específica pode remover qualquer delegação.
     *
     * O delegante pode remover qualquer uma de suas delegações independente de
     * permissão.
     *
     * @param  \App\Models\Usuario  $usuario
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(Usuario $usuario, Usuario $delegado)
    {
        return
            // Perfil foi concedido pelo usuário autenticado
            $usuario->id === $delegado->perfil_concedido_por
            // Usuário com perfil superior na própria lotação
            || (
                // Ambos possuem lotação válida
                !empty($usuario->lotacao_id)
                && !empty($delegado->lotacao_id)
                // Ambos pertentem a mesma lotação
                && $usuario->lotacao_id === $delegado->lotacao_id
                && $usuario->perfilSuperior($delegado)
            )
            || $usuario->possuiPermissao(Permissao::DELEGACAO_DELETE);
    }
}
