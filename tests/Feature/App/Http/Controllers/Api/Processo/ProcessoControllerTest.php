<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 */

use App\Http\Controllers\Api\Processo\ProcessoController;
use App\Http\Requests\Api\Processo\ShowProcessoRequest;
use App\Models\Processo;
use Database\Seeders\PerfilSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->processo = Processo::factory()->create();
});

// Autorização
test('usuário sem autenticação não consegue os dados de um processo por meio da API JSON', function () {
    $this->postJson(route('api.processo.show'), ['numero' => $this->processo->numero])
        ->assertUnauthorized();
});

// Falhas
test('informa se o processo não for encontrado por meio da API JSON', function () {
    login();

    $this->postJson(route('api.processo.show'), ['numero' => '0000010-85.1990.1.00.5019'])
        ->assertInvalid(['numero']);
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        ProcessoController::class,
        $action,
        $request
    );
})->with([
    ['show', ShowProcessoRequest::class],
]);

test('usuário autenticado consegue os dados de um processo por meio da API JSON', function () {
    login();

    $response = $this->postJson(route('api.processo.show'), ['numero' => $this->processo->numero]);

    $response
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where(
                'processo',
                processoApi($this->processo)
            )
        );
});
