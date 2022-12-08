<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Processo\ProcessoCollection;
use App\Http\Resources\Processo\ProcessoResource;
use App\Models\Processo;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->processos = Processo::factory(2)->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais do modelo', function () {
    $resource = ProcessoCollection::make($this->processos);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->processos->count());
});

test('collection resolve o resource correto', function () {
    $resource = ProcessoCollection::make($this->processos);

    expect($resource->collects)->toBe(ProcessoResource::class);
});
