<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Perfil\PerfilCollection;
use App\Http\Resources\Perfil\PerfilResource;
use App\Models\Perfil;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->perfis = Perfil::factory(2)->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PERFIL_CREATE]);

    $resource = PerfilCollection::make($this->perfis);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->perfis->count())
        ->and($dados['links'])->toMatchArray(['create' => route('administracao.perfil.create')]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = PerfilCollection::make($this->perfis);

    $dados = $resource->response()->getData(true);

    expect($dados)
        ->toHaveKey('data')
        ->not->toHaveKey('links');
});

test('collection resolve o resource correto', function () {
    $resource = PerfilCollection::make($this->perfis);

    expect($resource->collects)->toBe(PerfilResource::class);
});
