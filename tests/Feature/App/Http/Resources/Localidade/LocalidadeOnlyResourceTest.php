<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Models\Localidade;

beforeEach(function () {
    $this->localidade = Localidade::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = LocalidadeOnlyResource::make($this->localidade);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->localidade->only(['id', 'nome', 'descricao']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(LocalidadeOnlyResource::make(null)->resolve())->toBeEmpty();
});
