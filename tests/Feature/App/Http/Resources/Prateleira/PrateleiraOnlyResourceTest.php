<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Prateleira\PrateleiraOnlyResource;
use App\Models\Prateleira;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->prateleira = Prateleira::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = PrateleiraOnlyResource::make($this->prateleira);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->prateleira->only(['id', 'numero', 'estante_id']),
    ]);
});

test('retorna a estante pai se houver o eager load da propriedade', function () {
    $resource = PrateleiraOnlyResource::make($this->prateleira->load('estante'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->prateleira->only(['id', 'numero', 'estante_id'])
            + ['estante' => $this->prateleira->estante->only(['id', 'numero', 'sala_id'])],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = PrateleiraOnlyResource::make($this->prateleira->loadCount('caixas'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->prateleira->only(['id', 'numero', 'estante_id', 'caixas_count']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PrateleiraOnlyResource::make(null)->resolve())->toBeEmpty();
});
