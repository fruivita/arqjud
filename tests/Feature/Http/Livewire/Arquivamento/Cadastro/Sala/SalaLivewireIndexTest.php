<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Sala\SalaLivewireIndex;
use App\Models\Sala;
use App\Models\Estante;
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

    get(route('arquivamento.cadastro.sala.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.sala.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(SalaLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::SalaViewAny->value);

    Sala::factory(30)->create();

    Livewire::test(SalaLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('salas', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::SalaViewAny->value);

    get(route('arquivamento.cadastro.sala.index'))
    ->assertOk()
    ->assertSeeLivewire(SalaLivewireIndex::class);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::SalaViewAny->value);

    Sala::factory()->create(['numero' => 10]);
    Sala::factory()->create(['numero' => 210]);
    Sala::factory()->create(['numero' => 20]);

    Livewire::test(SalaLivewireIndex::class)
    ->set('termo', '210')
    ->assertCount('salas', 1)
    ->set('termo', '10')
    ->assertCount('salas', 2)
    ->set('termo', '')
    ->assertCount('salas', 3);
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::SalaViewAny->value);
    concederPermissao(Permissao::SalaDelete->value);

    $sala = Sala::factory()->create();

    Livewire::test(SalaLivewireIndex::class)
    ->call('marcarParaExcluir', $sala->id)
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
    concederPermissao(Permissao::SalaViewAny->value);

    Livewire::test(SalaLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'sala',
            'qtd_estantes',
            'localidade',
            'predio',
            'andar',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('SalaLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(SalaLivewireIndex::class))
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
