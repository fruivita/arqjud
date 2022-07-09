<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\AcaoCheckbox;
use App\Enums\Feedback;
use App\Enums\Permissao as EnumPermissao;
use App\Http\Livewire\Autorizacao\Permissao\PermissaoLivewireUpdate;
use App\Models\Perfil;
use App\Models\Permissao;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->permissao = Permissao::factory()->create(['nome' => 'foo', 'descricao' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('autorizacao.permissao.edit', $this->permissao))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('autorizacao.permissao.edit', $this->permissao))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(EnumPermissao::PermissaoView->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('nome é obrigatório', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('permissao.nome', '')
    ->call('update')
    ->assertHasErrors(['permissao.nome' => 'required']);
});

test('nome precisa ser uma string', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('permissao.nome', ['bar'])
    ->call('update')
    ->assertHasErrors(['permissao.nome' => 'string']);
});

test('nome precisa ter no máximo 50 caracteres', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('permissao.nome', Str::random(51))
    ->call('update')
    ->assertHasErrors(['permissao.nome' => 'max']);
});

test('nome precisa ser único', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    $permissao = Permissao::factory()->create(['nome' => 'outro foo']);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $permissao])
    ->set('modo_edicao', true)
    ->set('permissao.nome', 'foo')
    ->call('update')
    ->assertHasErrors(['permissao.nome' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('permissao.descricao', '')
    ->call('update')
    ->assertHasNoErrors(['permissao.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('permissao.descricao', ['baz'])
    ->call('update')
    ->assertHasErrors(['permissao.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('permissao.descricao', Str::random(256))
    ->call('update')
    ->assertHasErrors(['permissao.descricao' => 'max']);
});

test('ids dos perfis da permissão é opcional', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('selecionados', '')
    ->call('update')
    ->assertHasNoErrors(['selecionados']);
});

test('ids dos perfis da permissão precisa ser um array', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('selecionados', 1)
    ->call('update')
    ->assertHasErrors(['selecionados' => 'array']);
});

test('ids dos perfis da permissão precisam existir previamente no banco de dados', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('selecionados', [9090909090])
    ->call('update')
    ->assertHasErrors(['selecionados' => 'exists']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Perfil::factory(30)->create();

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('perfis', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('autorizacao.permissao.edit', $this->permissao))
    ->assertOk()
    ->assertSeeLivewire(PermissaoLivewireUpdate::class);
})->with([
    EnumPermissao::PermissaoView->value,
    EnumPermissao::PermissaoUpdate->value,
]);

test('define os perfis que devem ser pre-selecionados de acordo com a permissão', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Perfil::factory(30)->create();
    $permissao = Permissao::factory()->has(Perfil::factory(20), 'perfis')->create();

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $permissao])
    ->assertCount('selecionados', 20);
});

test('ações de checkbox funcionam como esperado', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);
    $perfis = Perfil::count();

    Perfil::factory(50)->create();
    $permissao = Permissao::factory()->create();

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $permissao])
    ->assertCount('selecionados', 0)
    ->set('acao_checkbox', AcaoCheckbox::SelecionarTodos->value)
    ->assertCount('selecionados', $perfis + 50)
    ->set('acao_checkbox', AcaoCheckbox::DesmarcarTodos->value)
    ->assertCount('selecionados', 0)
    ->set('acao_checkbox', AcaoCheckbox::SelecionarTodosNaPagina->value)
    ->assertCount('selecionados', 10)
    ->set('acao_checkbox', AcaoCheckbox::DesmarcarTodosNaPagina->value)
    ->assertCount('selecionados', 0);
});

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('perfis são opcionais na atualização', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    $permissao = Permissao::factory()->has(Perfil::factory(1), 'perfis')->create();

    expect($permissao->perfis)->toHaveCount(1);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $permissao])
    ->set('modo_edicao', true)
    ->set('selecionados', null)
    ->call('update')
    ->assertOk();

    $permissao->refresh()->load('perfis');

    expect($permissao->perfis)->toBeEmpty();
});

test('atualiza um registro com permissão', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    $this->permissao->load('perfis');

    expect($this->permissao->nome)->toBe('foo')
    ->and($this->permissao->descricao)->toBe('bar')
    ->and($this->permissao->perfis)->toBeEmpty();

    $perfil = Perfil::first();

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->set('modo_edicao', true)
    ->set('permissao.nome', 'novo foo')
    ->set('permissao.descricao', 'novo bar')
    ->set('selecionados', [$perfil->id])
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->permissao->refresh();

    expect($this->permissao->nome)->toBe('novo foo')
    ->and($this->permissao->descricao)->toBe('novo bar')
    ->and($this->permissao->perfis->first()->id)->toBe($perfil->id);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(EnumPermissao::PermissaoUpdate->value);

    Livewire::test(PermissaoLivewireUpdate::class, ['permissao' => $this->permissao])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'seletores',
            'perfil',
            'descricao',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('PermissaoLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(PermissaoLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComAcoesDeCheckbox::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
