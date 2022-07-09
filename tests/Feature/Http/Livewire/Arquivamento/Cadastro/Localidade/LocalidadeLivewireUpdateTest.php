<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Localidade\LocalidadeLivewireUpdate;
use App\Models\Predio;
use App\Models\Andar;
use App\Models\Localidade;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->localidade = Localidade::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.localidade.edit', $this->localidade))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.localidade.edit', $this->localidade))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::LocalidadeView->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('nome é obrigatório', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->set('localidade.nome', '')
    ->call('update')
    ->assertHasErrors(['localidade.nome' => 'required']);
});

test('nome precisa ser uma string', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->set('localidade.nome', ['foo'])
    ->call('update')
    ->assertHasErrors(['localidade.nome' => 'string']);
});

test('nome precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->set('localidade.nome', Str::random(101))
    ->call('update')
    ->assertHasErrors(['localidade.nome' => 'max']);
});

test('nome precisa ser único', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Localidade::factory()->create(['nome' => 'foo']);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->set('localidade.nome', 'foo')
    ->call('update')
    ->assertHasErrors(['localidade.nome' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->set('localidade.descricao', '')
    ->call('update')
    ->assertHasNoErrors(['localidade.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->set('localidade.descricao', ['foo'])
    ->call('update')
    ->assertHasErrors(['localidade.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->set('localidade.descricao', Str::random(256))
    ->call('update')
    ->assertHasErrors(['localidade.descricao' => 'max']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Predio::factory(30)->for($this->localidade, 'localidade')->create();

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('predios', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('arquivamento.cadastro.localidade.edit', $this->localidade))
    ->assertOk()
    ->assertSeeLivewire(LocalidadeLivewireUpdate::class);
})->with([
    Permissao::LocalidadeView->value,
    Permissao::LocalidadeUpdate->value
]);

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->set('localidade.nome', 'foo')
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);
    concederPermissao(Permissao::PredioDelete->value);

    $predio = Predio::factory()->for($this->localidade, 'localidade')->create();

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->call('marcarParaExcluir', $predio->id)
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

test('atualiza um registro com permissão', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->set('modo_edicao', true)
    ->set('localidade.nome', 'foo')
    ->set('localidade.descricao', 'foo bar')
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->localidade->refresh();

    expect($this->localidade->nome)->toBe('foo')
    ->and($this->localidade->descricao)->toBe('foo bar');
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    Livewire::test(LocalidadeLivewireUpdate::class, ['localidade' => $this->localidade])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'predio',
            'qtd_andares',
            'acoes',
        ],
        'por_pagina' => 10
    ]);
});

test('LocalidadeLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(LocalidadeLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
