<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

beforeAll(fn () => \Spatie\Once\Cache::getInstance()->disable());

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    login();

    $this->filenames = ['arqjud.log', 'arqjud-2020-12-30.log', 'foo.log'];

    $this->storage = Storage::fake('log-aplicacao');

    Arr::map($this->filenames, function ($filename) {
        $this->storage->put($filename, 'Contents');
    });
});

afterEach(fn () => logout());

// Not found
test('parâmetro das rotas de log estão protegidos com regex, mesmo que o log exista', function () {
    concederPermissao([Permissao::LOG_VIEW, Permissao::LOG_DELETE]);

    get(route('administracao.log.show', 'foo.log'))->assertNotFound();
    get(route('administracao.log.download', 'foo.log'))->assertNotFound();
    delete(route('administracao.log.download', 'foo.log'))->assertNotFound();
});

// Caminho feliz
test('parâmetro das rotas de log válidos pela regex são permitidos', function () {
    concederPermissao([Permissao::LOG_VIEW, Permissao::LOG_DELETE]);

    get(route('administracao.log.show', 'arqjud-2020-12-30.log'))->assertOk();
    get(route('administracao.log.download', 'arqjud-2020-12-30.log'))->assertOk();
    delete(route('administracao.log.destroy', 'arqjud-2020-12-30.log'))->assertRedirect(route('administracao.log.index'));

    get(route('administracao.log.show', 'arqjud.log'))->assertOk();
    get(route('administracao.log.download', 'arqjud.log'))->assertOk();
    delete(route('administracao.log.destroy', 'arqjud.log'))->assertRedirect(route('administracao.log.index'));
});
