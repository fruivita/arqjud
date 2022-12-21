<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Solicitacao\CounterResource;

// Caminho feliz
test('retorna os campos principais do resource', function () {
    $objeto = new stdClass();

    $objeto->solicitadas = 5;
    $objeto->entregues = 10;
    $objeto->devolvidas = 15;

    $resource = CounterResource::make($objeto);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => [
            'solicitadas' => 5,
            'entregues' => 10,
            'devolvidas' => 15,
        ],
    ]);
});

test('retorna as propriedades como null se inexistentes', function () {
    $objeto = new stdClass();

    $resource = CounterResource::make($objeto);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => [
            'solicitadas' => null,
            'entregues' => null,
            'devolvidas' => null,
        ],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(CounterResource::make(null)->resolve())->toBeEmpty();
});
