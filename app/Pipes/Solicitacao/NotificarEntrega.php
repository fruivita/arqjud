<?php

namespace App\Pipes\Solicitacao;

use App\Enums\Queue;
use App\Jobs\NotificarEntrega as JobNotificarEntrega;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class NotificarEntrega
{
    /**
     * Dispara o job responsável por notificar os usuários acerca da entrega de
     * processos solicitados.
     *
     * @return \stdClass
     */
    public function handle(\stdClass $entrega, \Closure $next)
    {
        JobNotificarEntrega::dispatch($entrega)->onQueue(Queue::Media->value);

        return $next($entrega);
    }
}
