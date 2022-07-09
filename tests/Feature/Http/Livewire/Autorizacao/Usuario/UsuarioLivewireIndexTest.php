<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Autorizacao\Usuario\UsuarioLivewireIndex;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->usuario = login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('autorizacao.usuario.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('autorizacao.usuario.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(UsuarioLivewireIndex::class)->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::UsuarioUpdate->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->set('exibir_modal_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não exibe o modal de edição sem permissão', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->assertSet('exibir_modal_edicao', false)
    ->call('edit', $this->usuario->id)
    ->assertSet('exibir_modal_edicao', false)
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->set('exibir_modal_edicao', true)
    ->call('update')
    ->assertForbidden();
});

test('perfis indisponíveis se o modal não puder ser carregado', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);

    expect(Perfil::count())->toBeGreaterThan(1);

    Livewire::test(UsuarioLivewireIndex::class)
    ->assertSet('perfis', null)
    ->call('edit', $this->usuario->id)
    ->assertSet('perfis', null);

    expect(Perfil::count())->toBeGreaterThan(1);
});

test('não atualiza um perfil superior', function () {
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    logout();
    login('bar');

    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->call('edit', $this->usuario->id)
    ->set('em_edicao.perfil_id', Perfil::ADMINISTRADOR)
    ->call('update')
    ->assertForbidden();

    $this->usuario->refresh();

    expect($this->usuario->perfil_id)->toBe(Perfil::GERENTE_NEGOCIO);
});

// Rules
test('perfil_id deve existir previamente no banco de dados', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->call('edit', $this->usuario->id)
    ->set('em_edicao.perfil_id', 2)
    ->call('update')
    ->assertHasErrors(['em_edicao.perfil_id' => 'exists']);
});

test('perfil_id é obrigatório', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->call('edit', $this->usuario->id)
    ->set('em_edicao.perfil_id', '')
    ->call('update')
    ->assertHasErrors(['em_edicao.perfil_id' => 'required']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);

    Usuario::factory(30)->create();

    Livewire::test(UsuarioLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('usuarios', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('autorizacao.usuario.index'))
    ->assertOk()
    ->assertSeeLivewire(UsuarioLivewireIndex::class);
})->with([
    Permissao::UsuarioViewAny->value,
    Permissao::UsuarioUpdate->value,
]);

test('exibe o modal de edição com permissão', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->assertSet('exibir_modal_edicao', false)
    ->call('edit', $this->usuario->id)
    ->assertOk()
    ->assertSet('exibir_modal_edicao', true);
});

test('somente perfis iguais ou inferiores estão disponíveis', function () {
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->assertSet('perfis', null)
    ->call('edit', $this->usuario->id)
    ->assertCount('perfis', 3);

    expect(Perfil::count())->toBe(4);
});

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->call('edit', $this->usuario->id)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('atualiza um registro com permissão', function () {
    logout();
    $usuario_bar = login('bar');

    $usuario_bar->perfil_id = Perfil::GERENTE_NEGOCIO;
    $usuario_bar->save();

    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    $this->usuario->refresh();

    Livewire::test(UsuarioLivewireIndex::class)
    ->call('edit', $this->usuario)
    ->assertSet('em_edicao.perfil_id', Perfil::PADRAO)
    ->set('em_edicao.perfil_id', Perfil::OBSERVADOR)
    ->call('update')
    ->assertOk();

    $this->usuario->refresh();

    expect($this->usuario->perfil->id)->toBe(Perfil::OBSERVADOR);
});

test('atualização do perfil remove as delegações feitas pelo usuário', function () {
    $lotacao = Lotacao::factory()->create();
    logout();

    $usuario_bar = login('bar');

    $usuario_bar->perfil_id = Perfil::ADMINISTRADOR;
    $usuario_bar->lotacao_id = $lotacao->id;
    $usuario_bar->save();

    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    $this->usuario->perfil_id = Perfil::ADMINISTRADOR;
    $this->usuario->lotacao_id = $lotacao->id;
    $this->usuario->perfil_concedido_por = $usuario_bar->id;
    $this->usuario->antigo_perfil_id = Perfil::OBSERVADOR;
    $this->usuario->save();

    Livewire::test(UsuarioLivewireIndex::class)
    ->call('edit', $this->usuario)
    ->assertSet('em_edicao.perfil_id', Perfil::ADMINISTRADOR)
    ->assertSet('em_edicao.perfil_concedido_por', $usuario_bar->id)
    ->assertSet('em_edicao.antigo_perfil_id', Perfil::OBSERVADOR)
    ->set('em_edicao.perfil_id', Perfil::GERENTE_NEGOCIO)
    ->call('update')
    ->assertOk();

    $this->usuario->refresh();

    expect($this->usuario->perfil_id)->toBe(Perfil::GERENTE_NEGOCIO)
    ->and($this->usuario->perfil_concedido_por)->toBeNull()
    ->and($this->usuario->antigo_perfil_id)->toBeNull();
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);

    Usuario::factory()->create(['nome' => 'nomefoo', 'username' => 'userbar']);
    Usuario::factory()->create(['nome' => 'nomebaz', 'username' => 'userloren']);
    Usuario::factory()->create(['nome' => 'nomeloren', 'username' => 'userdolor']);

    Livewire::test(UsuarioLivewireIndex::class)
    ->set('termo', 'mefoo')
    ->assertCount('usuarios', 1)
    ->set('termo', 'lore')
    ->assertCount('usuarios', 2)
    ->set('termo', '')
    ->assertCount('usuarios', Usuario::count());
});

test('atualiza perfil de usuário com o mesmo nível', function () {
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    logout();
    $usuario_bar = login('bar');

    $usuario_bar->perfil_id = Perfil::GERENTE_NEGOCIO;
    $usuario_bar->save();

    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->call('edit', $this->usuario->id)
    ->set('em_edicao.perfil_id', Perfil::OBSERVADOR)
    ->call('update')
    ->assertOk();

    $this->usuario->refresh();

    expect($this->usuario->perfil_id)->toBe(Perfil::OBSERVADOR);
});

test('atualiza perfil de usuário com nível inferior', function () {
    $this->usuario->perfil_id = Perfil::OBSERVADOR;
    $this->usuario->save();

    logout();
    $usuaro_bar = login('bar');

    $usuaro_bar->perfil_id = Perfil::GERENTE_NEGOCIO;
    $usuaro_bar->save();

    concederPermissao(Permissao::UsuarioViewAny->value);
    concederPermissao(Permissao::UsuarioUpdate->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->call('edit', $this->usuario->id)
    ->set('em_edicao.perfil_id', Perfil::PADRAO)
    ->call('update')
    ->assertOk();

    $this->usuario->refresh();

    expect($this->usuario->perfil_id)->toBe(Perfil::PADRAO);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);

    Livewire::test(UsuarioLivewireIndex::class)
    ->assertSet('exibir_modal_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'nome',
            'usuario',
            'perfil',
            'delegante',
            'acoes',
        ],

        'por_pagina' => 10,
    ]);
});

test('UsuarioLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(UsuarioLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPesquisa::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
