<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaOnlyResource;
use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Http\Resources\Solicitacao\SolicitacaoOnlyResource;
use App\Models\Processo;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    testTime()->freeze();

    $this->processo = Processo::factory()
        ->for(Processo::factory(), 'processoPai')
        ->hasProcessosFilho(Processo::factory(2))
        ->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = ProcessoOnlyResource::make($this->processo);

    expect($resource->response()->getData(true))->toMatchArray(['data' => processoApi($this->processo)]);
});

test('retorna a caixa pai, o processo pai, os processos filhos e a solicitação ativa se houver o eager load da propriedade', function () {
    $resource = ProcessoOnlyResource::make($this->processo->load(['caixa', 'processoPai', 'processosFilho', 'solicitacoesAtivas']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => processoApi($this->processo)
            + ['caixa' => CaixaOnlyResource::make($this->processo->caixa)->resolve()]
            + ['processo_pai' => ProcessoOnlyResource::make($this->processo->processoPai)->resolve()]
            + ['processos_filho' => ProcessoOnlyResource::collection($this->processo->processosFilho)->resolve()]
            + ['solicitacao_ativa' => SolicitacaoOnlyResource::collection($this->processo->solicitacoesAtivas)->resolve()],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = ProcessoOnlyResource::make($this->processo->loadCount(['processosFilho', 'solicitacoes']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => processoApi($this->processo)
            + $this->processo->only('processos_filho_count')
            + $this->processo->only('solicitacoes_count'),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(ProcessoOnlyResource::make(null)->resolve())->toBeEmpty();
});
