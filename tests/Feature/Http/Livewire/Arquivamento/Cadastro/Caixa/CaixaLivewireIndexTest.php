<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Caixa\CaixaLivewireIndex;
use App\Models\Caixa;
use App\Models\VolumeCaixa;
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

    get(route('arquivamento.cadastro.caixa.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.caixa.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(CaixaLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::CaixaViewAny->value);

    Caixa::factory(30)->create();

    Livewire::test(CaixaLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('caixas', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::CaixaViewAny->value);

    get(route('arquivamento.cadastro.caixa.index'))
    ->assertOk()
    ->assertSeeLivewire(CaixaLivewireIndex::class);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::CaixaViewAny->value);

    Caixa::factory()->create(['numero' => '100', 'ano' => '2015']);
    Caixa::factory()->create(['numero' => '120152', 'ano' => '2020']);
    Caixa::factory()->create(['numero' => '200', 'ano' => '2020']);

    Livewire::test(CaixaLivewireIndex::class)
    ->set('termo', '120152')
    ->assertCount('caixas', 1)
    ->set('termo', '2015')
    ->assertCount('caixas', 2)
    ->set('termo', '2020')
    ->assertCount('caixas', 2)
    ->set('termo', '')
    ->assertCount('caixas', 3);
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::CaixaViewAny->value);
    concederPermissao(Permissao::CaixaDelete->value);

    $caixa = Caixa::factory()->create();

    Livewire::test(CaixaLivewireIndex::class)
    ->call('marcarParaExcluir', $caixa->id)
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
    concederPermissao(Permissao::CaixaViewAny->value);

    Livewire::test(CaixaLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'caixa',
            'ano',
            'qtd_volumes',
            'localidade',
            'predio',
            'andar',
            'sala',
            'estante',
            'prateleira',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('CaixaLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(CaixaLivewireIndex::class))
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
