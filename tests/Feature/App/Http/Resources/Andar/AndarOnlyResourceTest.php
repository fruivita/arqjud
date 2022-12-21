<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Andar\AndarOnlyResource;
use App\Http\Resources\Predio\PredioOnlyResource;
use App\Models\Andar;

beforeEach(function () {
    $this->andar = Andar::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = AndarOnlyResource::make($this->andar);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'descricao', 'predio_id']),
    ]);
});

test('retorna o prédio pai se houver o eager load da propriedade', function () {
    $resource = AndarOnlyResource::make($this->andar->load('predio'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'descricao', 'predio_id'])
            + ['predio' => PredioOnlyResource::make($this->andar->predio)->resolve()],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = AndarOnlyResource::make($this->andar->loadCount('salas'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'descricao', 'predio_id', 'salas_count']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(AndarOnlyResource::make(null)->resolve())->toBeEmpty();
});
