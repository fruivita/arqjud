<?php

namespace App\Pipes\Andar;

use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinLocalidade
{
    /**
     * Aplica por pipe o join de todas as tabelas pais relacionadas aos
     * andares.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, \Closure $next)
    {
        $query
            ->join('predios', 'predios.id', 'andares.predio_id')
            ->join('localidades', 'localidades.id', 'predios.localidade_id');

        return $next($query);
    }
}
