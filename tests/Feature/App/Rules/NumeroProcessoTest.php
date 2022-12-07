<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\NumeroProcesso;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('verifica se o número de processo é válido, isto é, se respeita o padrão definido pelo CNJ, o de 15 e o de 10 dígitos', function ($numero, $esperado) {
    $validator = Validator::make(['numero' => $numero], [
        'numero' => [new NumeroProcesso()],
    ]);

    expect($validator->passes())->toBe($esperado);
})->with([
    // CNJ
    ['0239348-44.2022.4.00.3909', true],
    ['0239348-44.2022.400.3909', true],  // outra máscara
    ['02393484420224003909', true],      // sem máscara
    ['1239348-44.2022.400.3909', false], // sequencial alterado
    ['0239348-43.2022.400.3909', false], // digito verificador alterado
    ['0239348-44.2021.400.3909', false], // ano alterado
    ['0239348-44.2022.500.3909', false], // órgão alterado
    ['0239348-44.2022.401.3909', false], // tribunal alterado
    ['0239348-44.2022.400.4909', false], // unidade de origem alterado
    // 15 dígitos
    ['1900.44.80.081997-8', true],
    ['1900-44-80-081997.8', true],  // outra máscara
    ['190044800819978', true],      // sem máscara
    ['1900.44.80.081998-8', false], // sequencial alterado
    ['1900.44.80.081997-9', false], // digito verificador alterado
    ['1901.44.80.081997-8', false], // ano alterado
    ['1900.45.80.081997-8', false], // seção alterada
    ['1900.44.81.081997-8', false], // localidade de origem alterada
    // 10 dígitos
    ['93.9879806-9', true],
    ['93-9879806.9', true],  // outra máscara
    ['9398798069', true],    // sem máscara
    ['93.9879807-9', false], // sequencial alterado
    ['93.9879806-8', false], // digito verificador alterado
    ['94.9879806-9', false], // ano alterado
]);
