<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Models\Processo;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->processo = Processo::factory()->for(Processo::factory(), 'processoPai')->create();

    $this->processo_api = [
        'id' => $this->processo->id,
        'numero' => $this->processo->numero,
        'numero_antigo' => $this->processo->numero_antigo,
        'arquivado_em' => $this->processo->arquivado_em,
        'guarda_permanente' => $this->processo->guarda_permanente ? __('Sim') : __('Não'),
        'qtd_volumes' => $this->processo->qtd_volumes,
        'volume_caixa_id' => $this->processo->volume_caixa_id,
        'processo_pai_id' => $this->processo->processo_pai_id,
    ];
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = ProcessoOnlyResource::make($this->processo);

    expect($resource->response(request())->getData(true))->toBe(['data' => $this->processo_api]);
});

test('retorna o volume da caixa pai e o processo pai se houver o eager load da propriedade', function () {
    $resource = ProcessoOnlyResource::make($this->processo->load(['volumeCaixa', 'processoPai']));

    $processo_pai_api = [
        'id' => $this->processo->processoPai->id,
        'numero' => $this->processo->processoPai->numero,
        'numero_antigo' => $this->processo->processoPai->numero_antigo,
        'arquivado_em' => $this->processo->processoPai->arquivado_em,
        'guarda_permanente' => $this->processo->processoPai->guarda_permanente ? __('Sim') : __('Não'),
        'qtd_volumes' => $this->processo->processoPai->qtd_volumes,
        'volume_caixa_id' => $this->processo->processoPai->volume_caixa_id,
        'processo_pai_id' => $this->processo->processoPai->processo_pai_id,
    ];

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->processo_api
            + ['volume_caixa' => $this->processo->volumeCaixa->only(['id', 'numero', 'caixa_id'])]
            + ['processo_pai' => $processo_pai_api],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = ProcessoOnlyResource::make($this->processo->loadCount(['processosFilho', 'solicitacoes']));

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->processo_api
            + $this->processo->only('processos_filho_count')
            + $this->processo->only('solicitacoes_count')
    ]);
});
