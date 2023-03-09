<?php

namespace App\Pipes\Lotacao;

use App\Pipes\OrderBase;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pressupõe join com as tabelas pais se o critério de ordenação for por elas.
 *
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Order extends OrderBase
{
    /**
     * Aplica a ordenação pelo nome da lotação.
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
     * Aplica a ordenação pela sigla da lotação.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function sigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('sigla', $direcao);
    }

    /**
     * Aplica a ordenação pelo status administravel da lotação.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function administravel(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('administravel', $direcao);
    }

    /**
     * Aplica a ordenação pelo nome da lotação pai.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function lotacaoPaiNome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('lotacoes_pai.nome', $direcao);
    }

    /**
     * Aplica a ordenação pela sigla da lotação pai.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function lotacaoPaiSigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('lotacoes_pai.sigla', $direcao);
    }

    /**
     * Aplica a ordenação pela quantidade de usuários da lotação.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function usuariosCount(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('usuarios_count', $direcao);
    }
}
