<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\NumeroProcessoCNJ;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('verifica se o número de processo é válido, isto é, se respeita o padrão definido pelo CNJ', function (string $numero, bool $esperado) {
    $validator = Validator::make(['numero' => $numero], [
        'numero' => [new NumeroProcessoCNJ()],
    ]);

    expect($validator->passes())->toBe($esperado);
})->with([
    ['0239348-44.2022.4.00.3909', true],
    ['0239348-44.2022.400.3909', true],  // outra máscara
    ['02393484420224003909', true],      // sem máscara
    ['1239348-44.2022.400.3909', false], // sequencial alterado
    ['0239348-43.2022.400.3909', false], // digito verificador alterado
    ['0239348-44.2021.400.3909', false], // ano alterado
    ['0239348-44.2022.500.3909', false], // órgão alterado
    ['0239348-44.2022.401.3909', false], // tribunal alterado
    ['0239348-44.2022.400.4909', false], // unidade de origem alterado
]);

test('mensagem de falha de validação está definida', function () {
    $validator = Validator::make(['numero' => '33333333333333333333'], [
        'numero' => [new NumeroProcessoCNJ()],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.cnj'));
});
