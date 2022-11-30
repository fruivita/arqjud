<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;

// Caminho feliz
test('dados básicos compartilhados independente de autenticação estão definidos', function () {
    get(route('login'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->where('auth', null)
        );
});

test('dados básicos compartilhados com autenticação estão definidos', function () {
    $this->seed([PerfilSeeder::class]);

    login('foo');

    // mensagens de sucesso e de erro
    session()->put('sucesso', 'foo bar');
    session()->put('erro', 'bar baz');

    get(route('home.show'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('auth.user.username', 'foo')
                ->has('auth.menu', 0)
                ->where('flash.sucesso', 'foo bar')
                ->where('flash.erro', 'bar baz')
        );
});
