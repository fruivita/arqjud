<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Andar\AndarOnlyResource;
use App\Http\Resources\Sala\SalaOnlyResource;
use App\Models\Sala;

beforeEach(function () {
    $this->sala = Sala::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = SalaOnlyResource::make($this->sala);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id']),
    ]);
});

test('retorna o andar pai se houver o eager load da propriedade', function () {
    $resource = SalaOnlyResource::make($this->sala->load('andar'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id'])
            + ['andar' => AndarOnlyResource::make($this->sala->andar)->resolve()],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = SalaOnlyResource::make($this->sala->loadCount('estantes'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id', 'estantes_count']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(SalaOnlyResource::make(null)->resolve())->toBeEmpty();
});
