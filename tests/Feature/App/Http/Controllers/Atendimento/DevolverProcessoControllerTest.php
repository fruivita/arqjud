<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Atendimento\DevolverProcessoController;
use App\Http\Requests\Atendimento\StoreDevolverProcessoRequest;
use App\Jobs\NotificarSolicitanteDevolucao;
use App\Models\Permissao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = Usuario::factory()->create();
    Auth::login($this->usuario);
});

afterEach(function () {
    Auth::logout();
});

// Autorização
test('usuário sem permissão não consegue exibir formulário de devolução de processos ao arquivo', function () {
    get(route('atendimento.devolver-processo.create'))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        DevolverProcessoController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreDevolverProcessoRequest::class],
]);

test('action create compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    get(route('atendimento.devolver-processo.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Atendimento/DevolverProcesso/Create')
                ->whereAll([
                    'links' => [
                        'devolver' => route('atendimento.devolver-processo.store'),
                    ],
                ])
        );
});

test('devolução de processo muda o status da solicitação de entregue para devolvida', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $solicitacao = Solicitacao::factory()->entregue()->create();
    Solicitacao::factory(2)->solicitada()->create();

    post(route('atendimento.devolver-processo.store'), [
        'numero' => $solicitacao->processo->numero,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Solicitacao::solicitadas()->count())->toBe(2)
        ->and(Solicitacao::entregues()->count())->toBe(0)
        ->and(Solicitacao::devolvidas()->count())->toBe(1);
});

test('dispara o job NotificarSolicitanteDevolucao quando o usuário faz a devolução do processo ao arquivo', function () {
    Bus::fake();

    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $solicitacao = Solicitacao::factory()->entregue()->create();

    post(route('atendimento.devolver-processo.store'), [
        'numero' => $solicitacao->processo->numero,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    Bus::assertNotDispatchedSync(NotificarSolicitanteDevolucao::class);
    Bus::assertDispatchedTimes(NotificarSolicitanteDevolucao::class, 1);
});

test('DevolverProcessoController usa trait', function () {
    expect(
        collect(class_uses(DevolverProcessoController::class))
            ->has([
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
