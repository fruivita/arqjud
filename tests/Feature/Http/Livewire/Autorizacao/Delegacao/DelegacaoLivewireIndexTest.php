<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Http\Livewire\Autorizacao\Delegacao\DelegacaoLivewireIndex;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->lotacao = Lotacao::factory()->create();

    $this->usuario = login('foo');

    $this->usuario->lotacao_id = $this->lotacao->id;
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('autorizacao.delegacao.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('autorizacao.permissao.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(DelegacaoLivewireIndex::class)->assertForbidden();
});

test('não delega o perfil se o perfil do delegado for superior', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);
    concederPermissao(Permissao::DelegacaoCreate->value);

    $usuario_a = Usuario::factory()->create([
        'lotacao_id' => $this->lotacao->id,
        'perfil_id' => Perfil::ADMINISTRADOR,
    ]);

    Livewire::test(DelegacaoLivewireIndex::class)
    ->call('create', $usuario_a)
    ->assertForbidden();

    expect($usuario_a->perfil_id)->toBe(Perfil::ADMINISTRADOR)
    ->and($usuario_a->perfil_concedido_por)->toBeNull();
});

test('não delega perfil para usuário de outra lotação', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);
    concederPermissao(Permissao::DelegacaoCreate->value);

    $lotacao_a = Lotacao::factory()->create();
    $usuario_a = Usuario::factory()->create([
        'lotacao_id' => $lotacao_a->id,
        'perfil_id' => Perfil::OBSERVADOR,
    ]);

    Livewire::test(DelegacaoLivewireIndex::class)
    ->call('create', $usuario_a)
    ->assertForbidden();

    expect($usuario_a->perfil_id)->toBe(Perfil::OBSERVADOR)
    ->and($usuario_a->perfil_concedido_por)->toBeNull();
});

test('não remove delegação inexistente', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);
    concederPermissao(Permissao::DelegacaoCreate->value);

    $usuario_a = Usuario::factory()->create([
        'lotacao_id' => $this->lotacao->id,
        'perfil_id' => Perfil::OBSERVADOR,
    ]);

    Livewire::test(DelegacaoLivewireIndex::class)
    ->call('destroy', $usuario_a)
    ->assertForbidden();

    expect($usuario_a->perfil_id)->toBe(Perfil::OBSERVADOR)
    ->and($usuario_a->perfil_concedido_por)->toBeNull();
});

test('não remove delegação de perfil superior', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);
    concederPermissao(Permissao::DelegacaoCreate->value);

    $usuario_a = Usuario::factory()->create([
        'lotacao_id' => $this->lotacao->id,
        'perfil_id' => Perfil::ADMINISTRADOR,
    ]);
    $usuario_b = Usuario::factory()->create([
        'lotacao_id' => $this->lotacao->id,
        'perfil_id' => Perfil::ADMINISTRADOR,
        'perfil_concedido_por' => $usuario_a->id,
        'antigo_perfil_id' => Perfil::OBSERVADOR,
    ]);

    Livewire::test(DelegacaoLivewireIndex::class)
    ->call('destroy', $usuario_b)
    ->assertForbidden();

    expect($usuario_b->perfil_id)->toBe(Perfil::ADMINISTRADOR)
    ->and($usuario_b->perfil_concedido_por)->toBe($usuario_a->id)
    ->and($usuario_b->antigo_perfil_id)->toBe(Perfil::OBSERVADOR);
});

test('não remove delegação de usuário de outra lotação', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);
    concederPermissao(Permissao::DelegacaoCreate->value);

    $lotacao_a = Lotacao::factory()->create();
    $usuario_a = Usuario::factory()->create([
        'lotacao_id' => $lotacao_a->id,
        'perfil_id' => Perfil::ADMINISTRADOR,
    ]);
    $usuario_b = Usuario::factory()->create([
        'lotacao_id' => $lotacao_a->id,
        'perfil_id' => Perfil::ADMINISTRADOR,
        'perfil_concedido_por' => $usuario_a->id,
        'antigo_perfil_id' => Perfil::OBSERVADOR,
    ]);

    Livewire::test(DelegacaoLivewireIndex::class)
    ->call('destroy', $usuario_b)
    ->assertForbidden();

    expect($usuario_b->perfil_id)->toBe(Perfil::ADMINISTRADOR)
    ->and($usuario_b->perfil_concedido_por)->toBe($usuario_a->id)
    ->and($usuario_b->antigo_perfil_id)->toBe(Perfil::OBSERVADOR);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);

    Usuario::factory(30)->for($this->lotacao, 'lotacao')->create();

    Livewire::test(DelegacaoLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('delegaveis', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);

    get(route('autorizacao.delegacao.index'))
    ->assertOk()
    ->assertSeeLivewire(DelegacaoLivewireIndex::class);
});

test('exibe apenas os usuários da memsa lotação', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);

    Usuario::factory(30)->create();
    Usuario::factory(5)->for($this->lotacao, 'lotacao')->create();

    Livewire::test(DelegacaoLivewireIndex::class)
    ->assertCount('delegaveis', 6);
});

test('delega perfil para usuário da mesma lotação se o perfil for inferior', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);
    concederPermissao(Permissao::DelegacaoCreate->value);

    $usuario_a = Usuario::factory()->create([
        'lotacao_id' => $this->lotacao->id,
        'perfil_id' => Perfil::PADRAO,
    ]);

    Livewire::test(DelegacaoLivewireIndex::class)
    ->call('create', $usuario_a)
    ->assertHasNoErrors()
    ->assertOk();

    expect($usuario_a->perfil_id)->toBe(Perfil::GERENTE_NEGOCIO)
    ->and($usuario_a->perfil_concedido_por)->toBe($this->usuario->id)
    ->and($usuario_a->antigo_perfil_id)->toBe(Perfil::PADRAO);
});

test('remove delegação de perfil igual ou inferior, dentro da mesma lotação, mesmo que delegado por outro', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);
    concederPermissao(Permissao::DelegacaoCreate->value);

    $usuario_a = Usuario::factory()->create([
        'lotacao_id' => $this->lotacao->id,
        'perfil_id' => Perfil::OBSERVADOR,
    ]);

    $usuario_b = Usuario::factory()->create([
        'lotacao_id' => $this->lotacao->id,
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
        'perfil_concedido_por' => $this->usuario->id,
        'antigo_perfil_id' => Perfil::OBSERVADOR,
    ]);

    $usuario_c = Usuario::factory()->create([
        'lotacao_id' => $this->lotacao->id,
        'perfil_id' => Perfil::OBSERVADOR,
        'perfil_concedido_por' => $usuario_a->id,
        'antigo_perfil_id' => Perfil::PADRAO,
    ]);

    Livewire::test(DelegacaoLivewireIndex::class)
    ->call('destroy', $usuario_b)
    ->assertHasNoErrors()
    ->assertOk()
    ->call('destroy', $usuario_c)
    ->assertHasNoErrors()
    ->assertOk();

    expect($usuario_b->perfil_id)->toBe(Perfil::OBSERVADOR)
    ->and($usuario_b->perfil_concedido_por)->toBeNull()
    ->and($usuario_b->antigo_perfil_id)->toBeNull()
    ->and($usuario_c->perfil_id)->toBe(Perfil::PADRAO)
    ->and($usuario_c->perfil_concedido_por)->toBeNull()
    ->and($usuario_c->antigo_perfil_id)->toBeNull();
});

test('delegação atribui o perfil do delegado enquanto a revogação volta o delegado ao seu perfil antigo', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);
    concederPermissao(Permissao::DelegacaoCreate->value);

    $usuario_a = Usuario::factory()->create([
        'lotacao_id' => $this->lotacao->id,
        'perfil_id' => Perfil::OBSERVADOR,
    ]);

    $livewire = Livewire::test(DelegacaoLivewireIndex::class)
    ->call('create', $usuario_a)
    ->assertHasNoErrors()
    ->assertOk();

    expect($usuario_a->perfil_id)->toBe(Perfil::GERENTE_NEGOCIO)
    ->and($usuario_a->perfil_concedido_por)->toBe($this->usuario->id)
    ->and($usuario_a->antigo_perfil_id)->toBe(Perfil::OBSERVADOR);

    $livewire
    ->call('destroy', $usuario_a)
    ->assertHasNoErrors()
    ->assertOk();

    expect($usuario_a->perfil_id)->toBe(Perfil::OBSERVADOR)
    ->and($usuario_a->perfil_concedido_por)->toBeNull()
    ->and($usuario_a->antigo_perfil_id)->toBeNull();
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);
    concederPermissao(Permissao::DelegacaoCreate->value);

    Usuario::factory()->create([
        'nome' => 'fulano bar',
        'username' => 'bar baz',
        'lotacao_id' => $this->lotacao->id,
    ]);

    Usuario::factory()->create([
        'nome' => 'fulano foo bazz',
        'username' => 'taz',
        'lotacao_id' => $this->lotacao->id,
    ]);

    Usuario::factory()->create([
        'nome' => 'loren ipsun',
        'username' => 'situr dolor',
        'lotacao_id' => $this->lotacao->id,
    ]);

    Usuario::factory()
    ->for(Lotacao::factory(), 'lotacao')
    ->create([
        'nome' => 'fulano foo bazz de outra lotação',
        'username' => 'another taz',
    ]);

    Livewire::test(DelegacaoLivewireIndex::class)
    ->set('termo', 'taz')
    ->assertCount('delegaveis', 1)
    ->set('termo', 'fulano')
    ->assertCount('delegaveis', 2)
    ->set('termo', '')
    ->assertCount('delegaveis', Usuario::where('lotacao_id', $this->lotacao->id)->count());
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);

    Livewire::test(DelegacaoLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
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

test('DelegacaoLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(DelegacaoLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPesquisa::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
