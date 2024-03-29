<?php

namespace App\Pipes\Localidade;

use App\Pipes\OrderBase;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Order extends OrderBase
{
    /**
     * Aplica a ordenação por nome da localidade.
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
     * Aplica a ordenação pela quantidade de prédios da localidade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function prediosCount(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('predios_count', $direcao);
    }

    /**
     * Aplica a ordenação pela quantidade de caixas criadas pela localidade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function caixasCriadasCount(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('caixas_criadas_count', $direcao);
    }
}
