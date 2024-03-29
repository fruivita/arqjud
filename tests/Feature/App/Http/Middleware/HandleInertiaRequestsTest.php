<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Models\Perfil;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
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

    $perfil = Perfil::first();

    Auth::login(Usuario::factory()->for($perfil, 'perfil')->create(['matricula' => 'es11111']));

    get(route('home.show'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('auth.user.matricula', 'es11111')
                ->where('auth.user.perfil', $perfil->nome)
                ->where('auth.home', route('home.show'))
                ->where('auth.logout', route('logout'))
                ->has('auth.menu', 0)
                ->has('flash', null)
        );
});

test('compartilha mensagem de sucesso', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    session()->put('feedback', ['sucesso' => 'foo bar']);

    get(route('home.show'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->where('flash.sucesso', 'foo bar')
        );
});

test('compartilha mensagem de erro', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    session()->put('feedback', ['erro' => 'bar baz']);

    get(route('home.show'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->where('flash.erro', 'bar baz')
        );
});
