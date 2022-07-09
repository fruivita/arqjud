<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Caixa;
use App\Models\VolumeCaixa;
use App\Policies\CaixaPolicy;
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
test('usuário sem permissão não pode listar as caixas', function () {
    expect((new CaixaPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma caixa', function () {
    expect((new CaixaPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criar uma caixa', function () {
    expect((new CaixaPolicy())->create($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criar múltiplas caixas', function () {
    expect((new CaixaPolicy())->createMany($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma caixa', function () {
    expect((new CaixaPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma caixa', function () {
    expect((new CaixaPolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma caixa', function () {
    $caixa = Caixa::factory()->create();

    expect((new CaixaPolicy())->delete($this->usuario, $caixa))->toBeFalse();
});

test('caixa com volumes não pode ser excluida', function () {
    concederPermissao(Permissao::CaixaDelete->value);

    $caixa = Caixa::factory()->has(VolumeCaixa::factory(2), 'volumes')->create();

    expect((new CaixaPolicy())->delete($this->usuario, $caixa))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as caixas', function () {
    concederPermissao(Permissao::CaixaViewAny->value);

    expect((new CaixaPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma caixa', function () {
    concederPermissao(Permissao::CaixaView->value);

    expect((new CaixaPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criar uma caixa', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    expect((new CaixaPolicy())->create($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criar múltiplas caixas', function () {
    concederPermissao(Permissao::CaixaCreateMany->value);

    expect((new CaixaPolicy())->createMany($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma caixa', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    expect((new CaixaPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma caixa por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::CaixaView->value);

    expect((new CaixaPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma caixa por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    expect((new CaixaPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir uma caixa', function () {
    concederPermissao(Permissao::CaixaDelete->value);

    $caixa = Caixa::factory()->create();

    expect((new CaixaPolicy())->delete($this->usuario, $caixa))->toBeTrue();
});

test('caixa sem volumes pode ser excluída', function () {
    concederPermissao(Permissao::CaixaDelete->value);

    $caixa = Caixa::factory()->create();

    expect((new CaixaPolicy())->delete($this->usuario, $caixa))->toBeTrue();
});
