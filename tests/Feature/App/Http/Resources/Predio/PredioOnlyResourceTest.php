<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Predio\PredioOnlyResource;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->predio = Predio::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = PredioOnlyResource::make($this->predio);

    expect($resource->response(request())->getData(true))->toBe([
        'data' => $this->predio->only(['id', 'nome'])
    ]);
});
