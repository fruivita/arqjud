<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Lotacao;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    \Spatie\Once\Cache::getInstance()->disable();

    $this->usuario = Usuario::factory()->create();

    Auth::login($this->usuario);
});

afterEach(function () {
    logout();
});

// Proibido
test('usuário sem permissão não pode listar os usuários', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Usuario::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um usuário', function () {
    $usuario = Usuario::factory()->create();

    expect(Auth::user()->can(Policy::View->value, $usuario))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um usuário', function () {
    $usuario = Usuario::factory()
        ->for(Lotacao::factory(['administravel' => true]), 'lotacao')
        ->create();

    expect(Auth::user()->can(Policy::Update->value, $usuario))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um usuário', function () {
    $usuario = Usuario::factory()
        ->for(Lotacao::factory(['administravel' => true]), 'lotacao')
        ->create();

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, $usuario))->toBeFalse();
});

test('usuário sem perfil não pode atualizar um usuário', function () {
    concederPermissao(Permissao::USUARIO_UPDATE);

    $usuario = Usuario::factory()
        ->for(Lotacao::factory(['administravel' => true]), 'lotacao')
        ->create();

    $this->usuario->perfil_id = null;
    $this->usuario->save();

    expect(Auth::user()->can(Policy::Update->value, $usuario))->toBeFalse();
});

test('usuário de lotação não administrável não pode pode ter seu perfil atualizado', function () {
    concederPermissao(Permissao::USUARIO_UPDATE);

    $usuario = Usuario::factory()
        ->for(Lotacao::factory(['administravel' => false]), 'lotacao')
        ->create();

    expect(Auth::user()->can(Policy::Update->value, $usuario))->toBeFalse();
});

test('usuário não pode atualizar a si mesmo', function () {
    concederPermissao(Permissao::USUARIO_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Auth::user()))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os usuários', function () {
    concederPermissao(Permissao::USUARIO_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Usuario::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um usuário', function () {
    concederPermissao(Permissao::USUARIO_VIEW);

    $usuario = Usuario::factory()->create();

    expect(Auth::user()->can(Policy::View->value, $usuario))->toBeTrue();
});

test('usuário com permissão e perfil pode atualizar outro usuário', function () {
    concederPermissao(Permissao::USUARIO_UPDATE);

    $usuario = Usuario::factory()
        ->for(Lotacao::factory(['administravel' => true]), 'lotacao')
        ->create();

    expect(Auth::user()->can(Policy::Update->value, $usuario))->toBeTrue();
});

test('usuário com permissão pode visualizar a si mesmo por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::USUARIO_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Usuario::factory()->create()))->toBeTrue();
});

test('usuário com permissão pode visualizar um usuário por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::USUARIO_VIEW);

    $usuario = Usuario::factory()->create();

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, $usuario))->toBeTrue();
});

test('usuário com permissão e perfil pode atualizar outro usuário por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::USUARIO_UPDATE);

    $usuario = Usuario::factory()
        ->for(Lotacao::factory(['administravel' => true]), 'lotacao')
        ->create();

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, $usuario))->toBeTrue();
});
