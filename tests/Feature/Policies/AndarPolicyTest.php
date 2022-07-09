<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Andar;
use App\Models\Sala;
use App\Policies\AndarPolicy;
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
test('usuário sem permissão não pode listar os andares', function () {
    expect((new AndarPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente um andar', function () {
    expect((new AndarPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criar um andar', function () {
    expect((new AndarPolicy())->create($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um andar', function () {
    expect((new AndarPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um andar', function () {
    expect((new AndarPolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode excluir um andar', function () {
    $andar = Andar::factory()->create();

    expect((new AndarPolicy())->delete($this->usuario, $andar))->toBeFalse();
});

test('andar com salas não pode ser excluido', function () {
    concederPermissao(Permissao::AndarDelete->value);

    $andar = Andar::factory()->has(Sala::factory(2), 'salas')->create();

    expect((new AndarPolicy())->delete($this->usuario, $andar))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os andares', function () {
    concederPermissao(Permissao::AndarViewAny->value);

    expect((new AndarPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar um andar', function () {
    concederPermissao(Permissao::AndarView->value);

    expect((new AndarPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criar um andar', function () {
    concederPermissao(Permissao::AndarCreate->value);

    expect((new AndarPolicy())->create($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar um a andar', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    expect((new AndarPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar um andar por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::AndarView->value);

    expect((new AndarPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar um a andar por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    expect((new AndarPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir um andar', function () {
    concederPermissao(Permissao::AndarDelete->value);

    $andar = Andar::factory()->create();

    expect((new AndarPolicy())->delete($this->usuario, $andar))->toBeTrue();
});

test('andar sem salas pode ser excluída', function () {
    concederPermissao(Permissao::AndarDelete->value);

    $andar = Andar::factory()->create();

    expect((new AndarPolicy())->delete($this->usuario, $andar))->toBeTrue();
});
