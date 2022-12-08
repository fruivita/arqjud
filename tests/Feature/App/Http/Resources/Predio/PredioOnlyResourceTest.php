<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Predio\PredioOnlyResource;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->predio = Predio::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = PredioOnlyResource::make($this->predio);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'localidade_id']),
    ]);
});

test('retorna a localidade pai se houver o eager load da propriedade', function () {
    $resource = PredioOnlyResource::make($this->predio->load('localidade'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'localidade_id'])
            + ['localidade' => $this->predio->localidade->only(['id', 'nome'])],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = PredioOnlyResource::make($this->predio->loadCount('andares'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'localidade_id', 'andares_count']),
    ]);
});
