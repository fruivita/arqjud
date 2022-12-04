<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Estante;
use App\Models\Permissao;
use App\Models\Sala;
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
test('usuário sem permissão não pode listar as salas', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Sala::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar uma sala', function () {
    expect(Auth::user()->can(Policy::View->value, Sala::class))->toBeFalse();
});

test('usuário sem permissão não pode criar uma sala', function () {
    expect(Auth::user()->can(Policy::Create->value, Sala::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma sala', function () {
    expect(Auth::user()->can(Policy::Update->value, Sala::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma sala', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Sala::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma sala', function () {
    $sala = Sala::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $sala))->toBeFalse();
});

test('sala com estantes não pode ser excluída, independente de permissão', function () {
    concederPermissao(Permissao::SALA_DELETE);

    $sala = Sala::factory()->hasEstantes(2)->create();

    expect(Auth::user()->can(Policy::Delete->value, $sala))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as salas', function () {
    concederPermissao(Permissao::SALA_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Sala::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma sala', function () {
    concederPermissao(Permissao::SALA_VIEW);

    expect(Auth::user()->can(Policy::View->value, Sala::class))->toBeTrue();
});

test('usuário com permissão pode criar uma sala', function () {
    concederPermissao(Permissao::SALA_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Sala::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma sala', function () {
    concederPermissao(Permissao::SALA_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Sala::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma sala por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::SALA_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Sala::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma sala por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::SALA_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Sala::class))->toBeTrue();
});

test('usuário com permissão pode excluir uma sala sem estantes', function () {
    concederPermissao(Permissao::SALA_DELETE);

    $sala = Sala::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $sala))->toBeTrue();
});
