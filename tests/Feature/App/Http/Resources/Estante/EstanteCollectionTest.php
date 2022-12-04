<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Estante\EstanteCollection;
use App\Http\Resources\Estante\EstanteResource;
use App\Models\Estante;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->estantes = Estante::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = EstanteCollection::make($this->estantes);

    $dados = $resource->response(request())->getData(true);

    expect($dados['data'])->toHaveCount($this->estantes->count());
});

test('collection resolve o resource correto', function () {
    $resource = EstanteCollection::make($this->estantes);

    expect($resource->collects)->toBe(EstanteResource::class);
});
