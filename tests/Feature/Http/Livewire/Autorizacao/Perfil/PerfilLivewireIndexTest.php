<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Http\Livewire\Autorizacao\Perfil\PerfilLivewireIndex;
use App\Models\Perfil;
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

    get(route('autorizacao.perfil.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('autorizacao.perfil.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PerfilLivewireIndex::class)->assertForbidden();
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PerfilViewAny->value);

    Perfil::factory(30)->create();

    Livewire::test(PerfilLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('perfis', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::PerfilViewAny->value);

    get(route('autorizacao.perfil.index'))
    ->assertOk()
    ->assertSeeLivewire(PerfilLivewireIndex::class);
});

test('pesquisa retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PerfilViewAny->value);

    Perfil::factory()->create(['nome' => 'perfil_foo']);
    Perfil::factory()->create(['nome' => 'perfil_bar']);
    Perfil::factory()->create(['nome' => 'perfil_baz']);

    Livewire::test(PerfilLivewireIndex::class)
    ->set('termo', 'foo')
    ->assertCount('perfis', 1)
    ->set('termo', 'ba')
    ->assertCount('perfis', 2)
    ->set('termo', '')
    ->assertCount('perfis', Perfil::count());
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::PerfilViewAny->value);

    Livewire::test(PerfilLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'perfil',
            'permissoes',
            'acoes',
        ],
        'por_pagina' => 10,
    ])
    ->assertSet('limite', 10);
});

test('PerfilLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(PerfilLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\ComLimite::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPesquisa::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
