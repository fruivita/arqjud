<?php

namespace App\Pipes\TipoProcesso;

use App\Pipes\OrderBase;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Order extends OrderBase
{
    /**
     * Aplica a ordenação por nome do Tipo de Processo.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function nome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('nome', $direcao);
    }

    /**
     * Aplica a ordenação pela quantidade de caixas de determinado Tipo de
     * Processo.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function caixasCount(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('caixas_count', $direcao);
    }
}
