<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Perfil\PerfilOnlyResource;
use App\Models\Perfil;

beforeEach(function () {
    $this->funcao = Perfil::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = PerfilOnlyResource::make($this->funcao);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->funcao->only(['id', 'nome', 'slug', 'poder', 'descricao']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PerfilOnlyResource::make(null)->resolve())->toBeEmpty();
});
