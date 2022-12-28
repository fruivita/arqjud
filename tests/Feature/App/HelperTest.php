<?php

/**
 * @see https://pestphp.com/docs/
 */

use Illuminate\Support\Carbon;

// Inválido
test('helper mascara() não aplica a máscara informada se ela for incompatível com a string', function () {
    expect(mascara('123456789', '##.##-'))->toBe('123456789');
});

test('helper cnj() não aplica a máscara se o valor for incompatível', function () {
    expect(cnj('111111'))->toBe('111111');
});

test('helper v1() não aplica a máscara se o valor for incompatível', function () {
    expect(v1('111111'))->toBe('111111');
});

test('helper v2() não aplica a máscara se o valor for incompatível', function () {
    expect(v2('111111'))->toBe('111111');
});

// Caminho feliz
test('helper mascara() aplica a máscara informada na string se ela for compatível', function () {
    expect(mascara('123456789', '##.##-###/##'))->toBe('12.34-567/89');
});

test('helper cnj() aplica a máscara informada na string se ela for compatível', function () {
    expect(cnj('11111111111111111111'))->toBe('1111111-11.1111.1.11.1111');
});

test('helper v1() aplica a máscara informada na string se ela for compatível', function () {
    expect(v1('1111111111'))->toBe('11.1111111-1');
});

test('helper v2() aplica a máscara informada na string se ela for compatível', function () {
    expect(v2('111111111111111'))->toBe('1111.11.11.111111-1');
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

test('helper diaDaSemana() retorna o dia da semana por extenso para o número informado', function ($numero, $esperado) {
    expect(diaDaSemana($numero))->toBe($esperado);
})->with([
    [0, 'domingo'],
    [1, 'segunda-feira'],
    [2, 'terça-feira'],
    [3, 'quarta-feira'],
    [4, 'quinta-feira'],
    [5, 'sexta-feira'],
    [6, 'sábado'],
]);

test('helper mes() retorna o mês por extenso para o número informado', function ($numero, $esperado) {
    expect(mes($numero))->toBe($esperado);
})->with([
    [1, 'janeiro'],
    [2, 'fevereiro'],
    [3, 'março'],
    [4, 'abril'],
    [5, 'maio'],
    [6, 'junho'],
    [7, 'julho'],
    [8, 'agosto'],
    [9, 'setembro'],
    [10, 'outubro'],
    [11, 'novembro'],
    [12, 'dezembro'],
]);

test('helper dataCompleta() retorna a data em formato para documentos - teste de todos os dias da semana', function ($dias, $esperado) {
    $data = Carbon::createFromDate(2022, 1, 1)->addDays($dias);
    expect(dataCompleta($data))->toBe($esperado);
})->with([
    [0, 'Sábado, 1 de janeiro de 2022'],
    [1, 'Domingo, 2 de janeiro de 2022'],
    [2, 'Segunda-feira, 3 de janeiro de 2022'],
    [3, 'Terça-feira, 4 de janeiro de 2022'],
    [4, 'Quarta-feira, 5 de janeiro de 2022'],
    [5, 'Quinta-feira, 6 de janeiro de 2022'],
    [6, 'Sexta-feira, 7 de janeiro de 2022'],
    [7, 'Sábado, 8 de janeiro de 2022'],
]);

test('helper dataCompleta() retorna a data em formato para documentos - teste de todos os meses', function ($meses, $esperado) {
    $data = Carbon::createFromDate(2022, 1, 1)->addMonths($meses);
    expect(dataCompleta($data))->toBe($esperado);
})->with([
    [0, 'Sábado, 1 de janeiro de 2022'],
    [1, 'Terça-feira, 1 de fevereiro de 2022'],
    [2, 'Terça-feira, 1 de março de 2022'],
    [3, 'Sexta-feira, 1 de abril de 2022'],
    [4, 'Domingo, 1 de maio de 2022'],
    [5, 'Quarta-feira, 1 de junho de 2022'],
    [6, 'Sexta-feira, 1 de julho de 2022'],
    [7, 'Segunda-feira, 1 de agosto de 2022'],
    [8, 'Quinta-feira, 1 de setembro de 2022'],
    [9, 'Sábado, 1 de outubro de 2022'],
    [10, 'Terça-feira, 1 de novembro de 2022'],
    [11, 'Quinta-feira, 1 de dezembro de 2022'],
    [12, 'Domingo, 1 de janeiro de 2023'],
]);
