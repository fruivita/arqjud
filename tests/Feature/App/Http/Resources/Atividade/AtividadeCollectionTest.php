<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Atividade\AtividadeCollection;
use App\Http\Resources\Atividade\AtividadeResource;
use App\Models\Atividade;

beforeEach(function () {
    activity('foo')->log('bar');
    activity('loren')->log('ipson');

    $this->atividades = Atividade::all();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    $resource = AtividadeCollection::make($this->atividades);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->atividades->count());
});

test('collection resolve o resource correto', function () {
    $resource = AtividadeCollection::make($this->atividades);

    expect($resource->collects)->toBe(AtividadeResource::class);
});
