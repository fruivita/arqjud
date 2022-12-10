<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Sala\SalaOnlyResource;
use App\Models\Sala;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->sala = Sala::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = SalaOnlyResource::make($this->sala);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id']),
    ]);
});

test('retorna o andar pai se houver o eager load da propriedade', function () {
    $resource = SalaOnlyResource::make($this->sala->load('andar'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id'])
            + ['andar' => $this->sala->andar->only(['id', 'numero', 'apelido', 'descricao', 'predio_id'])],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = SalaOnlyResource::make($this->sala->loadCount('estantes'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id', 'estantes_count']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(SalaOnlyResource::make(null)->resolve())->toBeEmpty();
});
