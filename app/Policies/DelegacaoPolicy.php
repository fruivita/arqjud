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
     * Para delegar:
     * - delegante deve possuir perfil com poder maior que o delegado;
     * - perfil de ambos deve ser original, isto é, não pode ser um perfil
     * obtido por delegação;
     * - ambos precisam estar na mesma lotação válida;
     * - delegante deve possuir permissão específica para delegação.
     *
     * @param  \App\Models\Usuario  $delegante
     * @param  \App\Models\Usuario  $delegado
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Usuario $delegante, Usuario $delegado)
    {
        return
            // Ambos possuem lotação válida
            !empty($delegante->lotacao_id)
            && !empty($delegado->lotacao_id)
            // Ambos pertentem a mesma lotação
            && $delegante->lotacao_id === $delegado->lotacao_id
            && $delegante->perfilOriginal()
            && $delegado->perfilOriginal()
            && $delegante->possuiPermissao(Permissao::DELEGACAO_CREATE)
            && $delegante->perfilSuperior($delegado);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Para remover a delegação:
     * - o delegante deve possuir perfil com poder maior que o delegado;
     * - ambos precisam estar na mesma lotação válida.
     * - Delegante com permissão específica pode remover qualquer delegação.
     * - Delegante pode remover qualquer uma de suas delegações independente de
     * permissão.
     *
     * Não é possível remover delegação inexistente.
     *
     * @param  \App\Models\Usuario  $delegante
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(Usuario $delegante, Usuario $delegado)
    {
        return
            $delegado->perfilDelegado()
            && (
                // Perfil foi concedido pelo usuário autenticado
                $delegante->id === $delegado->perfil_concedido_por
                // Usuário com perfil superior na própria lotação
                || (
                    // Ambos possuem lotação válida
                    !empty($delegante->lotacao_id)
                    && !empty($delegado->lotacao_id)
                    // Ambos pertentem a mesma lotação
                    && $delegante->lotacao_id === $delegado->lotacao_id
                    && $delegante->perfilSuperior($delegado)
                )
                || $delegante->possuiPermissao(Permissao::DELEGACAO_DELETE)
            );
    }
}
