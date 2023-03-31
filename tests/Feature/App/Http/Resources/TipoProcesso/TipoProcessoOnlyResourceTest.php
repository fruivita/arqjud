<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\TipoProcesso\TipoProcessoOnlyResource;
use App\Models\TipoProcesso;

beforeEach(function () {
    $this->tipo_processo = TipoProcesso::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = TipoProcessoOnlyResource::make($this->tipo_processo);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->tipo_processo->only(['id', 'nome', 'descricao']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(TipoProcessoOnlyResource::make(null)->resolve())->toBeEmpty();
});
