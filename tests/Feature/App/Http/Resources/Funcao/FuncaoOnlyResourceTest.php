<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Funcao\FuncaoOnlyResource;
use App\Models\FuncaoConfianca;

beforeEach(function () {
    $this->funcao = FuncaoConfianca::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = FuncaoOnlyResource::make($this->funcao);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->funcao->only(['id', 'nome']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(FuncaoOnlyResource::make(null)->resolve())->toBeEmpty();
});
