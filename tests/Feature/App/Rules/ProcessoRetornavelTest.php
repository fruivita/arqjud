<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Processo;
use App\Models\Solicitacao;
use App\Rules\ProcessoRetornavel;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('processo solicitado não é um processo retornável ao arquivo', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->solicitada()->create();

    $validator = Validator::make(['numero' => apenasNumeros($processo->numero)], [
        'numero' => [new ProcessoRetornavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('processo já devolvido não é um processo retornável ao arquivo', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->devolvida()->create();

    $validator = Validator::make(['numero' => apenasNumeros($processo->numero)], [
        'numero' => [new ProcessoRetornavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('processo sem solicitação não é um processo retornável ao arquivo', function () {
    $processo = Processo::factory()->create();

    $validator = Validator::make(['numero' => apenasNumeros($processo->numero)], [
        'numero' => [new ProcessoRetornavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('processo entregue é retornável ao arquivo', function () {
    $processo = Processo::factory()->create(['numero' => '02393484420224003909']);

    Solicitacao::factory()->for($processo, 'processo')->entregue()->create();

    $validator = Validator::make(['numero' => '02393484420224003909'], [
        'numero' => [new ProcessoRetornavel()],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('mensagem de falha de validação está definida', function () {
    $processo = Processo::factory()->create();

    $validator = Validator::make(['numero' => apenasNumeros($processo->numero)], [
        'numero' => [new ProcessoRetornavel()],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.solicitacao.retornavel'));
});
