<?php

namespace App\Pipes\Caixa;

use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinLocalidadeCriadora
{
    /**
     * Aplica por pipe o join das tabelas:
     * - criadoras (Localidades criadoras das caixas).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, \Closure $next)
    {
        $query->join('localidades AS criadoras', 'criadoras.id', 'caixas.localidade_criadora_id');

        return $next($query);
    }
}
