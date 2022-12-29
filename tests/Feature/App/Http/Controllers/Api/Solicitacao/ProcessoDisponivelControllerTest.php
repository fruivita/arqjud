<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 */

use App\Http\Controllers\Api\Solicitacao\ProcessoDisponivelController;
use App\Http\Requests\Api\Solicitacao\ShowProcessoDisponivelRequest;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->processo = Processo::factory()->create();
});

// Autorização
test('usuário sem autenticação não consegue os dados do processo para solicitação por meio da API JSON', function () {
    $this->postJson(route('api.solicitacao.processo.show'), ['processo' => $this->processo->numero])
        ->assertUnauthorized();
});

test('usuário sem autorização não consegue os dados do processo para solicitação por meio da API JSON', function () {
    Auth::login(Usuario::factory()->create());

    $this->postJson(route('api.solicitacao.processo.show'), ['processo' => $this->processo->numero])
        ->assertForbidden();

    logout();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        ProcessoDisponivelController::class,
        $action,
        $request
    );
})->with([
    ['show', ShowProcessoDisponivelRequest::class],
]);

test('usuário autorizado consegue os dados de um processo para solicitação por meio da API JSON', function () {
    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    $response = $this->postJson(route('api.solicitacao.processo.show'), ['numero' => $this->processo->numero]);

    $response
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('processo', processoApi($this->processo))
        );

    logout();
});
