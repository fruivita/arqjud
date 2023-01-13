<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\NumeroProcesso;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('verifica se o número de processo é válido, isto é, se respeita o padrão definido pelo CNJ, o de 15 e o de 10 dígitos, todos sem máscara', function (string $numero, bool $esperado) {
    $validator = Validator::make(['numero' => $numero], [
        'numero' => [new NumeroProcesso()],
    ]);

    expect($validator->passes())->toBe($esperado);
})->with([
    // CNJ
    ['02393484420224003909', true],
    ['12393484420224003909', false], // sequencial alterado
    ['02393484320224003909', false], // digito verificador alterado
    ['02393484420214003909', false], // ano alterado
    ['02393484420225003909', false], // órgão alterado
    ['02393484420224013909', false], // tribunal alterado
    ['02393484420224004909', false], // unidade de origem alterado
    // 15 dígitos
    ['190044800819978', true],  // sem máscara
    ['190044800819988', false], // sequencial alterado
    ['190044800819979', false], // digito verificador alterado
    ['190144800819978', false], // ano alterado
    ['190045800819978', false], // seção alterada
    ['190044810819978', false], // localidade de origem alterada
    // 10 dígitos
    ['9398798069', true],
    ['9398798079', false], // sequencial alterado
    ['9398798068', false], // digito verificador alterado
    ['9498798069', false], // ano alterado
]);

test('mensagem de falha de validação está definida', function (string $numero, bool $esperado) {
    $validator = Validator::make(['numero' => $numero], [
        'numero' => [new NumeroProcesso()],
    ]);

    expect($validator->passes())->toBe($esperado)
        ->and($validator->errors()->first())->toBe(__('validation.processo', ['attribute' => 'numero']));
})->with([
    ['11111111112222222222', false],
    ['111111122222222', false],
    ['1111122222', false],
]);
