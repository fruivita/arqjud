<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Guia\GuiaResource;
use App\Models\Guia;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->guia = Guia::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::GUIA_VIEW]);

    $resource = GuiaResource::make($this->guia);

    expect($resource->response()->getData(true))->toBe([
        'data' => guiaApi($this->guia)
            + [
                'links' => [
                    'view' => route('atendimento.guia.show', $this->guia),
                    'pdf' => route('atendimento.guia.pdf', $this->guia),
                ],
            ],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = GuiaResource::make($this->guia);

    expect($resource->response()->getData(true))->toBe([
        'data' => guiaApi($this->guia),
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(GuiaResource::make(null)->resolve())->toBeEmpty();
});
