<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permissao;
use App\Models\Usuario;
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
test('usuário sem permissão não pode listar os usuários', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Usuario::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um usuário', function () {
    expect(Auth::user()->can(Policy::View->value, Usuario::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um usuário', function () {
    expect(Auth::user()->can(Policy::Update->value, $this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um usuário', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, $this->usuario))->toBeFalse();
});

test('usuário sem perfil não pode atualizar um usuário', function () {
    $this->usuario->perfil_id = null;
    $this->usuario->save();

    expect(Auth::user()->can(Policy::Update->value, $this->usuario))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os usuários', function () {
    concederPermissao(Permissao::USUARIO_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Usuario::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um usuário', function () {
    concederPermissao(Permissao::USUARIO_VIEW);

    expect(Auth::user()->can(Policy::View->value, Usuario::class))->toBeTrue();
});

test('usuário com permissão e perfil pode atualizar um usuário', function () {
    concederPermissao(Permissao::USUARIO_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, $this->usuario))->toBeTrue();
});

test('usuário com permissão pode visualizar um usuário por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::USUARIO_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, $this->usuario))->toBeTrue();
});

test('usuário com permissão e perfil pode atualizar um usuário por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::USUARIO_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, $this->usuario))->toBeTrue();
});
