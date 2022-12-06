<?php

/**
 * @see https://pestphp.com/docs/
 */

// Inválido
test('helper mascara() não aplica a máscara informada se ela for compatível com a string', function () {
    expect(mascara('123456789', '##.##-'))->toBe('123456789');
});

// Caminho feliz
test('helper mascara() aplica a máscara informada na string se ela for compatível', function () {
    expect(mascara('123456789', '##.##-###/##'))->toBe('12.34-567/89');
});

test('helper ascOrDesc() retorna a ordenação a que deve ser utilizada sendo desc a ordenação padrão', function () {
    expect(ascOrDesc())->toBe('desc')
        ->and(ascOrDesc('foo'))->toBe('desc')
        ->and(ascOrDesc('desc'))->toBe('desc')
        ->and(ascOrDesc('asc'))->toBe('asc')
        ->and(ascOrDesc('AsC'))->toBe('asc');
});

test('helper apenasNumeros() retorna apenas a parte numérica de uma string ou nulo se nada sobrar', function (mixed $string, mixed $esperado) {
    expect(apenasNumeros($string))->toBe($esperado);
})->with([
    ['123ABC456', '123456'],
    ['123456', '123456'],
    ['12.3A4-56', '123456'],
    ['aa-aa', null],
    [null, null],
]);
