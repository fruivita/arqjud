<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Policies\VolumeCaixaPolicy;
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
test('usuário sem permissão não pode listar os volumes de caixa', function () {
    expect((new VolumeCaixaPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente um volume de caixa', function () {
    expect((new VolumeCaixaPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criar um volume de caixa', function () {
    expect((new VolumeCaixaPolicy())->create($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um volume de caixa', function () {
    expect((new VolumeCaixaPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode excluir um volume de caixa', function () {
    expect((new VolumeCaixaPolicy())->delete($this->usuario))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os volumes de caixa', function () {
    concederPermissao(Permissao::VolumeCaixaViewAny->value);

    expect((new VolumeCaixaPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar um volume de caixa', function () {
    concederPermissao(Permissao::VolumeCaixaView->value);

    expect((new VolumeCaixaPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criar um volume de caixa', function () {
    concederPermissao(Permissao::VolumeCaixaCreate->value);

    expect((new VolumeCaixaPolicy())->create($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar um volume de caixa', function () {
    concederPermissao(Permissao::VolumeCaixaUpdate->value);

    expect((new VolumeCaixaPolicy())->update($this->usuario))->toBeTrue();
});
