<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Sala\SalaCollection;
use App\Http\Resources\Sala\SalaResource;
use App\Models\Sala;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->salas = Sala::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = SalaCollection::make($this->salas);

    $dados = $resource->response(request())->getData(true);

    expect($dados['data'])->toHaveCount($this->salas->count());
});

test('collection resolve o resource correto', function () {
    $resource = SalaCollection::make($this->salas);

    expect($resource->collects)->toBe(SalaResource::class);
});
