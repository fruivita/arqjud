<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Cargo\CargoOnlyResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Usuario;

beforeEach(function () {
    $this->usuario = Usuario::factory()->comCargo()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = UsuarioOnlyResource::make($this->usuario);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => usuarioApi($this->usuario),
    ]);
});

test('retorna o cargo e a lotação pai se houver o eager load da propriedade', function () {
    $resource = UsuarioOnlyResource::make($this->usuario->load('cargo', 'lotacao'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => usuarioApi($this->usuario)
            + ['lotacao' => lotacaoApi($this->usuario->lotacao)]
            + ['cargo' => CargoOnlyResource::make($this->usuario->cargo)->resolve()],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(UsuarioOnlyResource::make(null)->resolve())->toBeEmpty();
});
