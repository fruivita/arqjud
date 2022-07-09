<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Predio\PredioLivewireUpdate;
use App\Models\Predio;
use App\Models\Andar;
use App\Models\Sala;
use App\Models\Localidade;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->predio = Predio::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.predio.edit', $this->predio->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.predio.edit', $this->predio->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::PredioView->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('nome é obrigatório', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.nome', '')
    ->call('update')
    ->assertHasErrors(['predio.nome' => 'required']);
});

test('nome precisa ser uma string', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.nome', ['foo'])
    ->call('update')
    ->assertHasErrors(['predio.nome' => 'string']);
});

test('nome precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.nome', Str::random(101))
    ->call('update')
    ->assertHasErrors(['predio.nome' => 'max']);
});

test('nome e localidade_id precisam ser únicos', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    $localidade = Localidade::factory()->create();
    Predio::factory()->create(['nome' => 'foo', 'localidade_id' => $localidade->id]);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.nome', 'foo')
    ->set('predio.localidade_id', $localidade->id)
    ->call('update')
    ->assertHasErrors(['predio.nome' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.descricao', '')
    ->call('update')
    ->assertHasNoErrors(['predio.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.descricao', ['foo'])
    ->call('update')
    ->assertHasErrors(['predio.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.descricao', Str::random(256))
    ->call('update')
    ->assertHasErrors(['predio.descricao' => 'max']);
});

test('localidade_id é obrigatório', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.localidade_id', '')
    ->call('update')
    ->assertHasErrors(['predio.localidade_id' => 'required']);
});

test('localidade_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.localidade_id', 'foo')
    ->call('update')
    ->assertHasErrors(['predio.localidade_id' => 'integer']);
});

test('localidade_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.localidade_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['predio.localidade_id' => 'exists']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Andar::factory(30)->for($this->predio, 'predio')->create();

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('andares', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('arquivamento.cadastro.predio.edit', $this->predio->id))
    ->assertOk()
    ->assertSeeLivewire(PredioLivewireUpdate::class);
})->with([
    Permissao::PredioView->value,
    Permissao::PredioUpdate->value
]);

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.nome', 'foo')
    ->set('predio.localidade_id', $localidade->id)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::PredioUpdate->value);
    concederPermissao(Permissao::AndarDelete->value);

    $andar = Andar::factory()->for($this->predio, 'predio')->create();

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->call('marcarParaExcluir', $andar->id)
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

test('localidades estão disponíveis para seleção', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Localidade::factory(10)->create();

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->assertCount('localidades', 11);
});

test('atualiza um registro com permissão', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->set('modo_edicao', true)
    ->set('predio.nome', 'foo')
    ->set('predio.descricao', 'foo bar')
    ->set('predio.localidade_id', $localidade->id)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->predio->refresh();

    expect($this->predio->nome)->toBe('foo')
    ->and($this->predio->descricao)->toBe('foo bar')
    ->and($this->predio->localidade_id)->toBe($localidade->id);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    Livewire::test(PredioLivewireUpdate::class, ['id' => $this->predio->id])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'andar',
            'qtd_salas',
            'acoes',
        ],
        'por_pagina' => 10
    ]);
});

test('PredioLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(PredioLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
