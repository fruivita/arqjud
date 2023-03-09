<?php

namespace App\Pipes\Solicitacao;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class ExcluirSolicitacao
{
    /**
     * Exclui por pipe uma solicitação
     *
     * @return \stdClass
     */
    public function handle(\stdClass $solicitacao, \Closure $next)
    {
        $solicitacao->processo = $solicitacao->model->processo->numero;
        $solicitacao->solicitante = $solicitacao->model->solicitante;
        $solicitacao->destino = $solicitacao->model->destino;
        $solicitacao->solicitada_em = $solicitacao->model->solicitada_em;
        $solicitacao->operador = auth()->user();
        $solicitacao->cancelada_em = now();

        $solicitacao->model->delete();

        return $next($solicitacao);
    }
}
