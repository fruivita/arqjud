<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Predio\PredioLivewireIndex;
use App\Models\Predio;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
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

    get(route('arquivamento.cadastro.predio.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.predio.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PredioLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PredioViewAny->value);

    Predio::factory(30)->create();

    Livewire::test(PredioLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('predios', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::PredioViewAny->value);

    get(route('arquivamento.cadastro.predio.index'))
    ->assertOk()
    ->assertSeeLivewire(PredioLivewireIndex::class);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PredioViewAny->value);

    Predio::factory()->create(['nome' => 'foo']);
    Predio::factory()->create(['nome' => 'baz']);
    Predio::factory()->create(['nome' => 'bar']);

    Livewire::test(PredioLivewireIndex::class)
    ->set('termo', 'foo')
    ->assertCount('predios', 1)
    ->set('termo', 'ba')
    ->assertCount('predios', 2)
    ->set('termo', '')
    ->assertCount('predios', 3);
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::PredioViewAny->value);
    concederPermissao(Permissao::PredioDelete->value);

    $predio = Predio::factory()->create();

    Livewire::test(PredioLivewireIndex::class)
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

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::PredioViewAny->value);

    Livewire::test(PredioLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'predio',
            'qtd_andares',
            'localidade',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('PredioLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(PredioLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPesquisa::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
