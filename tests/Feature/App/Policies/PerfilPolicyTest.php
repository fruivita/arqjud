<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Perfil;
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
test('usuário sem permissão não pode listar os perfis', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Perfil::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um perfil', function () {
    expect(Auth::user()->can(Policy::View->value, Perfil::class))->toBeFalse();
});

test('usuário sem permissão não pode criar um perfil', function () {
    expect(Auth::user()->can(Policy::Create->value, Perfil::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um perfil', function () {
    expect(Auth::user()->can(Policy::Update->value, Perfil::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um perfil', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Perfil::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir um perfil', function () {
    $perfil = Perfil::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $perfil))->toBeFalse();
});

test('perfil Administrador e Padrão não pode ser excluído, mesmo se o usuário tiver permissão', function (string $slug) {
    concederPermissao(Permissao::PERFIL_DELETE);

    $perfil = Perfil::firstWhere('slug', $slug);

    expect(Auth::user()->can(Policy::Delete->value, $perfil))->toBeFalse();
})->with([
    Perfil::ADMINISTRADOR,
    Perfil::PADRAO,
]);

test('perfil com usuários não pode ser excluído, mesmo se o usuário tiver permissão', function () {
    concederPermissao(Permissao::PERFIL_DELETE);

    $perfil = Perfil::factory()->hasUsuarios()->create();

    expect(Auth::user()->can(Policy::Delete->value, $perfil))->toBeFalse();
});

test('perfil com delegados não pode ser excluído, mesmo se o usuário tiver permissão', function () {
    concederPermissao(Permissao::PERFIL_DELETE);

    $perfil = Perfil::factory()->hasDelegados()->create();

    expect(Auth::user()->can(Policy::Delete->value, $perfil))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os perfis', function () {
    concederPermissao(Permissao::PERFIL_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Perfil::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um perfil', function () {
    concederPermissao(Permissao::PERFIL_VIEW);

    expect(Auth::user()->can(Policy::View->value, Perfil::class))->toBeTrue();
});

test('usuário com permissão pode criar um perfil', function () {
    concederPermissao(Permissao::PERFIL_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Perfil::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um perfil', function () {
    concederPermissao(Permissao::PERFIL_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Perfil::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um perfil por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PERFIL_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Perfil::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um perfil por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PERFIL_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Perfil::class))->toBeTrue();
});

test('usuário com permissão pode excluir um perfil sem usuários ou delegados', function () {
    concederPermissao(Permissao::PERFIL_DELETE);

    $perfil = Perfil::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $perfil))->toBeTrue();
});
