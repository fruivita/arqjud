<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permissao;
use App\Enums\Policy;
use App\Models\Estante;
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
test('usuário sem permissão não pode listar as estantes', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Estante::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar uma estante', function () {
    expect(Auth::user()->can(Policy::View->value, Estante::class))->toBeFalse();
});

test('usuário sem permissão não pode criar uma estante', function () {
    expect(Auth::user()->can(Policy::Create->value, Estante::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma estante', function () {
    expect(Auth::user()->can(Policy::Update->value, Estante::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma estante', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Estante::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma estante', function () {
    $estante = Estante::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $estante))->toBeFalse();
});

test('estante com prateleiras não pode ser excluída, independente de permissão', function () {
    concederPermissao(Permissao::ESTANTE_DELETE);

    $estante = Estante::factory()->has(Prateleira::factory(2), 'prateleiras')->create();

    expect(Auth::user()->can(Policy::Delete->value, $estante))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as estantes', function () {
    concederPermissao(Permissao::ESTANTE_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Estante::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma estante', function () {
    concederPermissao(Permissao::ESTANTE_VIEW);

    expect(Auth::user()->can(Policy::View->value, Estante::class))->toBeTrue();
});

test('usuário com permissão pode criar uma estante', function () {
    concederPermissao(Permissao::ESTANTE_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Estante::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma estante', function () {
    concederPermissao(Permissao::ESTANTE_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Estante::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma estante por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::ESTANTE_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Estante::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma estante por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::ESTANTE_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Estante::class))->toBeTrue();
});

test('usuário com permissão pode excluir uma estante sem prateleiras', function () {
    concederPermissao(Permissao::ESTANTE_DELETE);

    $estante = Estante::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $estante))->toBeTrue();
});
