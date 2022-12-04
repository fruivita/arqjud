<?php

namespace App\Filters\Predio;

use App\Filters\OrderBase;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pressupõe join com as tabelas pais.
 *
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Order extends OrderBase
{
    /**
     * Aplica a ordenação pelo nome do prédio.
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
     * Aplica a ordenação pela quantidade de andares do prédio.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direcao asc ou desc
     * @return void
     */
    protected function andaresCount(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('andares_count', $direcao);
    }

    /**
     * Aplica a ordenação pelo nome da localidade pai.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direcao asc ou desc
     * @return void
     */
    protected function localidadePaiNome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('localidades.nome', $direcao);
    }
}
