<?php

namespace App\Filters\Predio;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinLocalidade
{
    /**
     * Aplica por pipe o join das tabelas:
     * - localidades;
     * - prédios.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Closure $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, Closure $next)
    {
        $query->join('localidades', 'localidades.id', 'predios.localidade_id');

        return $next($query);
    }
}
