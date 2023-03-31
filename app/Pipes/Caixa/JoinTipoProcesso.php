<?php

namespace App\Pipes\Caixa;

use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinTipoProcesso
{
    /**
     * Aplica por pipe o join das tabelas:
     * - tipos_processo.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, \Closure $next)
    {
        $query->join('tipos_processo', 'tipos_processo.id', 'caixas.tipo_processo_id');

        return $next($query);
    }
}
