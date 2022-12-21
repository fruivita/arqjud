<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\VolumeCaixa\VolumeCaixaOnlyResource;
use App\Models\VolumeCaixa;

beforeEach(function () {
    $this->volume = VolumeCaixa::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = VolumeCaixaOnlyResource::make($this->volume);

    expect($resource->response()->getData(true))->toMatchArray(['data' => volumeApi($this->volume)]);
});

test('retorna a caixa pai se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaOnlyResource::make($this->volume->load(['caixa']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => volumeApi($this->volume) + ['caixa' => caixaApi($this->volume->caixa)],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = VolumeCaixaOnlyResource::make($this->volume->loadCount('processos'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => volumeApi($this->volume) + $this->volume->only('processos_count'),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(VolumeCaixaOnlyResource::make(null)->resolve())->toBeEmpty();
});
