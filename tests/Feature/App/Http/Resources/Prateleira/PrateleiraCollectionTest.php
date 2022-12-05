<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Prateleira\PrateleiraCollection;
use App\Http\Resources\Prateleira\PrateleiraResource;
use App\Models\Prateleira;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->prateleiras = Prateleira::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = PrateleiraCollection::make($this->prateleiras);

    $dados = $resource->response(request())->getData(true);

    expect($dados['data'])->toHaveCount($this->prateleiras->count());
});

test('collection resolve o resource correto', function () {
    $resource = PrateleiraCollection::make($this->prateleiras);

    expect($resource->collects)->toBe(PrateleiraResource::class);
});
