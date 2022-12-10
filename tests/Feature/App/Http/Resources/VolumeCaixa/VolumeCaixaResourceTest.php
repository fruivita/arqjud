<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaResource;
use App\Http\Resources\VolumeCaixa\VolumeCaixaResource;
use App\Models\Permissao;
use App\Models\VolumeCaixa;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->volume = VolumeCaixa::factory()->create();

    $this->volume_api = [
        'id' => $this->volume->id,
        'numero' => $this->volume->numero,
        'descricao' => $this->volume->descricao,
        'caixa_id' => $this->volume->caixa_id,
    ];
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PROCESSO_CREATE, Permissao::VOLUME_CAIXA_DELETE, Permissao::VOLUME_CAIXA_VIEW, Permissao::VOLUME_CAIXA_UPDATE]);

    $resource = VolumeCaixaResource::make($this->volume);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->volume_api
            + [
                'links' => [
                    'view' => route('cadastro.volumeCaixa.edit', $this->volume),
                    'update' => route('cadastro.volumeCaixa.update', $this->volume),
                    'delete' => route('cadastro.volumeCaixa.destroy', $this->volume),
                    'create_processo' => route('cadastro.processo.create', $this->volume),
                ],
            ],
    ]);
});

test('retorna a caixa pai se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaResource::make($this->volume->load(['caixa']));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->volume_api
            + ['caixa' => CaixaResource::make($this->volume->caixa)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaResource::make($this->volume->loadCount('processos'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->volume_api
            + $this->volume->only('processos_count')
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = VolumeCaixaResource::make($this->volume);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->volume_api
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(VolumeCaixaResource::make(null)->resolve())->toBeEmpty();
});
