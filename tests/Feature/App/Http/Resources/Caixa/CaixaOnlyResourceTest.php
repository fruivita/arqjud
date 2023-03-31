<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaOnlyResource;
use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Resources\Prateleira\PrateleiraOnlyResource;
use App\Http\Resources\TipoProcesso\TipoProcessoOnlyResource;
use App\Models\Caixa;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->caixa = Caixa::factory()->create();

    Auth::login(Usuario::factory()->create());
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = CaixaOnlyResource::make($this->caixa);

    expect($resource->response()->getData(true))->toMatchArray(['data' => caixaApi($this->caixa)]);
});

test('retorna a prateleira pai, localidade criadora e o tipo de processo se houver o eager load da propriedade', function () {
    $resource = CaixaOnlyResource::make($this->caixa->load(['prateleira', 'localidadeCriadora', 'tipoProcesso']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => caixaApi($this->caixa)
            + ['prateleira' => PrateleiraOnlyResource::make($this->caixa->prateleira)->resolve()]
            + ['localidade_criadora' => LocalidadeOnlyResource::make($this->caixa->localidadeCriadora)->resolve()]
            + ['tipo_processo' => TipoProcessoOnlyResource::make($this->caixa->tipoProcesso)->resolve()],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = CaixaOnlyResource::make($this->caixa->loadCount('processos'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => caixaApi($this->caixa)
            + $this->caixa->only('processos_count'),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(CaixaOnlyResource::make(null)->resolve())->toBeEmpty();
});
