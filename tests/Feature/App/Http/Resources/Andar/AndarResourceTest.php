<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Andar\AndarResource;
use App\Http\Resources\Predio\PredioResource;
use App\Models\Andar;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->andar = Andar::factory()->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::SALA_CREATE, Permissao::ANDAR_DELETE, Permissao::ANDAR_VIEW, Permissao::ANDAR_UPDATE]);

    $resource = AndarResource::make($this->andar);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'descricao', 'predio_id'])
            + [
                'links' => [
                    'view' => route('cadastro.andar.edit', $this->andar),
                    'update' => route('cadastro.andar.update', $this->andar),
                    'delete' => route('cadastro.andar.destroy', $this->andar),
                    'sala' => [
                        'create' => route('cadastro.sala.create', $this->andar),
                        'store' => route('cadastro.sala.store', $this->andar),
                    ],
                ],
            ],
    ]);
});

test('retorna o prédio pai se houver o eager load da propriedade', function () {
    $resource = AndarResource::make($this->andar->load('predio'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'descricao', 'predio_id'])
            + ['predio' => PredioResource::make($this->andar->predio)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = AndarResource::make($this->andar->loadCount('salas'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'descricao', 'predio_id', 'salas_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = AndarResource::make($this->andar);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'descricao', 'predio_id'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(AndarResource::make(null)->resolve())->toBeEmpty();
});
