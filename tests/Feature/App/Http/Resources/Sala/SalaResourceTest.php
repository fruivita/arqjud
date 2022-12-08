<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Sala\SalaResource;
use App\Models\Permissao;
use App\Models\Sala;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->sala = Sala::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::ESTANTE_CREATE, Permissao::SALA_DELETE, Permissao::SALA_VIEW, Permissao::SALA_UPDATE]);

    $resource = SalaResource::make($this->sala);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->sala->only(['id', 'numero', 'andar_id'])
            + [
                'links' => [
                    'view' => route('cadastro.sala.edit', $this->sala),
                    'update' => route('cadastro.sala.update', $this->sala),
                    'delete' => route('cadastro.sala.destroy', $this->sala),
                    'create_estante' => route('cadastro.estante.create', $this->sala),
                ],
            ],
    ]);
});

test('retorna o andar pai se houver o eager load da propriedade', function () {
    $resource = SalaResource::make($this->sala->load('andar'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->sala->only(['id', 'numero', 'andar_id'])
            + ['andar' => $this->sala->andar->only(['id', 'numero', 'apelido', 'predio_id'])]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = SalaResource::make($this->sala->loadCount('estantes'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->sala->only(['id', 'numero', 'andar_id', 'estantes_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = SalaResource::make($this->sala);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->sala->only(['id', 'numero', 'andar_id'])
            + ['links' => []],
    ]);
});
