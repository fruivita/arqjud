<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Lotacao\LotacaoOnlyResource;
use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Http\Resources\Solicitacao\SolicitacaoOnlyResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Solicitacao;

beforeEach(function () {

    $this->solicitacao = Solicitacao::factory()->devolvida()->create();

    $this->solicitacao_api = [
        'id' => $this->solicitacao->id,
        'solicitada_em' => $this->solicitacao->solicitada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
        'entregue_em' => $this->solicitacao->entregue_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
        'devolvida_em' => $this->solicitacao->devolvida_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
        'por_guia' => $this->solicitacao->por_guia,
        'descricao' => $this->solicitacao->descricao,
        'status' => $this->solicitacao->status,
        'processo_id' => $this->solicitacao->processo_id,
        'solicitante_id' => $this->solicitacao->solicitante_id,
        'recebedor_id' => $this->solicitacao->recebedor_id,
        'remetente_id' => $this->solicitacao->remetente_id,
        'rearquivador_id' => $this->solicitacao->rearquivador_id,
        'lotacao_destinataria_id' => $this->solicitacao->lotacao_destinataria_id,
        'guia_id' => $this->solicitacao->guia_id,
    ];
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = SolicitacaoOnlyResource::make($this->solicitacao);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->solicitacao_api,
    ]);
});

test('retorna o processo, o solicitante, o recebedor, o remetente, o rearquivador e a lotação destinatária se houver o eager load da propriedade', function () {
    $resource = SolicitacaoOnlyResource::make($this->solicitacao->load('processo', 'solicitante', 'recebedor', 'remetente', 'rearquivador', 'lotacaoDestinataria'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->solicitacao_api
            + ['processo' => ProcessoOnlyResource::make($this->solicitacao->processo)->resolve()]
            + ['solicitante' => UsuarioOnlyResource::make($this->solicitacao->solicitante)->resolve()]
            + ['recebedor' => UsuarioOnlyResource::make($this->solicitacao->recebedor)->resolve()]
            + ['remetente' => UsuarioOnlyResource::make($this->solicitacao->remetente)->resolve()]
            + ['rearquivador' => UsuarioOnlyResource::make($this->solicitacao->rearquivador)->resolve()]
            + ['lotacao_destinataria' => LotacaoOnlyResource::make($this->solicitacao->lotacaoDestinataria)->resolve()]
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(SolicitacaoOnlyResource::make(null)->resolve())->toBeEmpty();
});
