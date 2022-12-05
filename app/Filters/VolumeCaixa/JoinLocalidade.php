<?php

namespace App\Filters\VolumeCaixa;

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
     * - prédios;
     * - andares;
     * - salas;
     * - estantes;
     * - prateleiras;
     * - caixas;
     * - criadoras (Localidades criadoras das caixas);
     * - volumes_caixa.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Closure  $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, Closure $next)
    {
        $query
            ->join('caixas', 'caixas.id', 'volumes_caixa.caixa_id')
            ->join('localidades AS criadoras', 'criadoras.id', 'caixas.localidade_criadora_id')
            ->join('prateleiras', 'prateleiras.id', 'caixas.prateleira_id')
            ->join('estantes', 'estantes.id', 'prateleiras.estante_id')
            ->join('salas', 'salas.id', 'estantes.sala_id')
            ->join('andares', 'andares.id', 'salas.andar_id')
            ->join('predios', 'predios.id', 'andares.predio_id')
            ->join('localidades', 'localidades.id', 'predios.localidade_id');

        return $next($query);
    }
}
