<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\AcaoCheckbox;
use App\Enums\Feedback;
use App\Enums\Permissao as EnumPermissao;
use App\Http\Livewire\Autorizacao\Perfil\PerfilLivewireUpdate;
use App\Models\Permissao;
use App\Models\Perfil;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->perfil = Perfil::factory()->create(['nome' => 'foo', 'descricao' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('autorizacao.perfil.edit', $this->perfil))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('autorizacao.perfil.edit', $this->perfil))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(EnumPermissao::PerfilView->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('nome é obrigatório', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('perfil.nome', '')
    ->call('update')
    ->assertHasErrors(['perfil.nome' => 'required']);
});

test('nome precisa ser uma string', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('perfil.nome', ['bar'])
    ->call('update')
    ->assertHasErrors(['perfil.nome' => 'string']);
});

test('nome precisa ter no máximo 50 caracteres', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('perfil.nome', Str::random(51))
    ->call('update')
    ->assertHasErrors(['perfil.nome' => 'max']);
});

test('nome precisa ser único', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    $perfil = Perfil::factory()->create(['nome' => 'outro foo']);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $perfil])
    ->set('modo_edicao', true)
    ->set('perfil.nome', 'foo')
    ->call('update')
    ->assertHasErrors(['perfil.nome' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('perfil.descricao', '')
    ->call('update')
    ->assertHasNoErrors(['perfil.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('perfil.descricao', ['baz'])
    ->call('update')
    ->assertHasErrors(['perfil.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('perfil.descricao', Str::random(256))
    ->call('update')
    ->assertHasErrors(['perfil.descricao' => 'max']);
});

test('ids das permissões do perfil é opcional', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('selecionados', '')
    ->call('update')
    ->assertHasNoErrors(['selecionados']);
});

test('ids das permissões do perfil precisa ser um array', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('selecionados', 1)
    ->call('update')
    ->assertHasErrors(['selecionados' => 'array']);
});

test('ids das permissões do perfil precisam existir previamente no banco de dados', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('selecionados', [9090909090])
    ->call('update')
    ->assertHasErrors(['selecionados' => 'exists']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Permissao::factory(30)->create();

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('permissoes', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('autorizacao.perfil.edit', $this->perfil))
    ->assertOk()
    ->assertSeeLivewire(PerfilLivewireUpdate::class);
})->with([
    EnumPermissao::PerfilView->value,
    EnumPermissao::PerfilUpdate->value
]);

test('define as permissões que devem ser pre-selecionados de acordo com o perfil', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Permissao::factory(30)->create();
    $perfil = Perfil::factory()->has(Permissao::factory(20), 'permissoes')->create();

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $perfil])
    ->assertCount('selecionados', 20);
});

test('ações de checkbox funcionam como esperado', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);
    $permissoes = Permissao::count();

    Permissao::factory(50)->create();
    $perfil = Perfil::factory()->create();

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $perfil])
    ->assertCount('selecionados', 0)
    ->set('acao_checkbox', AcaoCheckbox::SelecionarTodos->value)
    ->assertCount('selecionados', $permissoes + 50)
    ->set('acao_checkbox', AcaoCheckbox::DesmarcarTodos->value)
    ->assertCount('selecionados', 0)
    ->set('acao_checkbox', AcaoCheckbox::SelecionarTodosNaPagina->value)
    ->assertCount('selecionados', 10)
    ->set('acao_checkbox', AcaoCheckbox::DesmarcarTodosNaPagina->value)
    ->assertCount('selecionados', 0);
});

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('permissões são opcionais na atualização', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    $perfil = Perfil::factory()->has(Permissao::factory(1), 'permissoes')->create();

    expect($perfil->permissoes)->toHaveCount(1);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $perfil])
    ->set('modo_edicao', true)
    ->set('selecionados', null)
    ->call('update')
    ->assertOk();

    $perfil->refresh()->load('permissoes');

    expect($perfil->permissoes)->toBeEmpty();
});

test('atualiza um registro com permissão', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    $this->perfil->load('permissoes');

    expect($this->perfil->nome)->toBe('foo')
    ->and($this->perfil->descricao)->toBe('bar')
    ->and($this->perfil->permissoes)->toBeEmpty();

    $permissao = Permissao::first();

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->set('modo_edicao', true)
    ->set('perfil.nome', 'novo foo')
    ->set('perfil.descricao', 'novo bar')
    ->set('selecionados', [$permissao->id])
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->perfil->refresh();

    expect($this->perfil->nome)->toBe('novo foo')
    ->and($this->perfil->descricao)->toBe('novo bar')
    ->and($this->perfil->permissoes->first()->id)->toBe($permissao->id);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(EnumPermissao::PerfilUpdate->value);

    Livewire::test(PerfilLivewireUpdate::class, ['perfil' => $this->perfil])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'seletores',
            'permissao',
            'descricao',
            'acoes',
        ],
        'por_pagina' => 10
    ]);
});

test('PerfilLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(PerfilLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComAcoesDeCheckbox::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
