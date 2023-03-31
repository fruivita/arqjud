<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Caixa\CaixaEditResource;
use App\Http\Resources\Localidade\LocalidadeEditResource;
use App\Http\Resources\Prateleira\PrateleiraEditResource;
use App\Http\Resources\TipoProcesso\TipoProcessoEditResource;
use App\Models\Caixa;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    Auth::login(Usuario::factory()->create());

    $this->caixa = Caixa::factory()->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PROCESSO_CREATE, Permissao::CAIXA_DELETE, Permissao::CAIXA_VIEW, Permissao::CAIXA_UPDATE]);

    $resource = CaixaEditResource::make($this->caixa);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => caixaApi($this->caixa)
            + [
                'links' => [
                    'view' => route('cadastro.caixa.edit', $this->caixa),
                    'update' => route('cadastro.caixa.update', $this->caixa),
                    'processo' => [
                        'create' => route('cadastro.processo.create', $this->caixa),
                        'store' => route('cadastro.processo.store', $this->caixa),
                    ],
                ],
            ],
    ]);
});

test('retorna a prateleira pai, tipo de processo e a localidade criadora se houver o eager load da propriedade', function () {
    $resource = CaixaEditResource::make($this->caixa->load(['prateleira', 'localidadeCriadora', 'tipoProcesso']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => caixaApi($this->caixa)
            + ['prateleira' => PrateleiraEditResource::make($this->caixa->prateleira)->resolve()]
            + ['localidade_criadora' => LocalidadeEditResource::make($this->caixa->localidadeCriadora)->resolve()]
            + ['tipo_processo' => TipoProcessoEditResource::make($this->caixa->tipoProcesso)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = CaixaEditResource::make($this->caixa->loadCount('processos'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => caixaApi($this->caixa)
            + $this->caixa->only('processos_count')
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = CaixaEditResource::make($this->caixa);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => caixaApi($this->caixa)
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(CaixaEditResource::make(null)->resolve())->toBeEmpty();
});
