<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Processo;
use App\Models\Solicitacao;
use App\Rules\ProcessoMovimentavel;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('processo solicitado pode ser movimentado, pois localizado dentro do arquivo', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->solicitada()->create();

    $validator = Validator::make(['numero' => $processo->numero], [
        'numero' => [new ProcessoMovimentavel()],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('processo entregue NÃO pode ser movimentado, pois localizado fora do arquivo', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->entregue()->create();

    $validator = Validator::make(['numero' => $processo->numero], [
        'numero' => [new ProcessoMovimentavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('processo devolvido pode ser movimentado, pois localizado dentro do arquivo', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->devolvida()->create();

    $validator = Validator::make(['numero' => $processo->numero], [
        'numero' => [new ProcessoMovimentavel()],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('processo sem solicitação pode ser movimentado, pois localizado dentro do arquivo', function (string $numero) {
    Processo::factory()->create(['numero' => $numero]);

    $validator = Validator::make(['numero' => $numero], [
        'numero' => [new ProcessoMovimentavel()],
    ]);

    expect($validator->passes())->toBeTrue();
})->with([
    '02393484420224003909',
    '0239348-44.2022.4.00.3909', // máscara é irrelevante
]);

test('mensagem de falha de validação está definida', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->entregue()->create();

    $validator = Validator::make(['numero' => $processo->numero], [
        'numero' => [new ProcessoMovimentavel()],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.movimentacao'));
});
