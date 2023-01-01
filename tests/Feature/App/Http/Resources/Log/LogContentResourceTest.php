<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Log\LogContentResource;

beforeEach(function () {
    $this->string = 'foo';
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = LogContentResource::make($this->string);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => ['linha' => 'foo'],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(LogContentResource::make(null)->resolve())->toBeEmpty();
});
