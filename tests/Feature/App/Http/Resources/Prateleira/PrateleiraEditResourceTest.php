<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Estante\EstanteEditResource;
use App\Http\Resources\Prateleira\PrateleiraEditResource;
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

    $resource = PrateleiraEditResource::make($this->prateleira);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->prateleira->only(['id', 'numero', 'descricao', 'estante_id'])
            + [
                'links' => [
                    'view' => route('cadastro.prateleira.edit', $this->prateleira),
                    'update' => route('cadastro.prateleira.update', $this->prateleira),
                    'caixa' => [
                        'create' => route('cadastro.caixa.create', $this->prateleira),
                        'store' => route('cadastro.caixa.store', $this->prateleira),
                    ],
                ],
            ],
    ]);
});

test('retorna a estante pai se houver o eager load da propriedade', function () {
    $resource = PrateleiraEditResource::make($this->prateleira->load('estante'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->prateleira->only(['id', 'numero', 'descricao', 'estante_id'])
            + ['estante' => EstanteEditResource::make($this->prateleira->estante)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = PrateleiraEditResource::make($this->prateleira->loadCount('caixas'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->prateleira->only(['id', 'numero', 'descricao', 'estante_id', 'caixas_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = PrateleiraEditResource::make($this->prateleira);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->prateleira->only(['id', 'numero', 'descricao', 'estante_id'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PrateleiraEditResource::make(null)->resolve())->toBeEmpty();
});
