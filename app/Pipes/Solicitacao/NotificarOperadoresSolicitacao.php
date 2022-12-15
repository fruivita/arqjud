<?php

namespace App\Pipes\Solicitacao;

use App\Enums\Queue;
use App\Jobs\NotificarOperadoresSolicitacao as JobNotificarOperadoresSolicitacao;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class NotificarOperadoresSolicitacao
{
    /**
     * Dispara o job responsável por notificar os usuários de perfil operador
     * acerca da solicitação de processos feita pelo usuário.
     *
     * @param  \stdClass  $query
     * @param  \Closure  $next
     * @return \stdClass
     */
    public function handle(\stdClass $solicitacao, \Closure $next)
    {
        JobNotificarOperadoresSolicitacao::dispatch($solicitacao)->onQueue(Queue::Baixa->value);

        return $next($solicitacao);
    }
}
