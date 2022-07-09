<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Localidade;
use App\Models\Predio;
use App\Policies\LocalidadePolicy;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->usuario = login('foo');
});

afterEach(function () {
    logout();
});

// Proibido
test('usuário sem permissão não pode listar as localidades', function () {
    expect((new LocalidadePolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma localidade', function () {
    expect((new LocalidadePolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criar uma localidade', function () {
    expect((new LocalidadePolicy())->create($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma localidade', function () {
    expect((new LocalidadePolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma localidade', function () {
    expect((new LocalidadePolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma localidade', function () {
    $localidade = Localidade::factory()->create();

    expect((new LocalidadePolicy())->delete($this->usuario, $localidade))->toBeFalse();
});

test('localidade with prédios não pode ser excluida', function () {
    concederPermissao(Permissao::LocalidadeDelete->value);

    $localidade = Localidade::factory()->has(Predio::factory(2), 'predios')->create();

    expect((new LocalidadePolicy())->delete($this->usuario, $localidade))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as localidades', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);

    expect((new LocalidadePolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma localidade', function () {
    concederPermissao(Permissao::LocalidadeView->value);

    expect((new LocalidadePolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criar uma localidade', function () {
    concederPermissao(Permissao::LocalidadeCreate->value);

    expect((new LocalidadePolicy())->create($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma localidade', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    expect((new LocalidadePolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma localidade por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::LocalidadeView->value);

    expect((new LocalidadePolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma localidade por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::LocalidadeUpdate->value);

    expect((new LocalidadePolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir uma localidade', function () {
    concederPermissao(Permissao::LocalidadeDelete->value);

    $localidade = Localidade::factory()->create();

    expect((new LocalidadePolicy())->delete($this->usuario, $localidade))->toBeTrue();
});

test('localidade sem prédios pode ser excluída', function () {
    concederPermissao(Permissao::LocalidadeDelete->value);

    $localidade = Localidade::factory()->create();

    expect((new LocalidadePolicy())->delete($this->usuario, $localidade))->toBeTrue();
});
