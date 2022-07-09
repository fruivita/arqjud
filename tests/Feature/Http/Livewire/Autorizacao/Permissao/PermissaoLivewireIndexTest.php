<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao as EnumPermissao;
use App\Http\Livewire\Autorizacao\Permissao\PermissaoLivewireIndex;
use App\Models\Permissao;
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

    get(route('autorizacao.permissao.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('autorizacao.permissao.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PermissaoLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(EnumPermissao::PermissaoViewAny->value);

    Permissao::factory(30)->create();

    Livewire::test(PermissaoLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('permissoes', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(EnumPermissao::PermissaoViewAny->value);

    get(route('autorizacao.permissao.index'))
    ->assertOk()
    ->assertSeeLivewire(PermissaoLivewireIndex::class);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(EnumPermissao::PermissaoViewAny->value);

    Permissao::factory()->create(['nome' => 'permissao_foo']);
    Permissao::factory()->create(['nome' => 'permissao_bar']);
    Permissao::factory()->create(['nome' => 'permissao_baz']);

    Livewire::test(PermissaoLivewireIndex::class)
    ->set('termo', 'foo')
    ->assertCount('permissoes', 1)
    ->set('termo', 'ba')
    ->assertCount('permissoes', 2)
    ->set('termo', '')
    ->assertCount('permissoes', Permissao::count());
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(EnumPermissao::PermissaoViewAny->value);

    Livewire::test(PermissaoLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'permissao',
            'perfis',
            'acoes',
        ],
        'por_pagina' => 10,
    ])
    ->assertSet('limite', 10);
});

test('PermissaoLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(PermissaoLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\ComLimite::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPesquisa::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
