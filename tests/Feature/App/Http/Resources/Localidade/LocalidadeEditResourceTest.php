<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Localidade\LocalidadeEditResource;
use App\Models\Localidade;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->localidade = Localidade::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::LOCALIDADE_DELETE, Permissao::LOCALIDADE_VIEW, Permissao::LOCALIDADE_UPDATE, Permissao::PREDIO_CREATE]);

    $resource = LocalidadeEditResource::make($this->localidade);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->localidade->only(['id', 'nome', 'descricao'])
            + [
                'links' => [
                    'view' => route('cadastro.localidade.edit', $this->localidade),
                    'update' => route('cadastro.localidade.update', $this->localidade),
                    'predio' => [
                        'create' => route('cadastro.predio.create', $this->localidade),
                        'store' => route('cadastro.predio.store', $this->localidade),
                    ],
                ],
            ],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = LocalidadeEditResource::make($this->localidade->loadCount(['predios', 'caixasCriadas']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->localidade->only(['id', 'nome', 'descricao', 'predios_count', 'caixas_criadas_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = LocalidadeEditResource::make($this->localidade);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->localidade->only(['id', 'nome', 'descricao'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(LocalidadeEditResource::make(null)->resolve())->toBeEmpty();
});
