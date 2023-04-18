<?php

namespace App\Pipes\Solicitacao;

use App\Models\Processo;
use App\Models\Solicitacao;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class SolicitarProcesso
{
    /**
     * Cria por pipe a solicitação dos processos
     *
     * @return \stdClass
     */
    public function handle(\stdClass $solicitacao, \Closure $next)
    {
        $solicitacao->solicitada_em = now();

        Processo::query()
            ->whereIn('numero', $solicitacao->processos)
            ->get()
            ->each(function (Processo $processo) use ($solicitacao) {
                Solicitacao::create([
                    'processo_id' => $processo->id,
                    'solicitante_id' => $solicitacao->solicitante->id,
                    'destino_id' => $solicitacao->destino->id,
                    'solicitada_em' => $solicitacao->solicitada_em,
                    'por_guia' => false,
                ]);
            });

        return $next($solicitacao);
    }
}
