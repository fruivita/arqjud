<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Predio\PredioResource;
use App\Models\Permissao;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->predio = Predio::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::ANDAR_CREATE, Permissao::PREDIO_DELETE, Permissao::PREDIO_VIEW]);

    $resource = PredioResource::make($this->predio);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'localidade_id'])
            + [
                'links' => [
                    'create_andar' => route('cadastro.andar.create', $this->predio),
                    'view_or_update' => route('cadastro.predio.edit', $this->predio),
                    'delete' => route('cadastro.predio.destroy', $this->predio),
                ],
            ],
    ]);
});

test('retorna a localidade pai se houver o eager load da propriedade', function () {
    $resource = PredioResource::make($this->predio->load('localidade'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'localidade_id'])
            + ['localidade' => $this->predio->localidade->only(['id', 'nome'])]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = PredioResource::make($this->predio->loadCount('andares'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'localidade_id', 'andares_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = PredioResource::make($this->predio);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome', 'localidade_id'])
            + ['links' => []],
    ]);
});
