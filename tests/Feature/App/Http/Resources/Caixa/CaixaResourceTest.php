<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaResource;
use App\Models\Permissao;
use App\Models\Caixa;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->caixa = Caixa::factory()->create();

    $this->caixaApi = [
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
    concederPermissao([Permissao::VOLUME_CAIXA_CREATE, Permissao::CAIXA_DELETE, Permissao::CAIXA_VIEW]);

    $resource = CaixaResource::make($this->caixa);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->caixaApi
            + [
                'links' => [
                    'create_volume' => route('cadastro.volumeCaixa.create', $this->caixa),
                    'view_or_update' => route('cadastro.caixa.edit', $this->caixa),
                    'delete' => route('cadastro.caixa.destroy', $this->caixa),
                ],
            ],
    ]);
});

test('retorna a prateleira pai e a localidade criadora se houver o eager load da propriedade', function () {
    $resource = CaixaResource::make($this->caixa->load(['prateleira', 'localidadeCriadora']));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->caixaApi
            + ['prateleira' => $this->caixa->prateleira->only(['id', 'numero', 'estante_id'])]
            + ['localidade_criadora' => $this->caixa->localidadeCriadora->only(['id', 'nome'])]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = CaixaResource::make($this->caixa->loadCount('volumes'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->caixaApi
            + $this->caixa->only('volumes_count')
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = CaixaResource::make($this->caixa);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->caixaApi
            + ['links' => []],
    ]);
});
