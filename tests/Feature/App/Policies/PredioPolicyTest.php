<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permissao;
use App\Enums\Policy;
use App\Models\Andar;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = login();
});

afterEach(function () {
    logout();
});

// Proibido
test('usuário sem permissão não pode listar os prédios', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Predio::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um prédio', function () {
    expect(Auth::user()->can(Policy::View->value, Predio::class))->toBeFalse();
});

test('usuário sem permissão não pode criar um prédio', function () {
    expect(Auth::user()->can(Policy::Create->value, Predio::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um prédio', function () {
    expect(Auth::user()->can(Policy::Update->value, Predio::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um prédio', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Predio::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir um prédio', function () {
    $predio = Predio::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $predio))->toBeFalse();
});

test('prédio com andares não pode ser excluído, independente de permissão', function () {
    concederPermissao(Permissao::PREDIO_DELETE);

    $predio = Predio::factory()->has(Andar::factory(2), 'andares')->create();

    expect(Auth::user()->can(Policy::Delete->value, $predio))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os prédios', function () {
    concederPermissao(Permissao::PREDIO_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Predio::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um prédio', function () {
    concederPermissao(Permissao::PREDIO_VIEW);

    expect(Auth::user()->can(Policy::View->value, Predio::class))->toBeTrue();
});

test('usuário com permissão pode criar um prédio', function () {
    concederPermissao(Permissao::PREDIO_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Predio::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um prédio', function () {
    concederPermissao(Permissao::PREDIO_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Predio::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um prédio por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PREDIO_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Predio::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um prédio por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PREDIO_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Predio::class))->toBeTrue();
});

test('usuário com permissão pode excluir um prédio sem andares', function () {
    concederPermissao(Permissao::PREDIO_DELETE);

    $predio = Predio::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $predio))->toBeTrue();
});
