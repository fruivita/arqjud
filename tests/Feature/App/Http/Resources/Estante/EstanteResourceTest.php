<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Estante\EstanteResource;
use App\Http\Resources\Sala\SalaResource;
use App\Models\Estante;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->estante = Estante::factory()->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PRATELEIRA_CREATE, Permissao::ESTANTE_DELETE, Permissao::ESTANTE_VIEW, Permissao::ESTANTE_UPDATE]);

    $resource = EstanteResource::make($this->estante);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->estante->only(['id', 'numero', 'descricao', 'sala_id'])
            + [
                'links' => [
                    'view' => route('cadastro.estante.edit', $this->estante),
                    'update' => route('cadastro.estante.update', $this->estante),
                    'delete' => route('cadastro.estante.destroy', $this->estante),
                    'prateleira' => [
                        'create' => route('cadastro.prateleira.create', $this->estante),
                        'store' => route('cadastro.prateleira.store', $this->estante),
                    ],
                ],
            ],
    ]);
});

test('retorna a sala pai se houver o eager load da propriedade', function () {
    $resource = EstanteResource::make($this->estante->load('sala'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->estante->only(['id', 'numero', 'descricao', 'sala_id'])
            + ['sala' => SalaResource::make($this->estante->sala)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = EstanteResource::make($this->estante->loadCount('prateleiras'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->estante->only(['id', 'numero', 'descricao', 'sala_id', 'prateleiras_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = EstanteResource::make($this->estante);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->estante->only(['id', 'numero', 'descricao', 'sala_id'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(EstanteResource::make(null)->resolve())->toBeEmpty();
});
