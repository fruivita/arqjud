<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Policies\ConfiguracaoPolicy;
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
test('usuário sem permissão não pode visualizar individualmente uma configuração', function () {
    expect((new ConfiguracaoPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma configuração', function () {
    expect((new ConfiguracaoPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma configuração', function () {
    expect((new ConfiguracaoPolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode individualmente visualizar uma configuração', function () {
    concederPermissao(Permissao::ConfiguracaoView->value);

    expect((new ConfiguracaoPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma configuração', function () {
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    expect((new ConfiguracaoPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma configuração por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::ConfiguracaoView->value);

    expect((new ConfiguracaoPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma configuração por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::ConfiguracaoUpdate->value);

    expect((new ConfiguracaoPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});
