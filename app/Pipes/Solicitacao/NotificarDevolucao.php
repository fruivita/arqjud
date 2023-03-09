<?php

namespace App\Pipes\Solicitacao;

use App\Enums\Queue;
use App\Jobs\NotificarSolicitanteDevolucao;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class NotificarDevolucao
{
    /**
     * Dispara o job responsável por notificar o solicitante acerca da
     * devolução ao arquivo do processo por ele solicitado.
     *
     * @return \stdClass
     */
    public function handle(\stdClass $devolucao, \Closure $next)
    {
        NotificarSolicitanteDevolucao::dispatch($devolucao)->onQueue(Queue::Media->value);

        return $next($devolucao);
    }
}
