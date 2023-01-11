<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaEditResource;
use App\Http\Resources\VolumeCaixa\VolumeCaixaEditResource;
use App\Models\Permissao;
use App\Models\VolumeCaixa;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->volume = VolumeCaixa::factory()->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PROCESSO_CREATE, Permissao::VOLUME_CAIXA_DELETE, Permissao::VOLUME_CAIXA_VIEW, Permissao::VOLUME_CAIXA_UPDATE]);

    $resource = VolumeCaixaEditResource::make($this->volume);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => volumeApi($this->volume)
            + [
                'links' => [
                    'view' => route('cadastro.volume-caixa.edit', $this->volume),
                    'update' => route('cadastro.volume-caixa.update', $this->volume),
                    'processo' => [
                        'create' => route('cadastro.processo.create', $this->volume),
                        'store' => route('cadastro.processo.store', $this->volume),
                    ],
                ],
            ],
    ]);
});

test('retorna a caixa pai se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaEditResource::make($this->volume->load(['caixa']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => volumeApi($this->volume)
            + ['caixa' => CaixaEditResource::make($this->volume->caixa)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaEditResource::make($this->volume->loadCount('processos'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => volumeApi($this->volume)
            + $this->volume->only('processos_count')
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = VolumeCaixaEditResource::make($this->volume);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => volumeApi($this->volume)
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(VolumeCaixaEditResource::make(null)->resolve())->toBeEmpty();
});
