<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Permissao\PermissaoResource;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->permissao = Permissao::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PERMISSAO_VIEW, Permissao::PERMISSAO_UPDATE]);

    $resource = PermissaoResource::make($this->permissao);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->permissao->only(['id', 'nome', 'slug', 'descricao'])
            + [
                'links' => [
                    'view' => route('administracao.permissao.edit', $this->permissao),
                    'update' => route('administracao.permissao.update', $this->permissao),
                ],
            ],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = PermissaoResource::make($this->permissao);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->permissao->only(['id', 'nome', 'slug', 'descricao'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PermissaoResource::make(null)->resolve())->toBeEmpty();
});
