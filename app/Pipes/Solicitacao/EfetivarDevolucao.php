<?php

namespace App\Pipes\Solicitacao;

use App\Models\Processo;
use App\Models\Solicitacao;
use Illuminate\Support\Facades\Auth;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class EfetivarDevolucao
{
    /**
     * Efetiva a devolução do processo ao arquivo por meio da atualização das
     * propriedades pertinentes da solicitação.
     *
     * @return \stdClass
     */
    public function handle(\stdClass $devolucao, \Closure $next)
    {
        $solicitacao = Solicitacao::query()
            ->with(['solicitante'])
            ->entregues()
            ->whereBelongsTo(Processo::firstWhere('numero', $devolucao->processo), 'processo')
            ->first();

        $solicitacao->devolvida_em = now();
        $solicitacao->rearquivador_id = Auth::id();
        $solicitacao->save();

        $devolucao->devolvido_em = $solicitacao->devolvida_em;
        $devolucao->solicitante = $solicitacao->solicitante;

        return $next($devolucao);
    }
}
