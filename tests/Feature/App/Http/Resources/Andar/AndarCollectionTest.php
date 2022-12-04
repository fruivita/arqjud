<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Andar\AndarCollection;
use App\Http\Resources\Andar\AndarResource;
use App\Models\Andar;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->andares = Andar::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = AndarCollection::make($this->andares);

    $dados = $resource->response(request())->getData(true);

    expect($dados['data'])->toHaveCount($this->andares->count());
});

test('collection resolve o resource correto', function () {
    $resource = AndarCollection::make($this->andares);

    expect($resource->collects)->toBe(AndarResource::class);
});
