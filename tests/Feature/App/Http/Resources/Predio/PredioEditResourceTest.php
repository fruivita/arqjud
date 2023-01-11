<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Localidade\LocalidadeEditResource;
use App\Http\Resources\Predio\PredioEditResource;
use App\Models\Permissao;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->predio = Predio::factory()->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::ANDAR_CREATE, Permissao::PREDIO_DELETE, Permissao::PREDIO_VIEW, Permissao::PREDIO_UPDATE]);

    $resource = PredioEditResource::make($this->predio);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->predio->only(['id', 'nome', 'descricao', 'localidade_id'])
            + [
                'links' => [
                    'view' => route('cadastro.predio.edit', $this->predio),
                    'update' => route('cadastro.predio.update', $this->predio),
                    'andar' => [
                        'create' => route('cadastro.andar.create', $this->predio),
                        'store' => route('cadastro.andar.store', $this->predio),
                    ],
                ],
            ],
    ]);
});

test('retorna a localidade pai se houver o eager load da propriedade', function () {
    $resource = PredioEditResource::make($this->predio->load('localidade'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->predio->only(['id', 'nome', 'descricao', 'localidade_id'])
            + ['localidade' => LocalidadeEditResource::make($this->predio->localidade)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = PredioEditResource::make($this->predio->loadCount('andares'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->predio->only(['id', 'nome', 'descricao', 'localidade_id', 'andares_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = PredioEditResource::make($this->predio);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->predio->only(['id', 'nome', 'descricao', 'localidade_id'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PredioEditResource::make(null)->resolve())->toBeEmpty();
});
