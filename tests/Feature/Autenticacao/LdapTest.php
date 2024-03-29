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
use function Spatie\PestPluginTestTime\testTime;

beforeAll(fn () => \Spatie\Once\Cache::getInstance()->disable());

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
});

// Falha
test('caso a autenticação falhe, usuário autenticado permanece como null', function () {
    expect(usuarioAutenticado())->toBeNull();

    post(route('login'), [
        'matricula' => null,
        'password' => null,
    ])->assertSessionHasErrors();

    expect(usuarioAutenticado())->toBeNull();
});

test('caso a autenticação falhe, usuário compartilhado com as views é null', function () {
    $this
        ->followingRedirects()
        ->post(route('login'), [
            'matricula' => null,
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
    $usuario = login('11111');

    expect($usuario)->toBeInstanceOf(Usuario::class)
        ->and($usuario->matricula)->toBe('11111');

    logout();
});

test('usuário autenticado é compartilhado com as views', function () {
    $matricula = '11111';

    actingAs($matricula);

    $this
        ->followingRedirects()
        ->post(route('login'), [
            'matricula' => $matricula,
            'password' => 'secret',
        ])
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('auth.user.matricula', $matricula)
                ->has('errors', 0)
        );
});

/**
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database/importing/#password-synchronization
 */
test('matrícula é sincronizada no banco de dados', function () {
    expect(Usuario::count())->toBe(0);

    login('11111');

    $usuario = Usuario::first();

    expect(Usuario::count())->toBe(1)
        ->and($usuario->matricula)->toBe('11111')
        ->and(!empty($usuario->password))->toBeTrue();

    logout();
});

test('perfil "Padrão" (perfil padrão para novos usuários) é o atribuído ao usuário ao ser cadastrado na sincronização', function () {
    login();

    $usuario = Usuario::with('perfil')->first();

    expect($usuario->perfil->slug)->toBe(Perfil::PADRAO);

    logout();
});

test('registra o horário da autenticação bem como o ip do cliente', function () {
    testTime()->freeze();
    login();

    $this
        ->assertDatabaseCount('usuarios', 1)
        ->assertDatabaseHas('usuarios', [
            'ip' => request()->ip(),
            'ultimo_login' => now(),
        ]);

    logout();
});

test('usuário ao fazer logout é redirecionado à rota de login', function () {
    login();

    expect(usuarioAutenticado())->toBeInstanceOf(Usuario::class);

    post(route('logout'))->assertRedirect(route('login'));

    expect(usuarioAutenticado())->toBeNull();
});
