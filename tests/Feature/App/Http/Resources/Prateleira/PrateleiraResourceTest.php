<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Prateleira\PrateleiraResource;
use App\Models\Permissao;
use App\Models\Prateleira;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->prateleira = Prateleira::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::CAIXA_CREATE, Permissao::PRATELEIRA_DELETE, Permissao::PRATELEIRA_VIEW, Permissao::PRATELEIRA_UPDATE]);

    $resource = PrateleiraResource::make($this->prateleira);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->prateleira->only(['id', 'numero', 'estante_id'])
            + [
                'links' => [
                    'view' => route('cadastro.prateleira.edit', $this->prateleira),
                    'update' => route('cadastro.prateleira.update', $this->prateleira),
                    'delete' => route('cadastro.prateleira.destroy', $this->prateleira),
                    'create_caixa' => route('cadastro.caixa.create', $this->prateleira),
                ],
            ],
    ]);
});

test('retorna a estante pai se houver o eager load da propriedade', function () {
    $resource = PrateleiraResource::make($this->prateleira->load('estante'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->prateleira->only(['id', 'numero', 'estante_id'])
            + ['estante' => $this->prateleira->estante->only(['id', 'numero', 'sala_id'])]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = PrateleiraResource::make($this->prateleira->loadCount('caixas'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->prateleira->only(['id', 'numero', 'estante_id', 'caixas_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = PrateleiraResource::make($this->prateleira);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->prateleira->only(['id', 'numero', 'estante_id'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PrateleiraResource::make(null)->resolve())->toBeEmpty();
});
