<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Localidade\LocalidadeLivewireIndex;
use App\Models\Localidade;
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

    get(route('arquivamento.cadastro.localidade.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.localidade.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(LocalidadeLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);

    Localidade::factory(30)->create();

    Livewire::test(LocalidadeLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('localidades', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);

    get(route('arquivamento.cadastro.localidade.index'))
    ->assertOk()
    ->assertSeeLivewire(LocalidadeLivewireIndex::class);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);

    Localidade::factory()->create(['nome' => 'foo']);
    Localidade::factory()->create(['nome' => 'bar']);
    Localidade::factory()->create(['nome' => 'baz']);

    Livewire::test(LocalidadeLivewireIndex::class)
    ->set('termo', 'foo')
    ->assertCount('localidades', 1)
    ->set('termo', 'ba')
    ->assertCount('localidades', 2)
    ->set('termo', '')
    ->assertCount('localidades', 3);
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);
    concederPermissao(Permissao::LocalidadeDelete->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(LocalidadeLivewireIndex::class)
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

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);

    Livewire::test(LocalidadeLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'localidade',
            'qtd_predios',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('LocalidadeLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(LocalidadeLivewireIndex::class))
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
