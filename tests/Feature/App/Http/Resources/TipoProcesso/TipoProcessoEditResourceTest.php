<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\TipoProcesso\TipoProcessoEditResource;
use App\Models\TipoProcesso;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    Auth::login(Usuario::factory()->create());

    $this->tipo_processo = TipoProcesso::factory()->create();
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::TIPO_PROCESSO_DELETE, Permissao::TIPO_PROCESSO_VIEW, Permissao::TIPO_PROCESSO_UPDATE]);

    $resource = TipoProcessoEditResource::make($this->tipo_processo);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->tipo_processo->only(['id', 'nome', 'descricao'])
            + [
                'links' => [
                    'view' => route('cadastro.tipo-processo.edit', $this->tipo_processo),
                    'update' => route('cadastro.tipo-processo.update', $this->tipo_processo),
                ],
            ],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = TipoProcessoEditResource::make($this->tipo_processo->loadCount(['caixas']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->tipo_processo->only(['id', 'nome', 'descricao', 'caixas_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = TipoProcessoEditResource::make($this->tipo_processo);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->tipo_processo->only(['id', 'nome', 'descricao'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(TipoProcessoEditResource::make(null)->resolve())->toBeEmpty();
});
