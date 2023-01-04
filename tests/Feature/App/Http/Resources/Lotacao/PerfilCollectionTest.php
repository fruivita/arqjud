<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Lotacao\LotacaoCollection;
use App\Http\Resources\Lotacao\LotacaoResource;
use App\Models\Lotacao;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->lotacoes = Lotacao::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = LotacaoCollection::make($this->lotacoes);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->lotacoes->count());
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = LotacaoCollection::make($this->lotacoes);

    $dados = $resource->response()->getData(true);

    expect($dados)
        ->toHaveKey('data')
        ->not->toHaveKey('links');
});

test('collection resolve o resource correto', function () {
    $resource = LotacaoCollection::make($this->lotacoes);

    expect($resource->collects)->toBe(LotacaoResource::class);
});
