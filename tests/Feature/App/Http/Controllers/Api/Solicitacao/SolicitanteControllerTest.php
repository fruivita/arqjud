<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 */

use App\Http\Controllers\Api\Solicitacao\SolicitanteController;
use App\Http\Requests\Api\Solicitacao\ShowSolicitanteRequest;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->solicitante = Usuario::factory()->create();
});

// Autorização
test('usuário sem autenticação não consegue os dados do solicitante por meio da API JSON', function () {
    $this->postJson(route('api.solicitacao.solicitante.show'), ['solicitante' => $this->solicitante->username])
        ->assertUnauthorized();
});

test('usuário sem autorização não consegue os dados do solicitante por meio da API JSON', function () {
    Auth::login(Usuario::factory()->create());

    $this->postJson(route('api.solicitacao.solicitante.show'), ['solicitante' => $this->solicitante->username])
        ->assertForbidden();

    logout();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        SolicitanteController::class,
        $action,
        $request
    );
})->with([
    ['show', ShowSolicitanteRequest::class],
]);

test('usuário autorizado consegue os dados do solicitante por meio da API JSON', function () {
    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::SOLICITACAO_CREATE);

    $response = $this->postJson(route('api.solicitacao.solicitante.show'), ['solicitante' => $this->solicitante->username]);

    $response
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->whereAll([
                'solicitante' => data_get(UsuarioOnlyResource::make($this->solicitante->load('lotacao'))->response()->getData(true), 'data'),
            ])
        );

    logout();
});
