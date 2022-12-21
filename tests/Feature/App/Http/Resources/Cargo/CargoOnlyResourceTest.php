<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Cargo\CargoOnlyResource;
use App\Models\Cargo;

beforeEach(function () {
    $this->cargo = Cargo::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = CargoOnlyResource::make($this->cargo);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->cargo->only(['id', 'nome']),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(CargoOnlyResource::make(null)->resolve())->toBeEmpty();
});
