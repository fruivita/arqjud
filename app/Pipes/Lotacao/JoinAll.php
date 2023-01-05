<?php

namespace App\Pipes\Lotacao;

use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinAll
{
    /**
     * Aplica por pipe o join de todas as tabelas pais relacionadas à lotação.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Closure  $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, \Closure $next)
    {
        $query->leftJoin('lotacoes AS lotacoes_pai', 'lotacoes_pai.id', 'lotacoes.lotacao_pai');

        return $next($query);
    }
}
