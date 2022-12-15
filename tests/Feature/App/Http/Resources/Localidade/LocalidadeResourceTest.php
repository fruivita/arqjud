<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Localidade\LocalidadeResource;
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

    $resource = LocalidadeResource::make($this->localidade);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->localidade->only(['id', 'nome', 'descricao'])
            + [
                'links' => [
                    'view' => route('cadastro.localidade.edit', $this->localidade),
                    'update' => route('cadastro.localidade.update', $this->localidade),
                    'delete' => route('cadastro.localidade.destroy', $this->localidade),
                    'predio' => [
                        'create' => route('cadastro.predio.create', $this->localidade),
                        'store' => route('cadastro.predio.store', $this->localidade),
                    ],
                ],
            ],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = LocalidadeResource::make($this->localidade->loadCount(['predios', 'caixasCriadas']));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->localidade->only(['id', 'nome', 'descricao', 'predios_count', 'caixas_criadas_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = LocalidadeResource::make($this->localidade);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->localidade->only(['id', 'nome', 'descricao'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(LocalidadeResource::make(null)->resolve())->toBeEmpty();
});
