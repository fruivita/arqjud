<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\VolumeCaixa\VolumeCaixaCollection;
use App\Http\Resources\VolumeCaixa\VolumeCaixaResource;
use App\Models\VolumeCaixa;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->volumes = VolumeCaixa::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = VolumeCaixaCollection::make($this->volumes);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->volumes->count());
});

test('collection resolve o resource correto', function () {
    $resource = VolumeCaixaCollection::make($this->volumes);

    expect($resource->collects)->toBe(VolumeCaixaResource::class);
});
