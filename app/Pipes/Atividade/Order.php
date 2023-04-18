<?php

namespace App\Pipes\Atividade;

use App\Pipes\OrderBase;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Order extends OrderBase
{
    /**
     * Aplica a ordenação pelo nome da log da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function logName(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('log_name', $direcao);
    }

    /**
     * Aplica a ordenação pelo evento da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function event(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('event', $direcao);
    }

    /**
     * Aplica a ordenação pela descrição da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function description(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('description', $direcao);
    }

    /**
     * Aplica a ordenação pelo tipo de subject (entidade) da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function subjectType(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('subject_type', $direcao);
    }

    /**
     * Aplica a ordenação pelo id do subject (entidade) da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function subjectId(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('subject_id', $direcao);
    }

    /**
     * Aplica a ordenação pelo tipo de causador da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function causerType(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('causer_type', $direcao);
    }

    /**
     * Aplica a ordenação pelo id do causador da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function causerId(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('causer_id', $direcao);
    }

    /**
     * Aplica a ordenação pela matrícula do causador da atividade.
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
     * Aplica a ordenação pelo batch uuid da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function uuid(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('batch_uuid', $direcao);
    }

    /**
     * Aplica a ordenação pela data de criação registrada da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function createdAt(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('created_at', $direcao);
    }

    /**
     * Aplica a ordenação pela data de atualização registrada da atividade.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function updatedAt(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('updated_at', $direcao);
    }
}
