<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Andar\AndarOnlyResource;
use App\Models\Andar;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->andar = Andar::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = AndarOnlyResource::make($this->andar);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'predio_id'])
    ]);
});

test('retorna o prédio pai se houver o eager load da propriedade', function () {
    $resource = AndarOnlyResource::make($this->andar->load('predio'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'predio_id'])
            + ['predio' => $this->andar->predio->only(['id', 'nome', 'localidade_id'])]
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = AndarOnlyResource::make($this->andar->loadCount('salas'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'predio_id', 'salas_count'])
    ]);
});
