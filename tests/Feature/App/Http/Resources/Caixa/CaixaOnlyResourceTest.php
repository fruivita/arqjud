<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaOnlyResource;
use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Resources\Prateleira\PrateleiraOnlyResource;
use App\Models\Caixa;

beforeEach(function () {
    $this->caixa = Caixa::factory()->create();

    $this->caixa_api = [
        'id' => $this->caixa->id,
        'numero' => $this->caixa->numero,
        'ano' => $this->caixa->ano,
        'guarda_permanente' => $this->caixa->guarda_permanente ? __('Sim') : __('Não'),
        'complemento' => $this->caixa->complemento,
        'descricao' => $this->caixa->descricao,
        'prateleira_id' => $this->caixa->prateleira_id,
        'localidade_criadora_id' => $this->caixa->localidade_criadora_id,
    ];
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = CaixaOnlyResource::make($this->caixa);

    expect($resource->response()->getData(true))->toBe(['data' => $this->caixa_api]);
});

test('retorna a prateleira pai e localidade criadora se houver o eager load da propriedade', function () {
    $resource = CaixaOnlyResource::make($this->caixa->load(['prateleira', 'localidadeCriadora']));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->caixa_api
            + ['prateleira' => PrateleiraOnlyResource::make($this->caixa->prateleira)->resolve()]
            + ['localidade_criadora' => LocalidadeOnlyResource::make($this->caixa->localidadeCriadora)->resolve()],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = CaixaOnlyResource::make($this->caixa->loadCount('volumes'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->caixa_api
            + $this->caixa->only('volumes_count'),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(CaixaOnlyResource::make(null)->resolve())->toBeEmpty();
});
