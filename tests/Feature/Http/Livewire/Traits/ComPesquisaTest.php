<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Localidade\LocalidadeLivewireIndex;
use App\Models\Localidade;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->localidade = Localidade::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Rules
test('termo pesquisável precisa ser uma string', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);

    Livewire::test(LocalidadeLivewireIndex::class)
    ->set('termo', ['foo'])
    ->assertHasErrors(['termo' => 'string']);
});

test('termo pesquisável precisa ter no máximo 50 caracteres', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);

    Livewire::test(LocalidadeLivewireIndex::class)
    ->set('termo', Str::random(51))
    ->assertHasErrors(['termo' => 'max']);
});

test('termo pesquisável é validado em tempo real', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);

    Livewire::test(LocalidadeLivewireIndex::class)
    ->set('termo', Str::random(50))
    ->assertHasNoErrors()
    ->set('termo', Str::random(51))
    ->assertHasErrors(['termo' => 'max']);
});
