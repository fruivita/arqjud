<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Policies\LogPolicy;
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
test('usuário sem permissão não pode listar os logs da aplicação', function () {
    expect((new LogPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode excluir um log da aplicação', function () {
    expect((new LogPolicy())->delete($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode fazer o donwload de um log da aplicação', function () {
    expect((new LogPolicy())->download($this->usuario))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os logs da aplicação', function () {
    concederPermissao(Permissao::LogViewAny->value);

    expect((new LogPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir um log da aplicação', function () {
    concederPermissao(Permissao::LogDelete->value);

    expect((new LogPolicy())->delete($this->usuario))->toBeTrue();
});

test('usuário com permissão pode realizar o download individual dos logs da aplicação', function () {
    concederPermissao(Permissao::LogDownload->value);

    expect((new LogPolicy())->download($this->usuario))->toBeTrue();
});
