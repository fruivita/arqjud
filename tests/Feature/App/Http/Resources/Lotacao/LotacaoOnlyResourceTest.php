<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Lotacao\LotacaoOnlyResource;
use App\Models\Lotacao;
use FruiVita\Corporativo\Models\Lotacao as LotacaoCorporativo;

beforeEach(function () {
    $this->lotacao = Lotacao::factory()->for(Lotacao::factory(), 'lotacaoPai')->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = LotacaoOnlyResource::make($this->lotacao);

    expect($resource->response()->getData(true))->toBe([
        'data' => lotacaoApi($this->lotacao),
    ]);
});

test('retorna a lotação pai se houver o eager load da propriedade', function () {
    $resource = LotacaoOnlyResource::make($this->lotacao->load('lotacaoPai'));

    expect($resource->response()->getData(true))->toBe([
        'data' => lotacaoApi($this->lotacao)
            + ['lotacao_pai' => lotacaoApi($this->lotacao->lotacaoPai)],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(LotacaoOnlyResource::make(null)->resolve())->toBeEmpty();
});

function lotacaoApi(LotacaoCorporativo $lotacao)
{
    return [
        'id' => $lotacao->id,
        'nome' => $lotacao->nome,
        'sigla' => mb_strtoupper($lotacao->sigla),
        'lotacao_pai_id' => $lotacao->lotacao_pai_id,
    ];
}
