<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://ldaprecord.com/docs/laravel/v2/testing/
 * @see https://ldaprecord.com/docs/laravel/v2/auth/testing/
 */

use App\Models\Perfil;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
});

// Falha
test('caso a autenticação falhe, usuário autenticado permanece como null', function () {
    expect(usuarioAutenticado())->toBeNull();

    post(route('login'), [
        'username' => null,
        'password' => null,
    ])->assertSessionHasErrors();

    expect(usuarioAutenticado())->toBeNull();
});

test('caso a autenticação falhe, usuário compartilhado com as views é null', function () {
    $this
        ->followingRedirects()
        ->post(route('login'), [
            'username' => null,
            'password' => null,
        ])
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Autenticacao/Login')
                ->has('errors', 2)
                ->has('auth', null)
        );
});

// // Caminho feliz
test('view de autenticação é renderizada ao visitar a rota login', function () {
    get(route('login'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Autenticacao/Login')
                ->has('errors', 0)
        );
});

test('usuário autenticado, se tentar acessar a página de login, será redirecionado para a rota "home"', function () {
    get(route('login'))->assertOk();

    login();

    get(route('login'))->assertRedirect(route('home.show'));

    logout();

    get(route('login'))->assertOk();
});

test('autenticação cria o objeto da classe Usuario', function () {
    $usuario = login();

    expect($usuario)->toBeInstanceOf(Usuario::class)
        ->and($usuario->username)->toBe('foo');

    logout();
});

test('usuário autenticado é compartilhado com as views', function () {
    $samaccountname = 'foo';

    actingAs($samaccountname);

    $this
        ->followingRedirects()
        ->post(route('login'), [
            'username' => $samaccountname,
            'password' => 'secret',
        ])
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('auth.user.username', $samaccountname)
                ->has('errors', 0)
        );
});

/**
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database/importing/#password-synchronization
 */
test('username e nome são sincronizados no banco de dados', function () {
    expect(Usuario::count())->toBe(0);

    login();

    $usuario = Usuario::first();

    expect(Usuario::count())->toBe(1)
        ->and($usuario->username)->toBe('foo')
        ->and($usuario->nome)->toBe('foo')
        ->and(!empty($usuario->password))->toBeTrue();

    logout();
});

test('perfil "Padrão" (perfil padrão para novos usuários) é o atribuído ao usuário ao ser cadastrado na sincronização', function () {
    login();

    $usuario = Usuario::with('perfil')->first();

    expect($usuario->perfil->slug)->toBe(Perfil::PADRAO);

    logout();
});

test('usuário ao fazer logout é redirecionado à rota de login', function () {
    login();

    expect(usuarioAutenticado())->toBeInstanceOf(Usuario::class);

    post(route('logout'))->assertRedirect(route('login'));

    expect(usuarioAutenticado())->toBeNull();
});
