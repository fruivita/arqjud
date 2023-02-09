<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 */

use App\Http\Controllers\Api\Movimentacao\ProcessoMovimentavelController;
use App\Http\Requests\Api\Movimentacao\ShowProcessoMovimentavelRequest;
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
test('usuário sem autenticação não consegue os dados de um processo movimentável por meio da API JSON', function () {
    $this->postJson(route('api.movimentacao.processo.show'), ['numero' => $this->processo->numero])
        ->assertUnauthorized();
});

test('usuário sem autorização não consegue os dados de um processo movimentável por meio da API JSON', function () {
    Auth::login(Usuario::factory()->create());

    $this->postJson(route('api.movimentacao.processo.show'), ['processo' => $this->processo->numero])
        ->assertForbidden();

    logout();
});

// Falhas
test('informa se o processo não for encontrado por meio da API JSON', function () {
    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::MOVER_PROCESSO_CREATE);

    $this->postJson(route('api.movimentacao.processo.show'), ['numero' => '0000010-85.1990.1.00.5019'])
        ->assertInvalid(['numero']);
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        ProcessoMovimentavelController::class,
        $action,
        $request
    );
})->with([
    ['show', ShowProcessoMovimentavelRequest::class],
]);

test('usuário autenticado consegue os dados de um processo movimentável por meio da API JSON', function () {
    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::MOVER_PROCESSO_CREATE);

    $response = $this->postJson(route('api.movimentacao.processo.show'), ['numero' => $this->processo->numero]);

    $response
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where(
                'processo',
                processoApi($this->processo)
            )
        );
});
