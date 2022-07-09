<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\UsuarioLdap;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;

// Caminho feliz
test('valida se o usuário existe no servidor LDAP', function ($valor, $esperado) {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    login('foo'); // garante a existência do usuário de samaccountname 'foo'
    $rule = new UsuarioLdap();

    expect($rule->passes('username', $valor))->toBe($esperado);
})->with([
    ['foo', true],  // válido. usuário existente
    ['bar', false], // usuário inexistente
]);
