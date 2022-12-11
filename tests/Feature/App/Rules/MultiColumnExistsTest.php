<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Processo;
use App\Rules\MultiColumnExists;
use Illuminate\Support\Facades\Validator;

// Falha
test('se as colunas não forem informadas, a validação falhará', function () {
    Processo::factory()->create([
        'numero' => '11111111111111111111',
        'numero_antigo' => '22222222222222222222',
    ]);

    $validator = Validator::make(['termo' => '11111111111111111111'], [
        'termo' => [new MultiColumnExists('processos', [])],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.exists', ['attribute' => 'termo']));
});

// Caminho feliz
test('verifica se determinado valor em múltiplas colunas', function (string $termo, bool $esperado) {
    Processo::factory()->create([
        'numero' => '11111111111111111111',
        'numero_antigo' => '22222222222222222222',
    ]);

    $validator = Validator::make(['termo' => $termo], [
        'termo' => [new MultiColumnExists('processos', ['numero', 'numero_antigo'])],
    ]);

    expect($validator->passes())->toBe($esperado);
})->with([
    ['11111111111111111111', true],
    ['22222222222222222222', true],
    ['33333333333333333333', false],
]);

test('mensagem de falha de validação está definida', function () {
    Processo::factory()->create([
        'numero' => '11111111111111111111',
        'numero_antigo' => '22222222222222222222',
    ]);

    $validator = Validator::make(['termo' => '33333333333333333333'], [
        'termo' => [new MultiColumnExists('processos', ['numero', 'numero_antigo'])],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.exists', ['attribute' => 'termo']));
});
