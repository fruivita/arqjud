<?php

namespace App\Pipes\Estante;

use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinLocalidade
{
    /**
     * Aplica por pipe o join de todas as tabelas pais relacionadas às
     * estantes.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, \Closure $next)
    {
        $query
            ->join('salas', 'salas.id', 'estantes.sala_id')
            ->join('andares', 'andares.id', 'salas.andar_id')
            ->join('predios', 'predios.id', 'andares.predio_id')
            ->join('localidades', 'localidades.id', 'predios.localidade_id');

        return $next($query);
    }
}
