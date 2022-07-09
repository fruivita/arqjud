<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Policies\PermissaoPolicy;
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
test('usuário sem permissão não pode listar as permissões', function () {
    expect((new PermissaoPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma permissão', function () {
    expect((new PermissaoPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma permissão', function () {
    expect((new PermissaoPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma permissão', function () {
    expect((new PermissaoPolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as permissões', function () {
    concederPermissao(Permissao::PermissaoViewAny->value);

    expect((new PermissaoPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma permissão', function () {
    concederPermissao(Permissao::PermissaoView->value);

    expect((new PermissaoPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma permissão', function () {
    concederPermissao(Permissao::PermissaoUpdate->value);

    expect((new PermissaoPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma permissão por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PermissaoView->value);

    expect((new PermissaoPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma permissão por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PermissaoUpdate->value);

    expect((new PermissaoPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});
