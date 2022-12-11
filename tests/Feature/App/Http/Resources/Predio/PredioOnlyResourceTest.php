<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Resources\Predio\PredioOnlyResource;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->predio = Predio::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = PredioOnlyResource::make($this->predio);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'descricao', 'localidade_id']),
    ]);
});

test('retorna a localidade pai se houver o eager load da propriedade', function () {
    $resource = PredioOnlyResource::make($this->predio->load('localidade'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'descricao', 'localidade_id'])
            + ['localidade' => LocalidadeOnlyResource::make($this->predio->localidade)->resolve()],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = PredioOnlyResource::make($this->predio->loadCount('andares'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'descricao', 'localidade_id', 'andares_count']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PredioOnlyResource::make(null)->resolve())->toBeEmpty();
});
