<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Administracao\Documentacao\DocumentacaoLivewireIndex;
use App\Models\Documentacao;
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

    get(route('administracao.documentacao.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('administracao.documentacao.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(DocumentacaoLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::DocumentacaoViewAny->value);

    get(route('administracao.documentacao.index'))
    ->assertOk()
    ->assertSeeLivewire(DocumentacaoLivewireIndex::class);
});

test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::DocumentacaoViewAny->value);

    Documentacao::factory(30)->create();

    Livewire::test(DocumentacaoLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('documentacoes', 25);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::DocumentacaoViewAny->value);

    Documentacao::factory()->create(['app_link' => 'foo', 'doc_link' => 'loren']);
    Documentacao::factory()->create(['app_link' => 'bar', 'doc_link' => 'ipsun']);
    Documentacao::factory()->create(['app_link' => 'baz', 'doc_link' => 'dolor']);

    Livewire::test(DocumentacaoLivewireIndex::class)
    ->set('termo', 'foo')
    ->assertCount('documentacoes', 1)
    ->set('termo', 'ba')
    ->assertCount('documentacoes', 2)
    ->set('termo', '')
    ->assertCount('documentacoes', 3)
    ->set('termo', 'lo')
    ->assertCount('documentacoes', 2);
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::DocumentacaoViewAny->value);
    concederPermissao(Permissao::DocumentacaoDelete->value);

    $doc = Documentacao::factory()->create(['app_link' => 'foo']);

    Livewire::test(DocumentacaoLivewireIndex::class)
    ->call('marcarParaExcluir', $doc->id)
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
    concederPermissao(Permissao::DocumentacaoViewAny->value);

    Livewire::test(DocumentacaoLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'app_url',
            'doc_url',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('DocumentacaoLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(DocumentacaoLivewireIndex::class))
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
