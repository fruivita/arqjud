<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class DelegacaoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar quaisquer delegações de sua lotação.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::DelegacaoViewAny);
    }

    /**
     * Determina se o usuário pode delegar o seu perfil.
     *
     * Para delegar, o delegante precisa ter perfil maior que o delegado,
     * ambos precisam possuir a mesma lotação e deve possuir permissão
     * específica para delegação.
     *
     * @param \App\Models\Usuario $delegante
     * @param \App\Models\Usuario $delegado usuário que receberá a delegação.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(Usuario $delegante, Usuario $delegado)
    {
        return
            $delegante->perfilPorDelegacao() === false
            // delegante possui perfil maior que o delegado
            && $delegante->perfil_id > $delegado->perfil_id
            // mesma lotação
            && $delegante->lotacao_id == $delegado->lotacao_id
            && $delegante->possuiPermissao(Permissao::DelegacaoCreate);
    }

    /**
     * Determina se o usuário pode remover a delegação.
     *
     * Para remover, o usúario que irá fazê-la deve ter perfil igual ou maior
     * que o delegado e ambos devem possuir a mesma lotação.
     *
     * @param \App\Models\Usuario $delegante
     * @param \App\Models\Usuario $delegado
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(Usuario $delegante, Usuario $delegado)
    {
        return
            $delegado->perfilPorDelegacao()
            // usuário autenticado possui perfil igual ou maior que o delegado
            && $delegante->perfil_id >= $delegado->perfil_id
            // mesma lotação
            && $delegante->lotacao_id == $delegado->lotacao_id;
    }
}
