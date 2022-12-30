<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Cargo\CargoOnlyResource;
use App\Http\Resources\Funcao\FuncaoOnlyResource;
use App\Http\Resources\Perfil\PerfilOnlyResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Usuario;

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $usuario = Usuario::factory()->create();

    $resource = UsuarioOnlyResource::make($usuario);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => usuarioApi($usuario),
    ]);
});

test('retorna o relacionamento se houver o eager load da propriedade', function () {
    $usuario = Usuario::factory()->completo()->create();
    $resource = UsuarioOnlyResource::make($usuario->load(['cargo', 'lotacao', 'funcaoConfianca', 'perfil', 'delegante', 'perfilAntigo']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => usuarioApi($usuario)
            + ['lotacao' => lotacaoApi($usuario->lotacao)]
            + ['cargo' => CargoOnlyResource::make($usuario->cargo)->resolve()]
            + ['funcao' => FuncaoOnlyResource::make($usuario->funcaoConfianca)->resolve()]
            + ['perfil' => PerfilOnlyResource::make($usuario->perfil)->resolve()]
            + ['delegante' => UsuarioOnlyResource::make($usuario->delegante)->resolve()]
            + ['perfil_antigo' => PerfilOnlyResource::make($usuario->perfilAntigo)->resolve()],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(UsuarioOnlyResource::make(null)->resolve())->toBeEmpty();
});
