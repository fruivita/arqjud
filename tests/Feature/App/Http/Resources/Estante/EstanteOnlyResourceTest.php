<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Estante\EstanteOnlyResource;
use App\Models\Estante;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->estante = Estante::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = EstanteOnlyResource::make($this->estante);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'sala_id']),
    ]);
});

test('retorna a sala pai se houver o eager load da propriedade', function () {
    $resource = EstanteOnlyResource::make($this->estante->load('sala'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'sala_id'])
            + ['sala' => $this->estante->sala->only(['id', 'numero', 'andar_id'])],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = EstanteOnlyResource::make($this->estante->loadCount('prateleiras'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'sala_id', 'prateleiras_count']),
    ]);
});
