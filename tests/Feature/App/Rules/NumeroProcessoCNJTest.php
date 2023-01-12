<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\NumeroProcessoCNJ;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('verifica se o número de processo é válido, isto é, se respeita o padrão definido pelo CNJ sem máscara', function (string $numero, bool $esperado) {
    $validator = Validator::make(['numero' => $numero], [
        'numero' => [new NumeroProcessoCNJ()],
    ]);

    expect($validator->passes())->toBe($esperado);
})->with([
    ['02393484420224003909', true],
    ['12393484420224003909', false], // sequencial alterado
    ['02393484320224003909', false], // digito verificador alterado
    ['02393484420214003909', false], // ano alterado
    ['02393484420225003909', false], // órgão alterado
    ['02393484420224013909', false], // tribunal alterado
    ['02393484420224004909', false], // unidade de origem alterado
]);

test('mensagem de falha de validação está definida', function () {
    $validator = Validator::make(['numero' => '33333333333333333333'], [
        'numero' => [new NumeroProcessoCNJ()],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.cnj'));
});
