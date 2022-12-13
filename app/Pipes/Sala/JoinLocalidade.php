<?php

namespace App\Pipes\Sala;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinLocalidade
{
    /**
     * Aplica por pipe o join de todas as tabelas pais relacionadas às salas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Closure  $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, Closure $next)
    {
        $query
            ->join('andares', 'andares.id', 'salas.andar_id')
            ->join('predios', 'predios.id', 'andares.predio_id')
            ->join('localidades', 'localidades.id', 'predios.localidade_id');

        return $next($query);
    }
}
