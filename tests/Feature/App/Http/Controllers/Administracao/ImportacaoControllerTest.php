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
use App\Pipes\Importacao\Importar;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    post(route('administracao.importacao.store'), ['importacoes' => ['rh']])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    Bus::assertNotDispatchedSync(ImportarDadosRH::class);
    Bus::assertDispatchedTimes(ImportarDadosRH::class, 1);
});

test('registra o log em caso de falha na importação', function () {
    concederPermissao(Permissao::IMPORTACAO_CREATE);

    $this->partialMock(Importar::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive('rh')
        ->andThrow(\Exception::class)
        ->once();

    Log::spy();

    post(route('administracao.importacao.store'), ['importacoes' => ['rh']])
        ->assertRedirect()
        ->assertSessionHas('feedback.erro');

    Log::shouldHaveReceived('critical')
        ->withArgs(fn ($message) => $message === __('Falha ao executar a importação'))
        ->once();
});

test('importação está protegida por transaction', function () {
    concederPermissao(Permissao::IMPORTACAO_CREATE);

    $this->partialMock(Importar::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive('rh')
        ->andThrow(\Exception::class)
        ->once();

    $database = DB::spy();

    post(route('administracao.importacao.store'), ['importacoes' => ['rh']])
        ->assertRedirect()
        ->assertSessionHas('feedback.erro');

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('rollBack')->once();
    $database->shouldNotReceive('commit');
});

test('ImportacaoController usa trait', function () {
    expect(
        collect(class_uses(ImportacaoController::class))
            ->has([
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
