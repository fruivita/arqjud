<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Http\Livewire\Teste\Simulacao\SimulacaoLivewireCreate;
use App\Models\Usuario;
use App\Rules\NaoUsuarioAutenticado;
use App\Rules\UsuarioLdap;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('teste.simulacao.create'))->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('teste.simulacao.create'))->assertForbidden();
});

test('não desfaz a simulação sem estar autenticado', function () {
    logout();

    delete(route('teste.simulacao.destroy'))->assertRedirect(route('login'));
});

test('rota de desfazimento só está disponível se a simulação existir', function () {
    concederPermissao(Permissao::SimulacaoCreate->value);

    delete(route('teste.simulacao.destroy'))->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(SimulacaoLivewireCreate::class)
    ->assertForbidden();
});

test('não renderiza o componente com outra simulação em andamento', function () {
    logout();
    login('bar');
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertOk();

    get(route('teste.simulacao.create'))->assertForbidden();
});

test('não inicia simulação com outra em andamento', function () {
    logout();
    login('bar');
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertOk()
    ->set('username', 'baz')
    ->call('store')
    ->assertForbidden();
});

test('não desfaz a simulação se ela não existe', function () {
    logout();
    login('bar');
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->call('destroy')
    ->assertForbidden();
});

// Rules
test('username é obrigatório', function () {
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', '')
    ->call('store')
    ->assertHasErrors(['username' => 'required']);
});

test('username precisa ser uma string', function () {
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', ['bar'])
    ->call('store')
    ->assertHasErrors(['username' => 'string']);
});

test('username precisa ter no máximo 20 caracteres', function () {
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', Str::random(21))
    ->call('store')
    ->assertHasErrors(['username' => 'max']);
});

test('username precisa ser diferente do pertencente ao usuário autenticado, pois não se pode simular o próprio usuário', function () {
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertHasErrors(['username' => NaoUsuarioAutenticado::class]);
});

test('username precisa existir no servidor LDAP', function () {
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', 'bar')
    ->call('store')
    ->assertHasErrors(['username' => UsuarioLdap::class]);
});

// Caminho feliz
test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::SimulacaoCreate->value);

    get(route('teste.simulacao.create'))
    ->assertOk()
    ->assertSeeLivewire(SimulacaoLivewireCreate::class);
});

test('cria as variáveis de sessão ao criar a simulação e redireciona para a página home', function () {
    logout();
    login('bar');
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertRedirect(route('home'));

    expect(session()->get('simulado'))->toBeInstanceOf(Usuario::class)
    ->and(session()->get('simulado')->username)->toBe('foo')
    ->and(session()->get('simulador'))->toBeInstanceOf(Usuario::class)
    ->and(session()->get('simulador')->username)->toBe('bar');
});

test('tarja de simulação é exibida apenas durante a simualação', function () {
    logout();
    login('bar');
    concederPermissao(Permissao::SimulacaoCreate->value);

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', 'foo')
    ->assertDontSee(__('Simulação ativada por :attribute', ['attribute' => 'bar']))
    ->call('store');

    get(route('home'))
    ->assertSee(__('Simulação ativada por :attribute', ['attribute' => 'bar']));

    delete(route('teste.simulacao.destroy'));

    get(route('home'))
    ->assertDontSee(__('Simulação ativada por :attribute', ['attribute' => 'bar']));
});

test('simulação importa o usuário simulado do servidor LDAP para o banco de dados', function () {
    logout();
    // usuário 'foo' já existe no fake LDAP e também no banco de dados.
    // Portanto, vamos exclui-lo para testar a importação.
    Usuario::where('username', 'foo')->delete();

    login('bar');
    concederPermissao(Permissao::SimulacaoCreate->value);

    expect(Usuario::where('username', 'foo')->exists())->toBeFalse();

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertOk();

    expect(Usuario::where('username', 'foo')->exists())->toBeTrue();
});

test('simulação altera o usuário autenticado e, ao terminar, volta ao usuário anterior', function () {
    logout();
    login('bar');
    concederPermissao(Permissao::SimulacaoCreate->value);

    expect(auth()->user()->username)->toBe('bar');

    Livewire::test(SimulacaoLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store');

    // força a navegação para efetivar a mudança do usuário.
    get(route('home'));

    expect(auth()->user()->username)->toBe('foo');

    delete(route('teste.simulacao.destroy'));

    expect(auth()->user()->username)->toBe('bar');
});
