<?php

namespace App\Pipes\Solicitacao;

use App\Events\ProcessoSolicitadoPeloUsuario;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class NotificarOperadores
{
    /**
     * Dispara evento responsável por notificar os usuários de perfil operador
     * acerca da solicitação de processos feita pelo usuário.
     *
     * @param  \stdClass  $query
     * @param  \Closure  $next
     * @return \stdClass
     */
    public function handle(\stdClass $solicitacao, \Closure $next)
    {
        ProcessoSolicitadoPeloUsuario::dispatch($solicitacao);

        return $next($solicitacao);
    }
}
