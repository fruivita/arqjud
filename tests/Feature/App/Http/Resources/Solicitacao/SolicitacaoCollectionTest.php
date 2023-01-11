<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Solicitacao\SolicitacaoCollection;
use App\Http\Resources\Solicitacao\SolicitacaoResource;
use App\Models\Permissao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = Usuario::factory()->create();
    Auth::login($this->usuario);

    $this->solicitacoes = Solicitacao::factory(2)->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::SOLICITACAO_CREATE, Permissao::SOLICITACAO_EXTERNA_CREATE]);

    $resource = SolicitacaoCollection::make($this->solicitacoes);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->solicitacoes->count())
        ->and($dados['links'])->toMatchArray([
            'create' => route('atendimento.solicitar-processo.create'),
            'externo_create' => route('solicitacao.create'),
        ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = SolicitacaoCollection::make($this->solicitacoes);

    $dados = $resource->response()->getData(true);

    expect($dados['links'])->toBeEmpty();
});

test('collection resolve o resource correto', function () {
    $resource = SolicitacaoCollection::make($this->solicitacoes);

    expect($resource->collects)->toBe(SolicitacaoResource::class);
});
