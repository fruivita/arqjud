<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 */

use App\Http\Controllers\Api\Caixa\CaixaController;
use App\Http\Requests\Api\Caixa\ShowCaixaRequest;
use App\Models\Caixa;
use Database\Seeders\PerfilSeeder;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->caixa = Caixa::factory()->hasVolumes(3)->create();
});

// Autorização
test('usuário sem autenticação não consegue os dados da uma caixa por meio da API JSON', function () {
    $this->postJson(route('api.caixa.show'), [
        'localidade_criadora_id' => $this->caixa->localidade_criadora_id,
        'ano' => $this->caixa->ano,
        'guarda_permanente' => $this->caixa->guarda_permanente,
        'complemento' => $this->caixa->complemento,
        'numero' => $this->caixa->numero,
    ])->assertUnauthorized();
});

// Falhas
test('informa se a caixa não for encontrada por meio da API JSON', function (mixed $complemento) {
    login();

    $this->postJson(route('api.caixa.show'), [
        'localidade_criadora_id' => $this->caixa->localidade_criadora_id,
        'ano' => $this->caixa->ano,
        'guarda_permanente' => $this->caixa->guarda_permanente,
        'complemento' => $complemento,
        'numero' => $this->caixa->numero,
    ])->assertInvalid(['numero']);
})->with(['foo', null]);

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        CaixaController::class,
        $action,
        $request
    );
})->with([
    ['show', ShowCaixaRequest::class],
]);

test('usuário autenticado consegue os dados de um processo por meio da API JSON', function () {
    login();

    $response = $this->postJson(route('api.caixa.show'), [
        'localidade_criadora_id' => $this->caixa->localidade_criadora_id,
        'ano' => $this->caixa->ano,
        'guarda_permanente' => $this->caixa->guarda_permanente,
        'complemento' => $this->caixa->complemento,
        'numero' => $this->caixa->numero,
    ]);

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where(
                'caixa',
                caixaApi($this->caixa) + ['volumes' => volumesApi($this->caixa->volumes)]
            ));
});

test('complemento da caixa é campo opcional, mas é tratado adquadamente pela API', function ($complemento) {
    login();

    $caixa = Caixa::factory()->create(['complemento' => $complemento]);

    $response = $this->postJson(route('api.caixa.show'), [
        'localidade_criadora_id' => $caixa->localidade_criadora_id,
        'ano' => $caixa->ano,
        'guarda_permanente' => $caixa->guarda_permanente,
        'complemento' => $complemento,
        'numero' => $caixa->numero,
    ]);

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where(
                'caixa',
                caixaApi($caixa) + ['volumes' => volumesApi($caixa->volumes)]
            ));
})->with([
    null,
    'foo',
]);
