<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Log\LogResource;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->filename = 'arqjud.log';

    $this->storage = Storage::fake('log-aplicacao');

    $this->storage->put($this->filename, 'Contents');
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas', function () {
    concederPermissao([Permissao::LOG_VIEW, Permissao::LOG_DELETE]);

    $resource = LogResource::make(File::allFiles($this->storage->path(''))[0]);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => [
            'nome' => $this->filename,
            'links' => [
                'view' => route('administracao.log.show', $this->filename),
                'download' => route('administracao.log.download', $this->filename),
                'delete' => route('administracao.log.destroy', $this->filename),
            ],
        ],
    ]);
});

test('retorna apenas os campos principais se não houver rota autorizada para o modelo', function () {
    $resource = LogResource::make(File::allFiles($this->storage->path(''))[0]);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => [
            'nome' => $this->filename,
            'links' => [],
        ],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(LogResource::make(null)->resolve())->toBeEmpty();
});
