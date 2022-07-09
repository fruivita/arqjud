<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Localidade\LocalidadeLivewireCreate;
use App\Models\Predio;
use App\Models\Localidade;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
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

    get(route('arquivamento.cadastro.localidade.create'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.localidade.create'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(LocalidadeLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('nome é obrigatório', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.nome', '')
    ->call('store')
    ->assertHasErrors(['localidade.nome' => 'required']);
});

test('nome precisa ser uma string', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.nome', ['foo'])
    ->call('store')
    ->assertHasErrors(['localidade.nome' => 'string']);
});

test('nome precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.nome', Str::random(101))
    ->call('store')
    ->assertHasErrors(['localidade.nome' => 'max']);
});

test('nome precisa ser único', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Localidade::factory()->create(['nome' => 'foo']);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.nome', 'foo')
    ->call('store')
    ->assertHasErrors(['localidade.nome' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.descricao', '')
    ->call('store')
    ->assertHasNoErrors(['localidade.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.descricao', ['foo'])
    ->call('store')
    ->assertHasErrors(['localidade.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.descricao', Str::random(256))
    ->call('store')
    ->assertHasErrors(['localidade.descricao' => 'max']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Localidade::factory(30)->create();

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('localidades', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    get(route('arquivamento.cadastro.localidade.create'))
    ->assertOk()
    ->assertSeeLivewire(LocalidadeLivewireCreate::class);
});

test('emite evento de feedback ao criar um registro', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.nome', 'foo')
    ->call('store')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);
    concederPermissao(Permissao::LocalidadeDelete->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(LocalidadeLivewireCreate::class)
    ->call('marcarParaExcluir', $localidade->id)
    ->call('destroy')
    ->assertOk()
    ->assertDispatchedBrowserEvent('notificacao', [
        'tipo' => Feedback::Sucesso->value,
        'icone' => Feedback::Sucesso->icone(),
        'cabecalho' => Feedback::Sucesso->nome(),
        'mensagem' => null,
        'duracao' => 3000,
    ]);
});

test('cria um registro com permissão', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.nome', 'foo')
    ->set('localidade.descricao', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $localidade = Localidade::first();

    expect($localidade->nome)->toBe('foo')
    ->and($localidade->descricao)->toBe('foo bar');
});

test('reseta para um modelo em branco após criar um registro', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    $branco = new Localidade();

    Livewire::test(LocalidadeLivewireCreate::class)
    ->set('localidade.nome', 'foo')
    ->call('store')
    ->assertOk()
    ->assertSet('localidade', $branco);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    Livewire::test(LocalidadeLivewireCreate::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'localidade',
            'qtd_predios',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('LocalidadeLivewireCreate usa trait', function () {
    expect(
        collect(class_uses(LocalidadeLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
