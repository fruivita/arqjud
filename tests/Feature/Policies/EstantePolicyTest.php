<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Prateleira;
use App\Models\Estante;
use App\Policies\EstantePolicy;
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
test('usuário sem permissão não pode listar as estantes', function () {
    expect((new EstantePolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma estante', function () {
    expect((new EstantePolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criar uma estante', function () {
    expect((new EstantePolicy())->create($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma estante', function () {
    expect((new EstantePolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma estante', function () {
    expect((new EstantePolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

test('usuário com permissão pode individualmente visualizar uma estante por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::EstanteView->value);

    expect((new EstantePolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma estante por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    expect((new EstantePolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário sem permissão não pode excluir uma estante', function () {
    $estante = Estante::factory()->create();

    expect((new EstantePolicy())->delete($this->usuario, $estante))->toBeFalse();
});

test('estante com prateleiras não pode ser excluida', function () {
    concederPermissao(Permissao::EstanteDelete->value);

    $estante = Estante::factory()
    ->has(Prateleira::factory(2), 'prateleiras')
    ->create();

    expect((new EstantePolicy())->delete($this->usuario, $estante))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as estantes', function () {
    concederPermissao(Permissao::EstanteViewAny->value);

    expect((new EstantePolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma estante', function () {
    concederPermissao(Permissao::EstanteView->value);

    expect((new EstantePolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criar uma estante', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    expect((new EstantePolicy())->create($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma estante', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    expect((new EstantePolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir uma estante', function () {
    concederPermissao(Permissao::EstanteDelete->value);

    $estante = Estante::factory()->create();

    expect((new EstantePolicy())->delete($this->usuario, $estante))->toBeTrue();
});

test('estante sem prateleiras pode ser excluída', function () {
    concederPermissao(Permissao::EstanteDelete->value);

    $estante = Estante::factory()->create();

    expect((new EstantePolicy())->delete($this->usuario, $estante))->toBeTrue();
});
