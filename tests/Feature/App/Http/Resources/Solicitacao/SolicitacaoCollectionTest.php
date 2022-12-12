<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Solicitacao\SolicitacaoCollection;
use App\Http\Resources\Solicitacao\SolicitacaoResource;
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
test('retorna os campos principais do modelo', function () {
    $resource = SolicitacaoCollection::make($this->solicitacoes);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->solicitacoes->count());
});

test('collection resolve o resource correto', function () {
    $resource = SolicitacaoCollection::make($this->solicitacoes);

    expect($resource->collects)->toBe(SolicitacaoResource::class);
});
