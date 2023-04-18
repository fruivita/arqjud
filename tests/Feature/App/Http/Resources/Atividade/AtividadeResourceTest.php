<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Atividade\AtividadeResource;
use App\Models\Atividade;

beforeEach(function () {
    activity('foo')->log('bar');

    $this->atividade = Atividade::first();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    $resource = AtividadeResource::make($this->atividade);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => atividadeApi($this->atividade)
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(AtividadeResource::make(null)->resolve())->toBeEmpty();
});
