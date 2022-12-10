<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Models\Localidade;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->localidade = Localidade::factory()->create();
});

afterEach(function () {
    logout();
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
