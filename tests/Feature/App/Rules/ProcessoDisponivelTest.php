<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Processo;
use App\Models\Solicitacao;
use App\Rules\ProcessoDisponivel;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('processo já solicitado está indisponível para nova solicitação', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->solicitada()->create();

    $validator = Validator::make(['numero' => $processo->numero], [
        'numero' => [new ProcessoDisponivel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('processo já entregue está indisponível para nova solicitação', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->entregue()->create();

    $validator = Validator::make(['numero' => $processo->numero], [
        'numero' => [new ProcessoDisponivel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('processo já devolvido está disponível para nova solicitação', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->devolvida()->create();

    $validator = Validator::make(['numero' => apenasNumeros($processo->numero)], [
        'numero' => [new ProcessoDisponivel()],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('processo sem solicitação está disponível para solicitação', function () {
    Processo::factory()->create(['numero' => '02393484420224003909']);

    $validator = Validator::make(['numero' => '02393484420224003909'], [
        'numero' => [new ProcessoDisponivel()],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('mensagem de falha de validação está definida', function () {
    $processo = Processo::factory()->create();
    Solicitacao::factory()->for($processo, 'processo')->solicitada()->create();

    $validator = Validator::make(['numero' => $processo->numero], [
        'numero' => [new ProcessoDisponivel()],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.solicitacao.indisponivel'));
});
