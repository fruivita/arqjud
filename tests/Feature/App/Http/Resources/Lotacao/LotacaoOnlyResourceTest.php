<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Lotacao\LotacaoOnlyResource;
use App\Models\Lotacao;

beforeEach(function () {
    $this->lotacao = Lotacao::factory()->for(Lotacao::factory(), 'lotacaoPai')->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = LotacaoOnlyResource::make($this->lotacao);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->lotacao->only(['id', 'nome', 'sigla', 'lotacao_pai_id']),
    ]);
});

test('retorna a lotação pai se houver o eager load da propriedade', function () {
    $resource = LotacaoOnlyResource::make($this->lotacao->load('lotacaoPai'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->lotacao->only(['id', 'nome', 'sigla', 'lotacao_pai_id'])
            + ['lotacao_pai' => LotacaoOnlyResource::make($this->lotacao->lotacaoPai)->resolve()],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(LotacaoOnlyResource::make(null)->resolve())->toBeEmpty();
});
