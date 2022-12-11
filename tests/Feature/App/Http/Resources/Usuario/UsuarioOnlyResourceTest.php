<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Cargo\CargoOnlyResource;
use App\Http\Resources\Lotacao\LotacaoOnlyResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Usuario;

beforeEach(function () {
    $this->usuario = Usuario::factory()->comCargo()->create();

    $this->usuario_api = [
        'id' => $this->usuario->id,
        'matricula' => $this->usuario->matricula,
        'sigla' => $this->usuario->username,
        'nome' => $this->usuario->nome,
        'lotacao_id' => $this->usuario->lotacao_id,
        'cargo_id' => $this->usuario->cargo_id,
    ];
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = UsuarioOnlyResource::make($this->usuario);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->usuario_api,
    ]);
});

test('retorna o cargo e a lotação pai se houver o eager load da propriedade', function () {
    $resource = UsuarioOnlyResource::make($this->usuario->load('cargo', 'lotacao'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->usuario_api
            + ['lotacao' => LotacaoOnlyResource::make($this->usuario->lotacao)->resolve()]
            + ['cargo' => CargoOnlyResource::make($this->usuario->cargo)->resolve()],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(UsuarioOnlyResource::make(null)->resolve())->toBeEmpty();
});
