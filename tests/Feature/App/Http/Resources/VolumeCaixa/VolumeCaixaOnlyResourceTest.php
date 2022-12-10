<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\VolumeCaixa\VolumeCaixaOnlyResource;
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
test('retorna os campos principais do modelo', function () {
    $resource = VolumeCaixaOnlyResource::make($this->volume);

    expect($resource->response()->getData(true))->toBe(['data' => $this->volume_api]);
});

test('retorna a caixa pai se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaOnlyResource::make($this->volume->load(['caixa']));

    $caixa_api = [
        'id' => $this->volume->caixa->id,
        'numero' => $this->volume->caixa->numero,
        'ano' => $this->volume->caixa->ano,
        'guarda_permanente' => $this->volume->caixa->guarda_permanente ? __('Sim') : __('Não'),
        'complemento' => $this->volume->caixa->complemento,
        'descricao' => $this->volume->caixa->descricao,
        'prateleira_id' => $this->volume->caixa->prateleira_id,
        'localidade_criadora_id' => $this->volume->caixa->localidade_criadora_id,
    ];

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->volume_api
            + ['caixa' => $caixa_api],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaOnlyResource::make($this->volume->loadCount('processos'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->volume_api
            + $this->volume->only('processos_count'),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(VolumeCaixaOnlyResource::make(null)->resolve())->toBeEmpty();
});
