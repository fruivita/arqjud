<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permissao;
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
test('usuário sem permissão não pode listar as permissões', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Permissao::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar uma permissão', function () {
    expect(Auth::user()->can(Policy::View->value, Permissao::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma permissão', function () {
    expect(Auth::user()->can(Policy::Update->value, Permissao::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma permissão', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Permissao::class))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as permissões', function () {
    concederPermissao(Permissao::PERMISSAO_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Permissao::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma permissão', function () {
    concederPermissao(Permissao::PERMISSAO_VIEW);

    expect(Auth::user()->can(Policy::View->value, Permissao::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma permissão', function () {
    concederPermissao(Permissao::PERMISSAO_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Permissao::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma permissão por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PERMISSAO_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Permissao::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma permissão por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PERMISSAO_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Permissao::class))->toBeTrue();
});
