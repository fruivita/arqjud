<?php

namespace App\Pipes\Prateleira;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinLocalidade
{
    /**
     * Aplica por pipe o join de todas as tabelas pais relacionadas às
     * prateleiras.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Closure  $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, Closure $next)
    {
        $query
            ->join('estantes', 'estantes.id', 'prateleiras.estante_id')
            ->join('salas', 'salas.id', 'estantes.sala_id')
            ->join('andares', 'andares.id', 'salas.andar_id')
            ->join('predios', 'predios.id', 'andares.predio_id')
            ->join('localidades', 'localidades.id', 'predios.localidade_id');

        return $next($query);
    }
}
