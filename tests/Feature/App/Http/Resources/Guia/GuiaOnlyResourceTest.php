<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Guia\GuiaOnlyResource;
use App\Models\Guia;

beforeEach(function () {
    $this->guia = Guia::factory()->create();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = GuiaOnlyResource::make($this->guia);

    expect(data_get($resource->response()->getData(true), 'data'))->toMatchArray(guiaApi($this->guia));
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(GuiaOnlyResource::make(null)->resolve())->toBeEmpty();
});
