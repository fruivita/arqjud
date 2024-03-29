<?php

namespace App\Pipes\Usuario;

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
     * Aplica a ordenação pelo matricula.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function matricula(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('matricula', $direcao);
    }

    /**
     * Aplica a ordenação pelo email.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function email(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('email', $direcao);
    }

    /**
     * Aplica a ordenação pelo nome.
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
     * Aplica a ordenação pela data/hora do último login.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function ultimoLogin(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('ultimo_login', $direcao);
    }

    /**
     * Aplica a ordenação pela sigla da lotação.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function lotacaoSigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('lotacoes.sigla', $direcao);
    }

    /**
     * Aplica a ordenação pelo nome do cargo.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function cargoNome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('cargos.nome', $direcao);
    }

    /**
     * Aplica a ordenação pelo nome da função de confiança.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function funcaoNome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('funcoes_confianca.nome', $direcao);
    }

    /**
     * Aplica a ordenação pelo nome do perfil.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function perfilNome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('perfis.nome', $direcao);
    }
}
