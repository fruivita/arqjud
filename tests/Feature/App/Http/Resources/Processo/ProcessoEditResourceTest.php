<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaEditResource;
use App\Http\Resources\Processo\ProcessoEditResource;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    testTime()->freeze();
    $this->seed([PerfilSeeder::class]);
    Auth::login(Usuario::factory()->create());

    $this->processo = Processo::factory()->for(Processo::factory(), 'processoPai')->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PROCESSO_DELETE, Permissao::PROCESSO_VIEW, Permissao::PROCESSO_UPDATE]);

    $resource = ProcessoEditResource::make($this->processo);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => processoApi($this->processo)
            + [
                'links' => [
                    'view' => route('cadastro.processo.edit', $this->processo),
                    'update' => route('cadastro.processo.update', $this->processo),
                ],
            ],
    ]);
});

test('retornaa caixa pai e o processo pai se houver o eager load da propriedade', function () {
    $resource = ProcessoEditResource::make($this->processo->load(['caixa', 'processoPai']));
    expect($resource->response()->getData(true))->toMatchArray([
        'data' => processoApi($this->processo)
            + ['caixa' => CaixaEditResource::make($this->processo->caixa)->resolve()]
            + ['processo_pai' => ProcessoEditResource::make($this->processo->processoPai)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = ProcessoEditResource::make($this->processo->loadCount(['processosFilho', 'solicitacoes']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => processoApi($this->processo) // @phpstan-ignore-line
            + $this->processo->only('processos_filho_count')
            + $this->processo->only('solicitacoes_count')
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = ProcessoEditResource::make($this->processo);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => processoApi($this->processo)
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(ProcessoEditResource::make(null)->resolve())->toBeEmpty();
});
