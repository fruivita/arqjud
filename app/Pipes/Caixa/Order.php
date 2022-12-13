<?php

namespace App\Pipes\Caixa;

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
     * Aplica a ordenação pelo número da caixa.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function numero(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('numero', $direcao);
    }

    /**
     * Aplica a ordenação pelo ano da caixa.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function ano(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('ano', $direcao);
    }

    /**
     * Aplica a ordenação pelo status de guarda_permanente da caixa.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function guardaPermanente(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('guarda_permanente', $direcao);
    }

    /**
     * Aplica a ordenação pelo complemento da caixa.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function complemento(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('complemento', $direcao);
    }

    /**
     * Aplica a ordenação pela quantidade de volumes da caixa.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function volumesCount(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('volumes_count', $direcao);
    }

    /**
     * Aplica a ordenação pelo nome da localidade criadora.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function localidadeCriadoraNome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('criadoras.nome', $direcao);
    }

    /**
     * Aplica a ordenação pelo nome da localidade pai.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function localidadePaiNome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('localidades.nome', $direcao);
    }

    /**
     * Aplica a ordenação pelo nome do prédio pai.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function predioPaiNome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('predios.nome', $direcao);
    }

    /**
     * Aplica a ordenação pelo número do andar pai.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function andarPaiNumero(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('andares.numero', $direcao);
    }

    /**
     * Aplica a ordenação pelo apelido do andar pai.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function andarPaiApelido(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('andares.apelido', $direcao);
    }

    /**
     * Aplica a ordenação pelo número da sala pai.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function salaPaiNumero(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('salas.numero', $direcao);
    }

    /**
     * Aplica a ordenação pelo número da estante pai.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function estantePaiNumero(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('estantes.numero', $direcao);
    }

    /**
     * Aplica a ordenação pelo número da prateleira pai.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function prateleiraPaiNumero(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('prateleiras.numero', $direcao);
    }
}
