<?php

namespace App\Pipes\Solicitacao;

use App\Models\Solicitacao;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class EfetivarEntrega
{
    /**
     * Efetiva a entrega dos processos solcitados por meio da atualização das
     * propriedades necessárias da solicitação.
     *
     * @return \stdClass
     */
    public function handle(\stdClass $entrega, \Closure $next)
    {
        Solicitacao::query()
            ->whereIn('id', $entrega->solicitacoes)
            ->get()
            ->each(function (Solicitacao $solicitacao) use ($entrega) {
                $solicitacao->entregue_em = $entrega->guia->gerada_em;
                $solicitacao->por_guia = $entrega->por_guia;
                $solicitacao
                    ->recebedor()->associate($entrega->recebedor)
                    ->remetente()->associate($entrega->remetente)
                    ->guia()->associate($entrega->guia)
                    ->save();
            });

        return $next($entrega);
    }
}
