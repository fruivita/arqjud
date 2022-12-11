<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Estante\EstanteOnlyResource;
use App\Http\Resources\Sala\SalaOnlyResource;
use App\Models\Estante;

beforeEach(function () {
    $this->estante = Estante::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = EstanteOnlyResource::make($this->estante);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'descricao', 'sala_id']),
    ]);
});

test('retorna a sala pai se houver o eager load da propriedade', function () {
    $resource = EstanteOnlyResource::make($this->estante->load('sala'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'descricao', 'sala_id'])
            + ['sala' => SalaOnlyResource::make($this->estante->sala)->resolve()],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = EstanteOnlyResource::make($this->estante->loadCount('prateleiras'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'descricao', 'sala_id', 'prateleiras_count']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(EstanteOnlyResource::make(null)->resolve())->toBeEmpty();
});
