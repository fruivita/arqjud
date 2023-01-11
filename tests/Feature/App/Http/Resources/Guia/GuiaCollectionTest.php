<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Guia\GuiaCollection;
use App\Http\Resources\Guia\GuiaResource;
use App\Models\Guia;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->guias = Guia::factory(2)->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = GuiaCollection::make($this->guias);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->guias->count());
});

test('collection resolve o resource correto', function () {
    $resource = GuiaCollection::make($this->guias);

    expect($resource->collects)->toBe(GuiaResource::class);
});
