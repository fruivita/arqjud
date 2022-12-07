<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Andar\AndarResource;
use App\Models\Andar;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->andar = Andar::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::SALA_CREATE, Permissao::ANDAR_DELETE, Permissao::ANDAR_VIEW, Permissao::ANDAR_UPDATE]);

    $resource = AndarResource::make($this->andar);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'predio_id'])
            + [
                'links' => [
                    'view' => route('cadastro.andar.edit', $this->andar),
                    'update' => route('cadastro.andar.update', $this->andar),
                    'delete' => route('cadastro.andar.destroy', $this->andar),
                    'create_sala' => route('cadastro.sala.create', $this->andar),
                ],
            ],
    ]);
});

test('retorna o prédio pai se houver o eager load da propriedade', function () {
    $resource = AndarResource::make($this->andar->load('predio'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'predio_id'])
            + ['predio' => $this->andar->predio->only(['id', 'nome', 'localidade_id'])]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = AndarResource::make($this->andar->loadCount('salas'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'predio_id', 'salas_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = AndarResource::make($this->andar);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->andar->only(['id', 'numero', 'apelido', 'predio_id'])
            + ['links' => []],
    ]);
});
