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

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => lotacaoApi($this->lotacao),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(LotacaoOnlyResource::make(null)->resolve())->toBeEmpty();
});
