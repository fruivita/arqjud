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
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Closure  $next
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
            ->join('lotacoes AS destinatarias', 'solicitacoes.lotacao_destinataria_id', 'destinatarias.id');

        return $next($query);
    }
}
