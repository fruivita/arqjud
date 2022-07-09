<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\NaoUsuarioAutenticado;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;

// Rules
test('sem usuário autenticado, a validação retorna false', function () {
    $rule = new NaoUsuarioAutenticado();

    expect($rule->passes('username', 'bar'))->toBeFalse();
});

// Caminho feliz
test('valida se o usuário informado NÃO É o usuário autenticado', function ($valor, $esperado) {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);
    login('foo');
    $rule = new NaoUsuarioAutenticado();

    expect($rule->passes('username', $valor))->toBe($esperado);

    logout();
})->with([
    ['foo', false], // inválido, pois é o próprio usuário autenticado
    ['bar', true],  // válido, pois não é o usuário autenticado
]);
