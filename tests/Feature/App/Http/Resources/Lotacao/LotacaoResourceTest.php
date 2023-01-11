<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Lotacao\LotacaoResource;
use App\Models\Lotacao;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->lotacao = Lotacao::factory()->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::LOTACAO_UPDATE]);

    $resource = LotacaoResource::make($this->lotacao);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => lotacaoApi($this->lotacao)
            + [
                'links' => [
                    'update' => route('administracao.lotacao.update', $this->lotacao),
                ],
            ],
    ]);
});

test('retorna os elementos relacionados se houver o eager load da propriedade', function () {
    $lotacaoPai = Lotacao::factory()->create();
    $this->lotacao->lotacaoPai()->associate($lotacaoPai)->save();
    Usuario::factory(3)->for($this->lotacao, 'lotacao')->create();

    $resource = LotacaoResource::make($this->lotacao->loadCount('usuarios')->load(['lotacaoPai']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => lotacaoApi($this->lotacao)
            + ['lotacao_pai' => lotacaoApi($lotacaoPai)]
            + ['usuarios_count' => 3]
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = LotacaoResource::make($this->lotacao);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => lotacaoApi($this->lotacao)
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(LotacaoResource::make(null)->resolve())->toBeEmpty();
});
