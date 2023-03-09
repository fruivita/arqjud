<?php

namespace App\Pipes\Usuario;

use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class JoinAll
{
    /**
     * Aplica por pipe o join de todas as tabelas relacionadas aos usuários.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, \Closure $next)
    {
        $query
            ->leftJoin('lotacoes', 'usuarios.lotacao_id', 'lotacoes.id')
            ->leftJoin('cargos', 'usuarios.cargo_id', 'cargos.id')
            ->leftJoin('funcoes_confianca', 'usuarios.funcao_confianca_id', 'funcoes_confianca.id')
            ->leftJoin('perfis', 'usuarios.perfil_id', 'perfis.id');

        return $next($query);
    }
}
