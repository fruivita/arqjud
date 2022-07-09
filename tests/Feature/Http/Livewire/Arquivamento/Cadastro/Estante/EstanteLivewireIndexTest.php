<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Estante\EstanteLivewireIndex;
use App\Models\Estante;
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

    get(route('arquivamento.cadastro.estante.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.estante.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(EstanteLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::EstanteViewAny->value);

    Estante::factory(30)->create();

    Livewire::test(EstanteLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('estantes', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::EstanteViewAny->value);

    get(route('arquivamento.cadastro.estante.index'))
    ->assertOk()
    ->assertSeeLivewire(EstanteLivewireIndex::class);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::EstanteViewAny->value);

    Estante::factory()->create(['numero' => 10, 'apelido' => 'foo']);
    Estante::factory()->create(['numero' => 210, 'apelido' => 'bar']);
    Estante::factory()->create(['numero' => 20, 'apelido' => 'baz']);

    Livewire::test(EstanteLivewireIndex::class)
    ->set('termo', '210')
    ->assertCount('estantes', 1)
    ->set('termo', '10')
    ->assertCount('estantes', 2)
    ->set('termo', '')
    ->assertCount('estantes', 3)
    ->set('termo', 'ba')
    ->assertCount('estantes', 2);
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::EstanteViewAny->value);
    concederPermissao(Permissao::EstanteDelete->value);

    $estante = Estante::factory()->create();

    Livewire::test(EstanteLivewireIndex::class)
    ->call('marcarParaExcluir', $estante->id)
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
    concederPermissao(Permissao::EstanteViewAny->value);

    Livewire::test(EstanteLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'estante',
            'apelido',
            'qtd_prateleiras',
            'localidade',
            'predio',
            'andar',
            'sala',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('EstanteLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(EstanteLivewireIndex::class))
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
