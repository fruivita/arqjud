<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Prateleira\PrateleiraLivewireIndex;
use App\Models\Caixa;
use App\Models\Prateleira;
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

    get(route('arquivamento.cadastro.prateleira.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.prateleira.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PrateleiraLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PrateleiraViewAny->value);

    Prateleira::factory(30)->create();

    Livewire::test(PrateleiraLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('prateleiras', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::PrateleiraViewAny->value);

    get(route('arquivamento.cadastro.prateleira.index'))
    ->assertOk()
    ->assertSeeLivewire(PrateleiraLivewireIndex::class);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PrateleiraViewAny->value);

    Prateleira::factory()->create(['numero' => 10, 'apelido' => 'foo']);
    Prateleira::factory()->create(['numero' => 210, 'apelido' => 'bar']);
    Prateleira::factory()->create(['numero' => 20, 'apelido' => 'baz']);

    Livewire::test(PrateleiraLivewireIndex::class)
    ->set('termo', '210')
    ->assertCount('prateleiras', 1)
    ->set('termo', '10')
    ->assertCount('prateleiras', 2)
    ->set('termo', '')
    ->assertCount('prateleiras', 3)
    ->set('termo', 'ba')
    ->assertCount('prateleiras', 2);
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::PrateleiraViewAny->value);
    concederPermissao(Permissao::PrateleiraDelete->value);

    $prateleira = Prateleira::factory()->create();

    Livewire::test(PrateleiraLivewireIndex::class)
    ->call('marcarParaExcluir', $prateleira->id)
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
    concederPermissao(Permissao::PrateleiraViewAny->value);

    Livewire::test(PrateleiraLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'prateleira',
            'apelido',
            'qtd_caixas',
            'localidade',
            'predio',
            'andar',
            'sala',
            'estante',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('PrateleiraLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(PrateleiraLivewireIndex::class))
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
