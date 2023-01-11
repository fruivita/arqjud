<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Caixa;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = login();
});

afterEach(fn () => logout());

// Proibido
test('usuário sem permissão não pode listar as caixas', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Caixa::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar uma caixa', function () {
    expect(Auth::user()->can(Policy::View->value, Caixa::class))->toBeFalse();
});

test('usuário sem permissão não pode criar uma caixa', function () {
    expect(Auth::user()->can(Policy::Create->value, Caixa::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma caixa', function () {
    expect(Auth::user()->can(Policy::Update->value, Caixa::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma caixa', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Caixa::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma caixa', function () {
    $caixa = Caixa::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $caixa))->toBeFalse();
});

test('caixa com volumes não pode ser excluída, independente de permissão', function () {
    concederPermissao(Permissao::CAIXA_DELETE);

    $caixa = Caixa::factory()->hasVolumes(2)->create();

    expect(Auth::user()->can(Policy::Delete->value, $caixa))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as caixas', function () {
    concederPermissao(Permissao::CAIXA_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Caixa::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma caixa', function () {
    concederPermissao(Permissao::CAIXA_VIEW);

    expect(Auth::user()->can(Policy::View->value, Caixa::class))->toBeTrue();
});

test('usuário com permissão pode criar uma caixa', function () {
    concederPermissao(Permissao::CAIXA_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Caixa::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma caixa', function () {
    concederPermissao(Permissao::CAIXA_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Caixa::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma caixa por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::CAIXA_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Caixa::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma caixa por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::CAIXA_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Caixa::class))->toBeTrue();
});

test('usuário com permissão pode excluir uma caixa sem volumes', function () {
    concederPermissao(Permissao::CAIXA_DELETE);

    $caixa = Caixa::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $caixa))->toBeTrue();
});
