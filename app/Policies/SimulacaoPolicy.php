<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class SimulacaoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode criar simulações de uso da aplicação.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(Usuario $usuario)
    {
        return
            session()->missing('simulado')
            && $usuario->possuiPermissao(Permissao::SimulacaoCreate);
    }

    /**
     * Determina se o usário pode excluir simulações de uso.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(Usuario $usuario)
    {
        return session()->has('simulador');
    }
}
