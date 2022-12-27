<?php

namespace App\Pipes\Solicitacao;

use App\Enums\Queue;
use App\Jobs\NotificarSolicitanteSolicitacao;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class NotificarSolicitante
{
    /**
     * Dispara o job responsável por notificar o solicitante acerca de
     * solicitação de processos feita em seu nome.
     *
     * @param  \stdClass  $solicitacao
     * @param  \Closure  $next
     * @return \stdClass
     */
    public function handle(\stdClass $solicitacao, \Closure $next)
    {
        NotificarSolicitanteSolicitacao::dispatch($solicitacao)->onQueue(Queue::Media->value);

        return $next($solicitacao);
    }
}
