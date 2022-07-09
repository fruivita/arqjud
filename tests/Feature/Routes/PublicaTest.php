<?php

/**
 * @see https://pestphp.com/docs/
 */

use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use function Pest\Laravel\get;

// Caminho feliz
test('rota de login é rota pública, isto é, disponível sem autenticação', function () {
    get(route('login'))->assertOk();
});

test('usuário autenticado, se tentar acessar a página de login, será redirecionado para a rota "home"', function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    get(route('login'))->assertOk();

    login('foo');

    get(route('login'))->assertRedirect(route('home'));

    logout();

    get(route('login'))->assertOk();
});

test('rota de login retorna a view de login', function () {
    get(route('login'))->assertViewIs('login');
});
