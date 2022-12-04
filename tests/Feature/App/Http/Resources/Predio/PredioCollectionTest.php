<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Predio\PredioCollection;
use App\Http\Resources\Predio\PredioResource;
use App\Models\Localidade;
use App\Models\Predio;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->predios = Predio::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = PredioCollection::make($this->predios);

    $dados = $resource->response(request())->getData(true);

    expect($dados['data'])->toHaveCount($this->predios->count());
});

test('collection resolve o resource correto', function () {
    $resource = PredioCollection::make($this->predios);

    expect($resource->collects)->toBe(PredioResource::class);
});
