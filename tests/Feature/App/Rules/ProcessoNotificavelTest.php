<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Processo;
use App\Models\Solicitacao;
use App\Rules\ProcessoNotificavel;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('processo entregue não é um processo notificável', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->entregue()->create();

    $validator = Validator::make(['numero' => apenasNumeros($processo->numero)], [
        'numero' => [new ProcessoNotificavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('processo já devolvido não é um processo notificável', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->devolvida()->create();

    $validator = Validator::make(['numero' => apenasNumeros($processo->numero)], [
        'numero' => [new ProcessoNotificavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('processo sem solicitação não é um processo notificável', function () {
    $processo = Processo::factory()->create();

    $validator = Validator::make(['numero' => apenasNumeros($processo->numero)], [
        'numero' => [new ProcessoNotificavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('processo solicitado é notificável', function () {
    $processo = Processo::factory()->create(['numero' => '02393484420224003909']);

    Solicitacao::factory()->for($processo, 'processo')->solicitada()->create();

    $validator = Validator::make(['numero' => '02393484420224003909'], [
        'numero' => [new ProcessoNotificavel()],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('mensagem de falha de validação está definida', function () {
    $processo = Processo::factory()->create();

    $validator = Validator::make(['numero' => apenasNumeros($processo->numero)], [
        'numero' => [new ProcessoNotificavel()],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.solicitacao.notificavel', ['attribute' => 'numero']));
});
