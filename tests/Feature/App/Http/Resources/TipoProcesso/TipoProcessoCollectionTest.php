<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\TipoProcesso\TipoProcessoCollection;
use App\Http\Resources\TipoProcesso\TipoProcessoResource;
use App\Models\TipoProcesso;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    Auth::login(Usuario::factory()->create());

    $this->tipos_processo = TipoProcesso::factory(2)->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::TIPO_PROCESSO_CREATE]);

    $resource = TipoProcessoCollection::make($this->tipos_processo);

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount($this->tipos_processo->count())
        ->and($dados['links'])->toMatchArray(['create' => route('cadastro.tipo-processo.create')]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = TipoProcessoCollection::make($this->tipos_processo);

    $dados = $resource->response()->getData(true);

    expect($dados)
        ->toHaveKey('data')
        ->not->toHaveKey('links');
});

test('collection resolve o resource correto', function () {
    $resource = TipoProcessoCollection::make($this->tipos_processo);

    expect($resource->collects)->toBe(TipoProcessoResource::class);
});
