<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Perfil;
use App\Models\Usuario;
use App\Policies\UsuarioPolicy;
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
test('usuário sem permissão não pode listar os usuários', function () {
    expect((new UsuarioPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um usuário', function () {
    expect((new UsuarioPolicy())->update($this->usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um usuários', function () {
    expect((new UsuarioPolicy())->viewAnyOrUpdate($this->usuario))->toBeFalse();
});

test('não pode atualizar o perfil de usuário com perfil maior', function () {
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    concederPermissao(Permissao::UsuarioUpdate->value);

    $admin = Usuario::factory()->create([
        'perfil_id' => Perfil::ADMINISTRADOR,
    ]);

    expect((new UsuarioPolicy())->update($this->usuario, $admin))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os usuários', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);

    expect((new UsuarioPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar um usuário', function () {
    concederPermissao(Permissao::UsuarioUpdate->value);

    expect((new UsuarioPolicy())->update($this->usuario))->toBeTrue();
});

test('usuário com permissão pode visualizar usuários por meio da policy viewAnyOrUpdate', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);

    expect((new UsuarioPolicy())->viewAnyOrUpdate($this->usuario))->toBeTrue();
});

test('usuário com permissão pode atualizar usuário por meio da policy viewAnyOrUpdate', function () {
    concederPermissao(Permissao::UsuarioViewAny->value);

    expect((new UsuarioPolicy())->viewAnyOrUpdate($this->usuario))->toBeTrue();
});

test('usuário pode atualizar o perfil de usuário de mesmo perfil', function () {
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    concederPermissao(Permissao::UsuarioUpdate->value);

    $usuario_2 = Usuario::factory()->create([
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
    ]);

    expect((new UsuarioPolicy())->update($this->usuario, $usuario_2))->toBeTrue();
});

test('usuário pode atualizar o perfil de usuário de perfil inferior', function () {
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    concederPermissao(Permissao::UsuarioUpdate->value);

    $usuario_2 = Usuario::factory()->create([
        'perfil_id' => Perfil::OBSERVADOR,
    ]);

    expect((new UsuarioPolicy())->update($this->usuario, $usuario_2))->toBeTrue();
});
