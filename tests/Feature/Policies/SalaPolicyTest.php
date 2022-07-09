<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Estante;
use App\Models\Sala;
use App\Policies\SalaPolicy;
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
test('usuário sem permissão não pode listar as salas', function () {
    expect((new SalaPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma sala', function () {
    expect((new SalaPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criar uma sala', function () {
    expect((new SalaPolicy())->create($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma sala', function () {
    expect((new SalaPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma sala', function () {
    expect((new SalaPolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma sala', function () {
    $sala = Sala::factory()->create();

    expect((new SalaPolicy())->delete($this->usuario, $sala))->toBeFalse();
});

test('sala com estantes não pode ser excluida', function () {
    concederPermissao(Permissao::SalaDelete->value);

    $sala = Sala::factory()->has(Estante::factory(2), 'estantes')->create();

    expect((new SalaPolicy())->delete($this->usuario, $sala))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as salas', function () {
    concederPermissao(Permissao::SalaViewAny->value);

    expect((new SalaPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma sala', function () {
    concederPermissao(Permissao::SalaView->value);

    expect((new SalaPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criar uma sala', function () {
    concederPermissao(Permissao::SalaCreate->value);

    expect((new SalaPolicy())->create($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma sala', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    expect((new SalaPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma sala por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::SalaView->value);

    expect((new SalaPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma sala por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    expect((new SalaPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir uma sala', function () {
    concederPermissao(Permissao::SalaDelete->value);

    $sala = Sala::factory()->create();

    expect((new SalaPolicy())->delete($this->usuario, $sala))->toBeTrue();
});

test('sala sem estantes pode ser excluída', function () {
    concederPermissao(Permissao::SalaDelete->value);

    $sala = Sala::factory()->create();

    expect((new SalaPolicy())->delete($this->usuario, $sala))->toBeTrue();
});
