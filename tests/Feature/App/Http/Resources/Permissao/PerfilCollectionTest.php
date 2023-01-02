<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Permissao\PermissaoCollection;
use App\Http\Resources\Permissao\PermissaoResource;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->permissoes = Permissao::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    $resource = PermissaoCollection::make($this->permissoes);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->permissoes->count());
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = PermissaoCollection::make($this->permissoes);

    $dados = $resource->response()->getData(true);

    expect($dados)
        ->toHaveKey('data')
        ->not->toHaveKey('links');
});

test('collection resolve o resource correto', function () {
    $resource = PermissaoCollection::make($this->permissoes);

    expect($resource->collects)->toBe(PermissaoResource::class);
});
