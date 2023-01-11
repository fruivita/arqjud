<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaCollection;
use App\Http\Resources\Caixa\CaixaResource;
use App\Models\Caixa;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->caixas = Caixa::factory(2)->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = CaixaCollection::make($this->caixas);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->caixas->count());
});

test('collection resolve o resource correto', function () {
    $resource = CaixaCollection::make($this->caixas);

    expect($resource->collects)->toBe(CaixaResource::class);
});
