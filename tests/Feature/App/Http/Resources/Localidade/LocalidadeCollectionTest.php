<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Localidade\LocalidadeCollection;
use App\Http\Resources\Localidade\LocalidadeResource;
use App\Models\Localidade;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->localidades = Localidade::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::LOCALIDADE_CREATE]);

    $resource = LocalidadeCollection::make($this->localidades);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->localidades->count())
        ->and($dados['links'])->toMatchArray(['create' => route('cadastro.localidade.create')]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = LocalidadeCollection::make($this->localidades);

    $dados = $resource->response()->getData(true);

    expect($dados)
        ->toHaveKey('data')
        ->not->toHaveKey('links');
});

test('collection resolve o resource correto', function () {
    $resource = LocalidadeCollection::make($this->localidades);

    expect($resource->collects)->toBe(LocalidadeResource::class);
});
