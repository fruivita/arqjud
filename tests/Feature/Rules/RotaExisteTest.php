<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\RotaExiste;

test('valida se a rota existe na aplicação, isto é, se é uma rota válida', function ($valor, $esperado) {
    $rule = new RotaExiste();

    expect($rule->passes('app_route_name', $valor))->toBe($esperado);
})->with([
    ['foo.bar', false], // rota inexistente
    ['administracao.log.index', true],
]);
