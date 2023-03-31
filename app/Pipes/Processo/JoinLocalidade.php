<?php

namespace App\Pipes\Processo;

use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinLocalidade
{
    /**
     * Aplica por pipe o join de todas as tabelas pais relacionadas aos
     * processos.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, \Closure $next)
    {
        $query
            ->join('caixas', 'caixas.id', 'processos.caixa_id')
            ->join('localidades AS criadoras', 'criadoras.id', 'caixas.localidade_criadora_id')
            ->join('tipos_processo', 'tipos_processo.id', 'caixas.tipo_processo_id')
            ->join('prateleiras', 'prateleiras.id', 'caixas.prateleira_id')
            ->join('estantes', 'estantes.id', 'prateleiras.estante_id')
            ->join('salas', 'salas.id', 'estantes.sala_id')
            ->join('andares', 'andares.id', 'salas.andar_id')
            ->join('predios', 'predios.id', 'andares.predio_id')
            ->join('localidades', 'localidades.id', 'predios.localidade_id');

        return $next($query);
    }
}
