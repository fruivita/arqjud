<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Predio\PredioLivewireCreate;
use App\Models\Localidade;
use App\Models\Predio;
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

    get(route('arquivamento.cadastro.predio.create', $this->localidade->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.predio.create', $this->localidade->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->assertForbidden();
});

// Rules
test('nome é obrigatório', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.nome', '')
    ->call('store')
    ->assertHasErrors(['predio.nome' => 'required']);
});

test('nome precisa ser uma string', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.nome', ['foo'])
    ->call('store')
    ->assertHasErrors(['predio.nome' => 'string']);
});

test('nome precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.nome', Str::random(101))
    ->call('store')
    ->assertHasErrors(['predio.nome' => 'max']);
});

test('nome e localidade_id precisam ser únicos', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Predio::factory()->create(['nome' => 'foo', 'localidade_id' => $this->localidade->id]);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.nome', 'foo')
    ->call('store')
    ->assertHasErrors(['predio.nome' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.descricao', '')
    ->call('store')
    ->assertHasNoErrors(['predio.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.descricao', ['foo'])
    ->call('store')
    ->assertHasErrors(['predio.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.descricao', Str::random(256))
    ->call('store')
    ->assertHasErrors(['predio.descricao' => 'max']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Predio::factory(30)->for($this->localidade, 'localidade')->create();

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('predios', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::PredioCreate->value);

    get(route('arquivamento.cadastro.predio.create', $this->localidade->id))
    ->assertOk()
    ->assertSeeLivewire(PredioLivewireCreate::class);
});

test('emite evento de feedback ao criar um registro', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.nome', 'nome')
    ->call('store')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::PredioCreate->value);
    concederPermissao(Permissao::PredioDelete->value);

    $predio = Predio::factory()->create();

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
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

test('cria um registro com permissão', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.nome', 'foo')
    ->set('predio.descricao', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $predio = Predio::with('localidade')->first();

    expect($predio->nome)->toBe('foo')
    ->and($predio->descricao)->toBe('foo bar')
    ->and($predio->localidade->id)->toBe($this->localidade->id);
});

test('reseta para um modelo em branco após criar um registro', function () {
    concederPermissao(Permissao::PredioCreate->value);

    $branco = new Predio();

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->set('predio.nome', 'foo')
    ->call('store')
    ->assertOk()
    ->assertSet('predio', $branco);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::PredioCreate->value);

    Livewire::test(PredioLivewireCreate::class, ['id' => $this->localidade->id])
    ->assertSet('preferencias', [
        'colunas' => [
            'predio',
            'qtd_andares',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('PredioLivewireCreate usa trait', function () {
    expect(
        collect(class_uses(PredioLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
