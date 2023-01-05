<?php

namespace App\Pipes\Perfil;

use App\Pipes\OrderBase;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Order extends OrderBase
{
    /**
     * {@inheritdoc}
     */
    protected $column = 'poder';

    /**
     * Aplica a ordenação por nome do perfil.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function nome(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('nome', $direcao);
    }

    /**
     * Aplica a ordenação por slug do perfil.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function slug(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('slug', $direcao);
    }

    /**
     * Aplica a ordenação por poder do perfil.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function poder(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('poder', $direcao);
    }

    /**
     * Aplica a ordenação pela quantidade de usuários do perfil.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function usuariosCount(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('usuarios_count', $direcao);
    }
}
