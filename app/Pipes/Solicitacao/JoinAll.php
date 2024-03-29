<?php

namespace App\Pipes\Solicitacao;

use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinAll
{
    /**
     * Aplica por pipe o join de todas as tabelas relacionadas às solicitações.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, \Closure $next)
    {
        $query
            ->join('processos', 'solicitacoes.processo_id', 'processos.id')
            ->join('usuarios AS solicitantes', 'solicitacoes.solicitante_id', 'solicitantes.id')
            ->leftJoin('usuarios AS recebedores', 'solicitacoes.recebedor_id', 'recebedores.id')
            ->leftJoin('usuarios AS remetentes', 'solicitacoes.remetente_id', 'remetentes.id')
            ->leftJoin('usuarios AS rearquivadores', 'solicitacoes.rearquivador_id', 'rearquivadores.id')
            ->join('lotacoes AS destinos', 'solicitacoes.destino_id', 'destinos.id');

        return $next($query);
    }
}
