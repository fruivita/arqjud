<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Andar;
use App\Models\Predio;
use App\Policies\PredioPolicy;
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
test('usuário sem permissão não pode listar os prédios', function () {
    expect((new PredioPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente um prédio', function () {
    expect((new PredioPolicy())->view($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode criar um prédio', function () {
    expect((new PredioPolicy())->create($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um prédio', function () {
    expect((new PredioPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um prédio', function () {
    expect((new PredioPolicy())->viewOrUpdate($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode excluir um prédio', function () {
    $predio = Predio::factory()->create();

    expect((new PredioPolicy())->delete($this->usuario, $predio))->toBeFalse();
});

test('predio com andares não pode ser excluida', function () {
    concederPermissao(Permissao::PredioDelete->value);

    $predio = Predio::factory()->has(Andar::factory(2), 'andares')->create();

    expect((new PredioPolicy())->delete($this->usuario, $predio))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os prédios', function () {
    concederPermissao(Permissao::PredioViewAny->value);

    expect((new PredioPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar um prédio', function () {
    concederPermissao(Permissao::PredioView->value);

    expect((new PredioPolicy())->view($this->usuario))->toBeTrue();
});

test('usuário com permissão pode criar um prédio', function () {
    concederPermissao(Permissao::PredioCreate->value);

    expect((new PredioPolicy())->create($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar um prédio', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    expect((new PredioPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar um prédio por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PredioView->value);

    expect((new PredioPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar um a prédio por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PredioUpdate->value);

    expect((new PredioPolicy())->viewOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir um prédio', function () {
    concederPermissao(Permissao::PredioDelete->value);

    $predio = Predio::factory()->create();

    expect((new PredioPolicy())->delete($this->usuario, $predio))->toBeTrue();
});

test('prédio sem andares pode ser excluído', function () {
    concederPermissao(Permissao::PredioDelete->value);

    $predio = Predio::factory()->create();

    expect((new PredioPolicy())->delete($this->usuario, $predio))->toBeTrue();
});
