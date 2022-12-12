<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Processo\ProcessoResource;
use App\Http\Resources\VolumeCaixa\VolumeCaixaResource;
use App\Models\Permissao;
use App\Models\Processo;
use Database\Seeders\PerfilSeeder;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    testTime()->freeze();
    $this->seed([PerfilSeeder::class]);
    login();

    $this->processo = Processo::factory()->for(Processo::factory(), 'processoPai')->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PROCESSO_DELETE, Permissao::PROCESSO_VIEW, Permissao::PROCESSO_UPDATE]);

    $resource = ProcessoResource::make($this->processo);

    expect($resource->response()->getData(true))->toBe([
        'data' => processoApi($this->processo)
            + [
                'links' => [
                    'view' => route('cadastro.processo.edit', $this->processo),
                    'update' => route('cadastro.processo.update', $this->processo),
                    'delete' => route('cadastro.processo.destroy', $this->processo),
                ],
            ],
    ]);
});

test('retorna o volume da caixa pai e o processo pai se houver o eager load da propriedade', function () {
    $resource = ProcessoResource::make($this->processo->load(['volumeCaixa', 'processoPai']));
    expect($resource->response()->getData(true))->toBe([
        'data' => processoApi($this->processo)
            + ['volume_caixa' => VolumeCaixaResource::make($this->processo->volumeCaixa)->resolve()]
            + ['processo_pai' => ProcessoResource::make($this->processo->processoPai)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = ProcessoResource::make($this->processo->loadCount(['processosFilho', 'solicitacoes']));

    expect($resource->response()->getData(true))->toBe([
        'data' => processoApi($this->processo) // @phpstan-ignore-line
            + $this->processo->only('processos_filho_count')
            + $this->processo->only('solicitacoes_count')
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = ProcessoResource::make($this->processo);

    expect($resource->response()->getData(true))->toBe([
        'data' => processoApi($this->processo)
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(ProcessoResource::make(null)->resolve())->toBeEmpty();
});
