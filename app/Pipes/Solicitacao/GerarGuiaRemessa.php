<?php

namespace App\Pipes\Solicitacao;

use App\Models\Guia;
use App\Models\Solicitacao;
use App\Models\Usuario;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class GerarGuiaRemessa
{
    /**
     * Pipe responsável por criar a guia de remessa de processos no banco de
     * dados baseado nos dados da entrega.
     *
     * @return \stdClass
     */
    public function handle(\stdClass $entrega, \Closure $next)
    {
        $entrega->recebedor = Usuario::firstWhere('matricula', $entrega->recebedor);
        $entrega->remetente = auth()->user();

        $entrega->guia = $this->criarGuia($entrega->solicitacoes, $entrega->recebedor, $entrega->remetente);

        return $next($entrega);
    }

    /**
     * Cria uma guia de remessa de processos e a persiste no banco de dados.
     *
     * @param  \Illuminate\Support\Collection|array  $solicitacoes
     * @param  \App\Models\Usuario|\LdapRecord\Models\ActiveDirectory\User  $remetente
     * @return \App\Models\Guia
     */
    private function criarGuia(mixed $solicitacoes, Usuario $recebedor, mixed $remetente)
    {
        $solicitacoes = Solicitacao::with(['processo', 'solicitante'])->whereIn('id', $solicitacoes)->lazy();
        $recebedor->loadMissing('lotacao');

        $now = now();
        $guia = new Guia();

        $guia->numero = Guia::proximoNumero();
        $guia->ano = $now->year;
        $guia->gerada_em = $now;
        $guia->remetente = $remetente->only(['matricula', 'nome']);
        $guia->recebedor = $recebedor->only(['matricula', 'nome']);
        $guia->destino = $recebedor->lotacao->only(['nome', 'sigla']);
        $guia->processos = $solicitacoes->map(function (Solicitacao $solicitacao) {
            return [
                'numero' => apenasNumeros($solicitacao->processo->numero),
                'qtd_volumes' => $solicitacao->processo->qtd_volumes,
                'solicitante' => [
                    'matricula' => $solicitacao->solicitante->matricula,
                    'nome' => $solicitacao->solicitante->nome,
                ],
            ];
        });

        $guia->save();

        return $guia;
    }
}
