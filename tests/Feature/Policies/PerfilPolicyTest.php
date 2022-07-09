<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Policies\PerfilPolicy;
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
test('usuário sem permissão não pode listar os perfis', function () {
    expect((new PerfilPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente um perfil', function () {
    expect((new PerfilPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um perfil', function () {
    expect((new PerfilPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um perfil', function () {
    expect((new PerfilPolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os perfis', function () {
    concederPermissao(Permissao::PerfilViewAny->value);

    expect((new PerfilPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar um perfil', function () {
    concederPermissao(Permissao::PerfilView->value);

    expect((new PerfilPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar um perfil', function () {
    concederPermissao(Permissao::PerfilUpdate->value);

    expect((new PerfilPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar um perfil por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PerfilView->value);

    expect((new PerfilPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar um perfil por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PerfilUpdate->value);

    expect((new PerfilPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});
