<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

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
        'caixa_id' => $this->volume->caixa_id,
    ];
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PROCESSO_CREATE, Permissao::VOLUME_CAIXA_DELETE, Permissao::VOLUME_CAIXA_VIEW]);

    $resource = VolumeCaixaResource::make($this->volume);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->volume_api
            + [
                'links' => [
                    'create_processo' => route('cadastro.processo.create', $this->volume),
                    'view_or_update' => route('cadastro.volumeCaixa.edit', $this->volume),
                    'delete' => route('cadastro.volumeCaixa.destroy', $this->volume),
                ],
            ],
    ]);
});

test('retorna a caixa pai se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaResource::make($this->volume->load(['caixa']));

    $caixa_api = [
        'id' => $this->volume->caixa->id,
        'numero' => $this->volume->caixa->numero,
        'ano' => $this->volume->caixa->ano,
        'guarda_permanente' => $this->volume->caixa->guarda_permanente ? __('Sim') : __('Não'),
        'complemento' => $this->volume->caixa->complemento,
        'prateleira_id' => $this->volume->caixa->prateleira_id,
        'localidade_criadora_id' => $this->volume->caixa->localidade_criadora_id,
    ];

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->volume_api
            + ['caixa' => $caixa_api]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaResource::make($this->volume->loadCount('processos'));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->volume_api
            + $this->volume->only('processos_count')
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = VolumeCaixaResource::make($this->volume);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->volume_api
            + ['links' => []],
    ]);
});
