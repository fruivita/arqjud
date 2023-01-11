<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Administracao\LogController;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

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

// Autorização
test('usuário sem permissão não consegue exibir os logs de funcionamento', function () {
    get(route('administracao.log.index'))->assertForbidden();
});

test('usuário sem permissão não consegue exibir um log de funcionamento', function () {
    get(route('administracao.log.show', $this->filenames[0]))->assertForbidden();
});

test('usuário sem permissão não consegue fazer o download de um log de funcionamento', function () {
    get(route('administracao.log.download', $this->filenames[0]))->assertForbidden();
});

test('usuário sem permissão não consegue excluir um log de funcionamento', function () {
    $this->storage->assertExists($this->filenames);

    delete(route('administracao.log.destroy', $this->filenames[0]))->assertForbidden();

    $this->storage->assertExists($this->filenames);
});

// Caminho feliz
test('action index compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::LOG_VIEW_ANY);

    get(route('administracao.log.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Log/Index')
                ->has('arquivos.data', 2)
        );
});

test('action show compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao([Permissao::LOG_VIEW, Permissao::LOG_DELETE]);

    get(route('administracao.log.show', $this->filenames[0]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Log/Show')
                ->where('conteudo.data', [['linha' => 'Contents']])
                ->where('conteudo.meta.arquivo', $this->filenames[0])
                ->has('conteudo.meta.links', 5)
        );
});

test('action download disponibiliza o download do arquivo de log', function () {
    concederPermissao([Permissao::LOG_VIEW]);

    get(route('administracao.log.download', $this->filenames[0]))
        ->assertDownload($this->filenames[0]);
});

test('action delete exclui o arquivo de log informado', function () {
    concederPermissao([Permissao::LOG_VIEW_ANY, Permissao::LOG_DELETE]);

    $this->storage->assertExists($this->filenames);

    delete(route('administracao.log.destroy', $this->filenames[0]))
        ->assertRedirect(route('administracao.log.index'));

    $this->storage->assertMissing($this->filenames[0]);
    $this->storage->assertExists($this->filenames[1]);
});

test('LogController usa trait', function () {
    expect(
        collect(class_uses(LogController::class))
            ->has([
                \App\Http\Traits\ComFeedback::class,
                \App\Http\Traits\ComPaginacaoEmCache::class,
            ])
    )->toBeTrue();
});
