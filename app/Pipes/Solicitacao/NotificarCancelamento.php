<?php

namespace App\Pipes\Solicitacao;

use App\Enums\Queue;
use App\Jobs\NotificarSolicitanteCancelamento;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class NotificarCancelamento
{
    /**
     * Dispara o job responsável por notificar o solicitante acerca do
     * cancelamento de sua solicitação de processo.
     *
     * @param  \stdClass  $solicitacao
     * @param  \Closure  $next
     * @return \stdClass
     */
    public function handle(\stdClass $solicitacao, \Closure $next)
    {
        NotificarSolicitanteCancelamento::dispatch($solicitacao)->onQueue(Queue::Media->value);

        return $next($solicitacao);
    }
}
