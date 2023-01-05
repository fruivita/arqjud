<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Cargo\CargoOnlyResource;
use App\Http\Resources\Funcao\FuncaoOnlyResource;
use App\Http\Resources\Perfil\PerfilOnlyResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Http\Resources\Usuario\UsuarioResource;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $usuario = Usuario::factory()->create();
    Auth::login($usuario);

    $this->usuario = Usuario::factory()->completo()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::USUARIO_UPDATE]);

    $resource = UsuarioResource::make($this->usuario);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => usuarioApi($this->usuario)
            + [
                'links' => [
                    'view' => route('autorizacao.usuario.edit', $this->usuario),
                    'update' => route('autorizacao.usuario.update', $this->usuario),
                ],
            ],
    ]);
});

test('retorna o relacionamento se houver o eager load da propriedade e sem os links se não houver rota autorizada', function () {
    $resource = UsuarioResource::make($this->usuario->load(['lotacao', 'cargo', 'funcaoConfianca', 'perfil']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => usuarioApi($this->usuario)
            + ['lotacao' => lotacaoApi($this->usuario->lotacao)]
            + ['cargo' => CargoOnlyResource::make($this->usuario->cargo)->resolve()]
            + ['funcao' => FuncaoOnlyResource::make($this->usuario->funcaoConfianca)->resolve()]
            + ['perfil' => PerfilOnlyResource::make($this->usuario->perfil)->resolve()]
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(UsuarioResource::make(null)->resolve())->toBeEmpty();
});
