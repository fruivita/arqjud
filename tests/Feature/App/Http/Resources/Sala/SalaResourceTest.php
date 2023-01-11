<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Andar\AndarResource;
use App\Http\Resources\Sala\SalaResource;
use App\Models\Permissao;
use App\Models\Sala;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->sala = Sala::factory()->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::ESTANTE_CREATE, Permissao::SALA_DELETE, Permissao::SALA_VIEW, Permissao::SALA_UPDATE]);

    $resource = SalaResource::make($this->sala);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id'])
            + [
                'links' => [
                    'view' => route('cadastro.sala.edit', $this->sala),
                    'update' => route('cadastro.sala.update', $this->sala),
                    'delete' => route('cadastro.sala.destroy', $this->sala),
                    'estante' => [
                        'create' => route('cadastro.estante.create', $this->sala),
                        'store' => route('cadastro.estante.store', $this->sala),
                    ],
                ],
            ],
    ]);
});

test('retorna o andar pai se houver o eager load da propriedade', function () {
    $resource = SalaResource::make($this->sala->load('andar'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id'])
            + ['andar' => AndarResource::make($this->sala->andar)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = SalaResource::make($this->sala->loadCount('estantes'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id', 'estantes_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = SalaResource::make($this->sala);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->sala->only(['id', 'numero', 'descricao', 'andar_id'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(SalaResource::make(null)->resolve())->toBeEmpty();
});
