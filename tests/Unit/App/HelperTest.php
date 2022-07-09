<?php

/**
 * @see https://pestphp.com/docs/
 */

// Inválido
test('stringParaArrayAssoc retorna nulo se os valores informados forem inválidos', function ($chaves, $delimitador, $string) {
    expect(stringParaArrayAssoc($chaves, $delimitador, $string))->toBeNull();
})->with([
    [
        ['nome', 'idade', 'nacionalidade', 'chave_excesso'], // qtd de chaves incompatível com a string
        ',',
        'foo,18,bar',
    ],
    [
        [], // chaves não informadas
        ',',
        'foo,18,bar',
    ],
    [
        ['nome', 'idade', 'nacionalidade'],
        ',',
        '', // string não informada (falso boleano)
    ],
    [
        ['nome', 'idade', 'nacionalidade'],
        '', // delimitador não informado (falso boleano)
        'foo,18,bar',
    ],
]);

// Caminho feliz
test('maxIntegerSeguro retorna o maior integer seguro, isto é, não sujeito à truncagem, para trabalho em javascript', function () {
    expect(maxIntegerSeguro())->toBe(9007199254740991);
});

test('stringParaArrayAssoc quebra a string baseada no delimitador e retorna um array associativo', function () {
    $chaves = ['nome', 'idade', 'nacionalidade'];
    $string = 'foo,18,bar';
    $delimitador = ',';
    $esperado = [
        'nome' => 'foo',
        'idade' => '18',
        'nacionalidade' => 'bar',
    ];

    expect(stringParaArrayAssoc($chaves, $delimitador, $string))->toMatchArray($esperado);
});
