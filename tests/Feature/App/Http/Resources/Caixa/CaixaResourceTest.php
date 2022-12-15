<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaResource;
use App\Http\Resources\Localidade\LocalidadeResource;
use App\Http\Resources\Prateleira\PrateleiraResource;
use App\Models\Caixa;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->caixa = Caixa::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::VOLUME_CAIXA_CREATE, Permissao::CAIXA_DELETE, Permissao::CAIXA_VIEW, Permissao::CAIXA_UPDATE]);

    $resource = CaixaResource::make($this->caixa);

    expect($resource->response()->getData(true))->toBe([
        'data' => caixaApi($this->caixa)
            + [
                'links' => [
                    'view' => route('cadastro.caixa.edit', $this->caixa),
                    'update' => route('cadastro.caixa.update', $this->caixa),
                    'delete' => route('cadastro.caixa.destroy', $this->caixa),
                    'create_volume' => route('cadastro.volume-caixa.create', $this->caixa),
                ],
            ],
    ]);
});

test('retorna a prateleira pai e a localidade criadora se houver o eager load da propriedade', function () {
    $resource = CaixaResource::make($this->caixa->load(['prateleira', 'localidadeCriadora']));

    expect($resource->response()->getData(true))->toBe([
        'data' => caixaApi($this->caixa)
            + ['prateleira' => PrateleiraResource::make($this->caixa->prateleira)->resolve()]
            + ['localidade_criadora' => LocalidadeResource::make($this->caixa->localidadeCriadora)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = CaixaResource::make($this->caixa->loadCount('volumes'));

    expect($resource->response()->getData(true))->toBe([
        'data' => caixaApi($this->caixa)
            + $this->caixa->only('volumes_count')
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = CaixaResource::make($this->caixa);

    expect($resource->response()->getData(true))->toBe([
        'data' => caixaApi($this->caixa)
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(CaixaResource::make(null)->resolve())->toBeEmpty();
});
