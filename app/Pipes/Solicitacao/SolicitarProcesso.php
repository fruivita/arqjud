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
     * @param  \stdClass  $solicitacao
     * @param  \Closure  $next
     * @return \stdClass
     */
    public function handle(\stdClass $solicitacao, \Closure $next)
    {
        $solicitacao->solicitada_em = now();

        $solicitacoes = Processo::query()
            ->whereIn('numero', $solicitacao->processos)
            ->lazy()
            ->map(function (Processo $processo) use ($solicitacao) {
                return [
                    'processo_id' => $processo->id,
                    'solicitante_id' => $solicitacao->solicitante->id,
                    'destino_id' => $solicitacao->destino->id,
                    'solicitada_em' => $solicitacao->solicitada_em,
                    'por_guia' => false,
                    'created_at' => $solicitacao->solicitada_em,
                    'updated_at' => $solicitacao->solicitada_em,
                ];
            });

        Solicitacao::insert($solicitacoes->toArray());

        return $next($solicitacao);
    }
}
