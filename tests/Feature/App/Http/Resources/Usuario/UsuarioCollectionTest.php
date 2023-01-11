<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Usuario\UsuarioCollection;
use App\Http\Resources\Usuario\UsuarioResource;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $usuario = Usuario::factory()->create();
    Auth::login($usuario);

    $this->usuarios = Usuario::factory(2)->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    concederPermissao([Permissao::USUARIO_UPDATE]);

    $resource = UsuarioCollection::make($this->usuarios);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->usuarios->count());
});

test('collection resolve o resource correto', function () {
    $resource = UsuarioCollection::make($this->usuarios);

    expect($resource->collects)->toBe(UsuarioResource::class);
});
