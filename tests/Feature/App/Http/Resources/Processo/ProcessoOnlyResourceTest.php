<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Http\Resources\Solicitacao\SolicitacaoOnlyResource;
use App\Http\Resources\VolumeCaixa\VolumeCaixaOnlyResource;
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

    expect($resource->response()->getData(true))->toBe(['data' => processoApi($this->processo)]);
});

test('retorna o volume da caixa pai, o processo pai, os processos filhos e a solicitação ativa se houver o eager load da propriedade', function () {
    $resource = ProcessoOnlyResource::make($this->processo->load(['volumeCaixa', 'processoPai', 'processosFilho', 'solicitacoesAtivas']));

    expect($resource->response()->getData(true))->toBe([
        'data' => processoApi($this->processo)
            + ['volume_caixa' => VolumeCaixaOnlyResource::make($this->processo->volumeCaixa)->resolve()]
            + ['processo_pai' => ProcessoOnlyResource::make($this->processo->processoPai)->resolve()]
            + ['processos_filho' => ProcessoOnlyResource::collection($this->processo->processosFilho)->resolve()]
            + ['solicitacao_ativa' => SolicitacaoOnlyResource::collection($this->processo->solicitacoesAtivas)->resolve()],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = ProcessoOnlyResource::make($this->processo->loadCount(['processosFilho', 'solicitacoes']));

    expect($resource->response()->getData(true))->toBe([
        'data' => processoApi($this->processo)
            + $this->processo->only('processos_filho_count')
            + $this->processo->only('solicitacoes_count'),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(ProcessoOnlyResource::make(null)->resolve())->toBeEmpty();
});

function processoApi(Processo $processo)
{
    return [
        'id' => $processo->id,
        'numero' => $processo->numero,
        'numero_antigo' => $processo->numero_antigo,
        'arquivado_em' => $processo->arquivado_em->format('d-m-Y'),
        'guarda_permanente' => $processo->guarda_permanente ? __('Sim') : __('Não'),
        'qtd_volumes' => $processo->qtd_volumes,
        'descricao' => $processo->descricao,
        'volume_caixa_id' => $processo->volume_caixa_id,
        'processo_pai_id' => $processo->processo_pai_id,
    ];
}
