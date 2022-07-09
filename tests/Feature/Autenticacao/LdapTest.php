<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://ldaprecord.com/docs/laravel/v2/testing/
 * @see https://ldaprecord.com/docs/laravel/v2/auth/testing/
 */

use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

// Autorização
test('rotas privadas não são exibidas para usuários não autenticados', function () {
    get(route('login'))
    ->assertDontSee([
        route('logout'),
        route('home'),
    ]);
});

// Rules
test('username é obrigatório para autenticação', function () {
    post(route('login'), [
        'username' => null,
        'password' => 'secret',
    ])->assertSessionHasErrors([
        'username' => __('validation.required', ['attribute' => 'username']),
    ]);

    expect(usuarioAutenticado())->toBeNull();
});

test('password é obrigatório para autenticação', function () {
    post(route('login'), [
        'username' => 'foo',
        'password' => null,
    ])->assertSessionHasErrors([
        'password' => __('validation.required', ['attribute' => 'password']),
    ]);

    expect(usuarioAutenticado())->toBeNull();
});

// Caminho feliz
test('autenticação cria o objeto da classe Usuario', function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $samaccountname = 'foo';
    $usuario = login($samaccountname);

    expect($usuario)->toBeInstanceOf(Usuario::class)
    ->and($usuario->username)->toBe($samaccountname);

    logout();
});

test('username, password e nome são sincronizados no banco de dados', function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    expect(Usuario::count())->toBe(0);

    $samaccountname = 'foo';
    login($samaccountname);

    $usuario = Usuario::first();

    expect(Usuario::count())->toBe(1)
    ->and($usuario->username)->toBe($samaccountname)
    ->and($usuario->nome)->toBe($samaccountname . ' bar baz')
    ->and(! empty($usuario->password))->toBeTrue();

    logout();
});

test('perfil "Padrão" (perfil padrão para novos usuários) é o atribuído ao usuário quando é cadastrado na sincronização', function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    login('foo');

    $usuario = Usuario::first();

    expect($usuario->perfil->id)->toBe(Perfil::PADRAO);

    logout();
});

test('se não for informada a lotação, o usuário será lotado na lotação padrão (SEM_LOTACAO)', function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    login('foo');

    $usuario = Usuario::first();

    expect($usuario->lotacao->id)->toBe(Lotacao::SEM_LOTACAO);

    logout();
});

test('usuário ao fazer logout é redirecionado à rota de login', function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    login('foo');

    expect(usuarioAutenticado())->toBeInstanceOf(Usuario::class);

    post(route('logout'))->assertRedirect(route('login'));

    expect(usuarioAutenticado())->toBeNull();
});

/*
 * Testa a integração com o servidor LDAP.
 *
 * Efetivamente verifica se a autenticação está funcionando.
 *
 * Para o teste, informe no arquivo .env o username e password com permissões
 * de autenticação (e não apenas de leitura) no domínio. Após o teste, limpe os
 * dados.
 */
test('teste real de autenticação no servidor LDAP (login e logout)', function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $username = config('testing.username');

    post(route('login'), [
        'username' => $username,
        'password' => config('testing.password'),
    ]);

    $usuario = usuarioAutenticado();

    expect($usuario)->toBeInstanceOf(Usuario::class)
    ->and($usuario->username)->toBe($username);

    logout();

    expect(usuarioAutenticado())->toBeNull();
})->group('integration');
