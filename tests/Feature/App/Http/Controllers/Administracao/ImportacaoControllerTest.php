<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Administracao\ImportacaoController;
use App\Jobs\ImportarDadosRH;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Bus;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue exibir formulário de importação forçada de dados', function () {
    get(route('administracao.importacao.create'))->assertForbidden();
});

test('usuário sem permissão não consegue solicitar a importação forçada de dados', function () {
    post(route('administracao.importacao.store', ['importacoes' => ['rh']]))->assertForbidden();
});

// Caminho feliz
test('action create compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::IMPORTACAO_CREATE);

    get(route('administracao.importacao.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Importacao/Create')
                ->whereAll([
                    'links' => ['store' => route('administracao.importacao.store')],
                    'opcoes' => [['id' => 'rh', 'nome' => 'Dados do RH']],
                ])
        );
});

test('dispara o job ImportarDadosRH quando o usuário solicita a importação forçada de dados', function () {
    Bus::fake();

    concederPermissao(Permissao::IMPORTACAO_CREATE);

    post(route('administracao.importacao.store', ['importacoes' => ['rh']]))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    Bus::assertNotDispatchedSync(ImportarDadosRH::class);
    Bus::assertDispatchedTimes(ImportarDadosRH::class, 1);
});

test('ImportacaoController usa trait', function () {
    expect(
        collect(class_uses(ImportacaoController::class))
            ->has([
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
