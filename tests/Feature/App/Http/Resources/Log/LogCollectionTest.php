<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Log\LogCollection;
use App\Http\Resources\Log\LogResource;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    login();

    $this->filenames = ['arqjud.log', 'arqjud-2020-12-30.log'];

    $this->storage = Storage::fake('log-aplicacao');

    Arr::map($this->filenames, function ($filename) {
        $this->storage->put($filename, 'Contents');
    });
});

afterEach(fn () => logout());

// Caminho feliz
test('retorna os campos principais', function () {
    $resource = LogCollection::make(File::allFiles($this->storage->path('')));

    $dados = $resource->response()->getData(true);

    expect($dados['data'])->toHaveCount(count($this->filenames));
});

test('collection resolve o resource correto', function () {
    $resource = LogCollection::make(File::allFiles($this->storage->path('')));

    expect($resource->collects)->toBe(LogResource::class);
});
