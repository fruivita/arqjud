<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Perfil\PerfilEditResource;
use App\Http\Resources\Permissao\PermissaoOnlyResource;
use App\Models\Perfil;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->perfil = Perfil::factory()->create();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::PERFIL_DELETE, Permissao::PERFIL_VIEW, Permissao::PERFIL_UPDATE]);

    $resource = PerfilEditResource::make($this->perfil);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->perfil->only(['id', 'nome', 'slug', 'poder', 'descricao'])
            + [
                'links' => [
                    'view' => route('administracao.perfil.edit', $this->perfil),
                    'update' => route('administracao.perfil.update', $this->perfil),
                ],
            ],
    ]);
});

test('retorna os relacionamentos se houver o eager load da propriedade', function () {
    $permissoes = Permissao::factory(2)->create();
    $this->perfil->permissoes()->attach($permissoes->pluck('id'));
    $resource = PerfilEditResource::make($this->perfil->load('permissoes'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->perfil->only(['id', 'nome', 'slug', 'poder', 'descricao'])
            + ['permissoes' => data_get(PermissaoOnlyResource::collection($permissoes)->response()->getData(true), 'data')]
            + ['links' => []],
    ]);
});

test('retorna a quantidade de filhos se houver o eager load da propriedade', function () {
    $resource = PerfilEditResource::make($this->perfil->loadCount(['usuarios', 'delegados']));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->perfil->only(['id', 'nome', 'slug', 'poder', 'descricao', 'usuarios_count', 'delegados_count'])
            + ['links' => []],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = PerfilEditResource::make($this->perfil);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => $this->perfil->only(['id', 'nome', 'slug', 'poder', 'descricao'])
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(PerfilEditResource::make(null)->resolve())->toBeEmpty();
});
