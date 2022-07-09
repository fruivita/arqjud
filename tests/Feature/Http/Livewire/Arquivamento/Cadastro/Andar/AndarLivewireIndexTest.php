<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Andar\AndarLivewireIndex;
use App\Models\Andar;
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

    get(route('arquivamento.cadastro.andar.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.andar.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(AndarLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::AndarViewAny->value);

    Andar::factory(30)->create();

    Livewire::test(AndarLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('andares', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::AndarViewAny->value);

    get(route('arquivamento.cadastro.andar.index'))
    ->assertOk()
    ->assertSeeLivewire(AndarLivewireIndex::class);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::AndarViewAny->value);

    Andar::factory()->create(['numero' => 10, 'apelido' => 'foo']);
    Andar::factory()->create(['numero' => 210, 'apelido' => 'bar']);
    Andar::factory()->create(['numero' => 20, 'apelido' => 'baz']);

    Livewire::test(AndarLivewireIndex::class)
    ->set('termo', '210')
    ->assertCount('andares', 1)
    ->set('termo', '10')
    ->assertCount('andares', 2)
    ->set('termo', '')
    ->assertCount('andares', 3)
    ->set('termo', 'ba')
    ->assertCount('andares', 2);
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::AndarViewAny->value);
    concederPermissao(Permissao::AndarDelete->value);

    $andar = Andar::factory()->create();

    Livewire::test(AndarLivewireIndex::class)
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

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::AndarViewAny->value);

    Livewire::test(AndarLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'andar',
            'apelido',
            'qtd_salas',
            'localidade',
            'predio',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('AndarLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(AndarLivewireIndex::class))
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
