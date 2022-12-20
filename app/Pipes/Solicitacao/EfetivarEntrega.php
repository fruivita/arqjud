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
     * @param  \stdClass  $entrega
     * @param  \Closure  $next
     * @return \stdClass
     */
    public function handle(\stdClass $entrega, \Closure $next)
    {
        Solicitacao::query()
            ->whereIn('id', $entrega->solicitacoes)
            ->update([
                'recebedor_id' => $entrega->recebedor->id,
                'remetente_id' => $entrega->remetente->id,
                'entregue_em' => $entrega->guia->gerada_em,
                'por_guia' => $entrega->por_guia,
                'guia_id' => $entrega->guia->id,
            ]);

        return $next($entrega);
    }
}
