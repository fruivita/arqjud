<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Caixa;
use App\Models\Prateleira;
use App\Policies\PrateleiraPolicy;
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
test('usuário sem permissão não pode listar as prateleiras', function () {
    expect((new PrateleiraPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma prateleira', function () {
    expect((new PrateleiraPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criar uma prateleira', function () {
    expect((new PrateleiraPolicy())->create($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma prateleira', function () {
    expect((new PrateleiraPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma prateleira', function () {
    expect((new PrateleiraPolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma prateleira', function () {
    $prateleira = Prateleira::factory()->create();

    expect((new PrateleiraPolicy())->delete($this->usuario, $prateleira))->toBeFalse();
});

test('prateleira sem caixas não pode ser excluida', function () {
    concederPermissao(Permissao::PrateleiraDelete->value);

    $prateleira = Prateleira::factory()
    ->has(Caixa::factory(2), 'caixas')
    ->create();

    expect((new PrateleiraPolicy())->delete($this->usuario, $prateleira))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as prateleiras', function () {
    concederPermissao(Permissao::PrateleiraViewAny->value);

    expect((new PrateleiraPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma prateleira', function () {
    concederPermissao(Permissao::PrateleiraView->value);

    expect((new PrateleiraPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criar uma prateleira', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    expect((new PrateleiraPolicy())->create($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma prateleira', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    expect((new PrateleiraPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma prateleira por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PrateleiraView->value);

    expect((new PrateleiraPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma prateleira por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    expect((new PrateleiraPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir uma prateleira', function () {
    concederPermissao(Permissao::PrateleiraDelete->value);

    $prateleira = Prateleira::factory()->create();

    expect((new PrateleiraPolicy())->delete($this->usuario, $prateleira))->toBeTrue();
});

test('prateleira sem caixas pode ser excluída', function () {
    concederPermissao(Permissao::PrateleiraDelete->value);

    $prateleira = Prateleira::factory()->create();

    expect((new PrateleiraPolicy())->delete($this->usuario, $prateleira))->toBeTrue();
});
