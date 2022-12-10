<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaResource;
use App\Models\Caixa;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->caixa = Caixa::factory()->create();

    $this->caixa_api = [
        'id' => $this->caixa->id,
        'numero' => $this->caixa->numero,
        'ano' => $this->caixa->ano,
        'guarda_permanente' => $this->caixa->guarda_permanente ? __('Sim') : __('Não'),
        'complemento' => $this->caixa->complemento,
        'prateleira_id' => $this->caixa->prateleira_id,
        'localidade_criadora_id' => $this->caixa->localidade_criadora_id,
    ];
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::VOLUME_CAIXA_CREATE, Permissao::CAIXA_DELETE, Permissao::CAIXA_VIEW, Permissao::CAIXA_UPDATE]);

    $resource = CaixaResource::make($this->caixa);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->caixa_api
            + [
                'links' => [
                    'view' => route('cadastro.caixa.edit', $this->caixa),
                    'update' => route('cadastro.caixa.update', $this->caixa),
                    'delete' => route('cadastro.caixa.destroy', $this->caixa),
                    'create_volume' => route('cadastro.volumeCaixa.create', $this->caixa),
                ],
            ],
    ]);
});

test('retorna a prateleira pai e a localidade criadora se houver o eager load da propriedade', function () {
    $resource = CaixaResource::make($this->caixa->load(['prateleira', 'localidadeCriadora']));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->caixa_api
            + ['prateleira' => $this->caixa->prateleira->only(['id', 'numero', 'estante_id'])]
            + ['localidade_criadora' => $this->caixa->localidadeCriadora->only(['id', 'nome'])]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = CaixaResource::make($this->caixa->loadCount('volumes'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->caixa_api
            + $this->caixa->only('volumes_count')
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = CaixaResource::make($this->caixa);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->caixa_api
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(CaixaResource::make(null)->resolve())->toBeEmpty();
});
