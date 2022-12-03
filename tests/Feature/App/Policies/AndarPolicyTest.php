<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permissao;
use App\Enums\Policy;
use App\Models\Andar;
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
test('usuário sem permissão não pode listar os andares', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Andar::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um andar', function () {
    expect(Auth::user()->can(Policy::View->value, Andar::class))->toBeFalse();
});

test('usuário sem permissão não pode criar um andar', function () {
    expect(Auth::user()->can(Policy::Create->value, Andar::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um andar', function () {
    expect(Auth::user()->can(Policy::Update->value, Andar::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um andar', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Andar::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir um andar', function () {
    $andar = Andar::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $andar))->toBeFalse();
});

test('andar com salas não pode ser excluído, independente de permissão', function () {
    concederPermissao(Permissao::ANDAR_DELETE);

    $andar = Andar::factory()->has(Sala::factory(2), 'salas')->create();

    expect(Auth::user()->can(Policy::Delete->value, $andar))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os andares', function () {
    concederPermissao(Permissao::ANDAR_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Andar::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um andar', function () {
    concederPermissao(Permissao::ANDAR_VIEW);

    expect(Auth::user()->can(Policy::View->value, Andar::class))->toBeTrue();
});

test('usuário com permissão pode criar um andar', function () {
    concederPermissao(Permissao::ANDAR_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Andar::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um andar', function () {
    concederPermissao(Permissao::ANDAR_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Andar::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um andar por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::ANDAR_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Andar::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um andar por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::ANDAR_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Andar::class))->toBeTrue();
});

test('usuário com permissão pode excluir um andar sem salas', function () {
    concederPermissao(Permissao::ANDAR_DELETE);

    $andar = Andar::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $andar))->toBeTrue();
});
