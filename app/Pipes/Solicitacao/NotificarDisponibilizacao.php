<?php

namespace App\Pipes\Solicitacao;

use App\Enums\Queue;
use App\Jobs\NotificarSolicitanteProcessoDisponivel;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class NotificarDisponibilizacao
{
    /**
     * Dispara o job responsável por notificar o solicitante acerca da
     * disponibilização para retirada do processo por ele solicitado.
     *
     * @return \stdClass
     */
    public function handle(\stdClass $notificar, \Closure $next)
    {
        NotificarSolicitanteProcessoDisponivel::dispatch($notificar)->onQueue(Queue::Media->value);

        return $next($notificar);
    }
}
