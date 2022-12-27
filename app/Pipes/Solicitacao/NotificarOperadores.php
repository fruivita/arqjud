<?php

namespace App\Pipes\Solicitacao;

use App\Enums\Queue;
use App\Jobs\NotificarOperadoresSolicitacao;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class NotificarOperadores
{
    /**
     * Dispara o job responsável por notificar os usuários de perfil operador
     * acerca da solicitação de processos feita pelo usuário.
     *
     * @param  \stdClass  $solicitacao
     * @param  \Closure  $next
     * @return \stdClass
     */
    public function handle(\stdClass $solicitacao, \Closure $next)
    {
        NotificarOperadoresSolicitacao::dispatch($solicitacao)->onQueue(Queue::Media->value);

        return $next($solicitacao);
    }
}
