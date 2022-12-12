<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Solicitacao\SolicitacaoOnlyResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Solicitacao;

beforeEach(function () {
    $this->solicitacao = Solicitacao::factory()->devolvida()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = SolicitacaoOnlyResource::make($this->solicitacao);

    expect($resource->response()->getData(true))->toBe([
        'data' => solicitacaoApi($this->solicitacao),
    ]);
});

test('retorna o processo, o solicitante, o recebedor, o remetente, o rearquivador e a lotação destinatária se houver o eager load da propriedade', function () {
    $resource = SolicitacaoOnlyResource::make($this->solicitacao->load('processo', 'solicitante', 'recebedor', 'remetente', 'rearquivador', 'lotacaoDestinataria'));

    expect($resource->response()->getData(true))->toBe([
        'data' => solicitacaoApi($this->solicitacao)
            + ['processo' => processoApi($this->solicitacao->processo)]
            + ['solicitante' => UsuarioOnlyResource::make($this->solicitacao->solicitante)->resolve()]
            + ['recebedor' => UsuarioOnlyResource::make($this->solicitacao->recebedor)->resolve()]
            + ['remetente' => UsuarioOnlyResource::make($this->solicitacao->remetente)->resolve()]
            + ['rearquivador' => UsuarioOnlyResource::make($this->solicitacao->rearquivador)->resolve()]
            + ['lotacao_destinataria' => lotacaoApi($this->solicitacao->lotacaoDestinataria)]
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(SolicitacaoOnlyResource::make(null)->resolve())->toBeEmpty();
});
