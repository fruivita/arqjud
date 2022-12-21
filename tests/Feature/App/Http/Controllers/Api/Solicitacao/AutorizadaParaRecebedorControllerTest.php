<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 */

use App\Http\Controllers\Api\Solicitacao\AutorizadaParaRecebedorController;
use App\Http\Requests\Api\Solicitacao\ShowAutorizadaParaRecebedorRequest;
use App\Http\Resources\Solicitacao\SolicitacaoOnlyResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Permissao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->recebedor = Usuario::factory()->create();
});

// Autorização
test('usuário sem autenticação não consegue os dados das solicitações de processo para entrega por meio da API JSON', function () {
    $this->postJson(route('api.solicitacao.entregas-autorizadas.show'), ['recebedor' => $this->recebedor->username])
        ->assertUnauthorized();
});

test('usuário sem autorização não consegue os dados das solicitações de processo para entrega por meio da API JSON', function () {
    login();

    $this->postJson(route('api.solicitacao.entregas-autorizadas.show'), ['recebedor' => $this->recebedor->username])
        ->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        AutorizadaParaRecebedorController::class,
        $action,
        $request
    );
})->with([
    ['show', ShowAutorizadaParaRecebedorRequest::class],
]);

test('usuário autorizado consegue os dados das solicitações de processo para entrega por meio da API JSON', function () {
    login();

    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $solicitacoes = Solicitacao::factory(2)->solicitada()->create(['lotacao_destinataria_id' => $this->recebedor->lotacao_id]);
    Solicitacao::factory(3)->solicitada()->create();

    $response = $this->postJson(route('api.solicitacao.entregas-autorizadas.show'), ['recebedor' => $this->recebedor->username]);

    $response
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->whereAll([
                'recebedor' => data_get(UsuarioOnlyResource::make($this->recebedor->loadMissing('lotacao'))->response()->getData(true), 'data'),
                'solicitacoes' => data_get(SolicitacaoOnlyResource::collection($solicitacoes->load(['processo', 'solicitante', 'lotacaoDestinataria']))->response()->getData(true), 'data'),
            ])
        );
});
