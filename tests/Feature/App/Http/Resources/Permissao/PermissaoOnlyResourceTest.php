<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Permissao\PermissaoOnlyResource;
use App\Models\Permissao;

beforeEach(function () {
    $this->permissao = Permissao::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = PermissaoOnlyResource::make($this->permissao);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->permissao->only(['id', 'nome', 'slug', 'descricao']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PermissaoOnlyResource::make(null)->resolve())->toBeEmpty();
});
