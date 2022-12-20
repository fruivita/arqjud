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
     * @param  \stdClass  $entrega
     * @param  \Closure  $next
     * @return \stdClass
     */
    public function handle(\stdClass $entrega, \Closure $next)
    {
        $entrega->recebedor = Usuario::firstWhere('username', $entrega->recebedor);

        $entrega->guia = $this->criarGuia($entrega->solicitacoes, $entrega->recebedor);

        return $next($entrega);
    }

    /**
     * Cria uma guia de remessa de processos e a persiste no banco de dados.
     *
     * @param \Illuminate\Support\Collection|array $solicitacoes
     * @param \App\Models\Usuario $recebedor
     * @return \App\Models\Guia
     */
    private function criarGuia(mixed $solicitacoes, Usuario $recebedor)
    {
        $solicitacoes = Solicitacao::with(['processo', 'solicitante'])->whereIn('id', $solicitacoes)->lazy();
        $recebedor->loadMissing('lotacao');

        $now = now();
        $guia = new Guia();

        $guia->numero = Guia::proximoNumero();
        $guia->ano = $now->year;
        $guia->gerada_em = $now;
        $guia->remetente = auth()->user()->only(['username', 'nome']);
        $guia->recebedor = $recebedor->only(['username', 'nome']);
        $guia->lotacao_destinataria = $recebedor->lotacao->only(['nome', 'sigla']);
        $guia->processos = $solicitacoes->map(function (Solicitacao $solicitacao) {
            return [
                'numero' => apenasNumeros($solicitacao->processo->numero),
                'qtd_volumes' => $solicitacao->processo->qtd_volumes,
                'solicitante' => [
                    'username' => fake()->firstName(),
                    'nome' => fake()->name(),
                ],
            ];
        });

        $guia->save();

        return $guia;
    }
}
