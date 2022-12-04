<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Estante\EstanteResource;
use App\Models\Permissao;
use App\Models\Estante;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->estante = Estante::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PRATELEIRA_CREATE, Permissao::ESTANTE_DELETE, Permissao::ESTANTE_VIEW]);

    $resource = EstanteResource::make($this->estante);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'sala_id'])
            + [
                'links' => [
                    'create_prateleira' => route('cadastro.prateleira.create', $this->estante),
                    'view_or_update' => route('cadastro.estante.edit', $this->estante),
                    'delete' => route('cadastro.estante.destroy', $this->estante),
                ],
            ],
    ]);
});

test('retorna a sala pai se houver o eager load da propriedade', function () {
    $resource = EstanteResource::make($this->estante->load('sala'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'sala_id'])
            + ['sala' => $this->estante->sala->only(['id', 'numero', 'andar_id'])]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = EstanteResource::make($this->estante->loadCount('prateleiras'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'sala_id', 'prateleiras_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = EstanteResource::make($this->estante);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->estante->only(['id', 'numero', 'sala_id'])
            + ['links' => []],
    ]);
});
