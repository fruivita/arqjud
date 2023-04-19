<?php

namespace App\Pipes\Solicitacao;

use App\Models\Processo;
use App\Models\Solicitacao;
use Illuminate\Support\Facades\Auth;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class RegistrarNotificacao
{
    /**
     * Registra a data da notificação do solicitante acerca da disponibilização
     * do processo solicitado.
     *
     * @return \stdClass
     */
    public function handle(\stdClass $notificar, \Closure $next)
    {
        $solicitacao = Solicitacao::query()
            ->with(['solicitante'])
            ->solicitadas()
            ->whereBelongsTo(Processo::firstWhere('numero', $notificar->processo), 'processo')
            ->first();

        $solicitacao->notificado_em = now();
        $solicitacao->save();

        $notificar->solicitante = $solicitacao->solicitante;

        return $next($notificar);
    }
}
