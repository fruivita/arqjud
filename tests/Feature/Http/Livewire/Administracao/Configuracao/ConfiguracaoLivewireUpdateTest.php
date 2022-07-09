<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Administracao\Configuracao\ConfiguracaoLivewireUpdate;
use App\Models\Configuracao;
use App\Rules\UsuarioLdap;
use Database\Seeders\ConfiguracaoSeeder;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([ConfiguracaoSeeder::class, LotacaoSeeder::class, PerfilSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('administracao.configuracao.edit'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('administracao.configuracao.edit'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::ConfiguracaoView->value);

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('superadmin é obrigatório', function () {
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->set('modo_edicao', true)
    ->set('configuracao.superadmin', '')
    ->call('update')
    ->assertHasErrors(['configuracao.superadmin' => 'required']);
});

test('superadmin precisa ser uma string', function () {
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->set('modo_edicao', true)
    ->set('configuracao.superadmin', ['bar'])
    ->call('update')
    ->assertHasErrors(['configuracao.superadmin' => 'string']);
});

test('superadmin precisa ter no máximo 20 caracteres', function () {
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->set('modo_edicao', true)
    ->set('configuracao.superadmin', Str::random(21))
    ->call('update')
    ->assertHasErrors(['configuracao.superadmin' => 'max']);
});

test('superadmin precisa existir no servidor LDAP', function () {
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->set('modo_edicao', true)
    ->set('configuracao.superadmin', 'bar')
    ->call('update')
    ->assertHasErrors(['configuracao.superadmin' => UsuarioLdap::class]);
});

// Caminho feliz
test('renderiza o componente com permissão view ou update', function ($permissao) {
    concederPermissao($permissao);

    get(route('administracao.configuracao.edit'))
    ->assertOk()
    ->assertSeeLivewire(ConfiguracaoLivewireUpdate::class);
})->with([
    Permissao::ConfiguracaoView->value,
    Permissao::ConfiguracaoUpdate->value,
]);

test('configuração carregada para atuação é a esperada, visto que ela é única e predefinida', function () {
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->assertSet('configuracao.id', Configuracao::ID);
});

test('emite evento de feedback ao atualizar um registro', function () {
    logout();
    login('dumb user');
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->set('modo_edicao', true)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->assertSet('modo_edicao', false);
});

test('atualiza um registro com permissão', function () {
    logout();
    login('bar');
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    expect(Configuracao::find(Configuracao::ID)->superadmin)->toBe('dumb user');

    Livewire::test(ConfiguracaoLivewireUpdate::class)
    ->set('modo_edicao', true)
    ->set('configuracao.superadmin', 'bar')
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    expect(Configuracao::find(Configuracao::ID)->superadmin)->toBe('bar');
});

test('ConfiguracaoLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(ConfiguracaoLivewireUpdate::class))
        ->has([
            \App\Traits\ComUsuarioLdapImportavel::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
        ])
    )->toBeTrue();
});
