<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Estante\EstanteOnlyResource;
use App\Http\Resources\Prateleira\PrateleiraOnlyResource;
use App\Models\Prateleira;

beforeEach(function () {
    $this->prateleira = Prateleira::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = PrateleiraOnlyResource::make($this->prateleira);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->prateleira->only(['id', 'numero', 'descricao', 'estante_id']),
    ]);
});

test('retorna a estante pai se houver o eager load da propriedade', function () {
    $resource = PrateleiraOnlyResource::make($this->prateleira->load('estante'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->prateleira->only(['id', 'numero', 'descricao', 'estante_id'])
            + ['estante' => EstanteOnlyResource::make($this->prateleira->estante)->resolve()],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = PrateleiraOnlyResource::make($this->prateleira->loadCount('caixas'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->prateleira->only(['id', 'numero', 'descricao', 'estante_id', 'caixas_count']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PrateleiraOnlyResource::make(null)->resolve())->toBeEmpty();
});
