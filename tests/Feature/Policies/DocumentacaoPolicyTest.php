<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Policies\DocumentacaoPolicy;
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
test('usuário sem permissão não pode listar adocumentação da aplicação', function () {
    expect((new DocumentacaoPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente adocumentação da aplicação', function () {
    expect((new DocumentacaoPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criardocumentação da aplicação', function () {
    expect((new DocumentacaoPolicy())->create($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar umadocumentação da aplicação', function () {
    expect((new DocumentacaoPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar umadocumentação da aplicação', function () {
    expect((new DocumentacaoPolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode excluir umadocumentação da aplicação', function () {
    expect((new DocumentacaoPolicy())->delete($this->usuario))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar adocumentação da aplicação', function () {
    concederPermissao(Permissao::DocumentacaoViewAny->value);

    expect((new DocumentacaoPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar umadocumentação da aplicação', function () {
    concederPermissao(Permissao::DocumentacaoView->value);

    expect((new DocumentacaoPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criardocumentação da aplicação', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    expect((new DocumentacaoPolicy())->create($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar umadocumentação da aplicação', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    expect((new DocumentacaoPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar umadocumentação da aplicação por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::DocumentacaoView->value);

    expect((new DocumentacaoPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar umadocumentação da aplicação por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    expect((new DocumentacaoPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir umadocumentação da aplicação', function () {
    concederPermissao(Permissao::DocumentacaoDelete->value);

    expect((new DocumentacaoPolicy())->delete($this->usuario))->toBeTrue();
});
