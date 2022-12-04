<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Caixa;
use App\Models\Permissao;
use App\Models\Prateleira;
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
test('usuário sem permissão não pode listar as prateleiras', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Prateleira::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar uma prateleira', function () {
    expect(Auth::user()->can(Policy::View->value, Prateleira::class))->toBeFalse();
});

test('usuário sem permissão não pode criar uma prateleira', function () {
    expect(Auth::user()->can(Policy::Create->value, Prateleira::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma prateleira', function () {
    expect(Auth::user()->can(Policy::Update->value, Prateleira::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma prateleira', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Prateleira::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma prateleira', function () {
    $prateleira = Prateleira::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $prateleira))->toBeFalse();
});

test('prateleira com caixas não pode ser excluída, independente de permissão', function () {
    concederPermissao(Permissao::PRATELEIRA_DELETE);

    $prateleira = Prateleira::factory()->has(Caixa::factory(2), 'caixas')->create();

    expect(Auth::user()->can(Policy::Delete->value, $prateleira))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as prateleiras', function () {
    concederPermissao(Permissao::PRATELEIRA_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Prateleira::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma prateleira', function () {
    concederPermissao(Permissao::PRATELEIRA_VIEW);

    expect(Auth::user()->can(Policy::View->value, Prateleira::class))->toBeTrue();
});

test('usuário com permissão pode criar uma prateleira', function () {
    concederPermissao(Permissao::PRATELEIRA_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Prateleira::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma prateleira', function () {
    concederPermissao(Permissao::PRATELEIRA_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Prateleira::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma prateleira por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PRATELEIRA_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Prateleira::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma prateleira por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PRATELEIRA_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Prateleira::class))->toBeTrue();
});

test('usuário com permissão pode excluir uma prateleira sem caixas', function () {
    concederPermissao(Permissao::PRATELEIRA_DELETE);

    $prateleira = Prateleira::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $prateleira))->toBeTrue();
});
