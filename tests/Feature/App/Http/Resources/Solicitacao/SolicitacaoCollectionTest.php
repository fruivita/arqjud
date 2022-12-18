<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Solicitacao\SolicitacaoCollection;
use App\Http\Resources\Solicitacao\SolicitacaoResource;
use App\Models\Permissao;
use App\Models\Solicitacao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->solicitacoes = Solicitacao::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::SOLICITACAO_CREATE]);

    $resource = SolicitacaoCollection::make($this->solicitacoes);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->solicitacoes->count())
        ->and($dados['links'])->toBe(['create' => route('solicitacao.create')]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = SolicitacaoCollection::make($this->solicitacoes);

    $dados = $resource->response()->getData(true);

    expect($dados)
        ->toHaveKey('data')
        ->not->toHaveKey('links');
});

test('collection resolve o resource correto', function () {
    $resource = SolicitacaoCollection::make($this->solicitacoes);

    expect($resource->collects)->toBe(SolicitacaoResource::class);
});
