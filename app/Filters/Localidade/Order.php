<?php

namespace App\Filters\Localidade;

use App\Filters\OrderBase;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Order extends OrderBase
{
    /**
     * Aplica a ordenação por nome da localidade.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direcao asc ou desc
     * @return void
     */
    protected function nome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('nome', $direcao);
    }

    /**
     * Aplica a ordenação pela quantidade de prédios da localidade.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direcao asc ou desc
     * @return void
     */
    protected function prediosCount(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('predios_count', $direcao);
    }
}
