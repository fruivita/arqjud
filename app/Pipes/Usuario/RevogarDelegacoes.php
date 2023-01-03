<?php

namespace App\Pipes\Usuario;

use App\Models\Usuario;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class RevogarDelegacoes
{
    /**
     * Revoga por pipe todas as delegações feitas pelo usuário restaurando o
     * perfil antigo de cada usuário.
     *
     * @param  \App\Models\Usuario  $usuario
     * @param  \Closure  $next
     * @return \App\Models\Usuario
     */
    public function handle(Usuario $usuario, \Closure $next)
    {
        $usuario
            ->delegados()
            ->get()
            ->each(fn (Usuario $delegado) => $delegado->revogaDelegacao());

        return $next($usuario);
    }
}
