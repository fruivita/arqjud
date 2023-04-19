<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Atendimento\NotificarSolicianteController;
use App\Http\Requests\Atendimento\StoreNotificarSolicitanteRequest;
use App\Jobs\NotificarSolicitanteProcessoDisponivel;
use App\Models\Permissao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarDisponibilizacao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = Usuario::factory()->create();
    Auth::login($this->usuario);
});

afterEach(fn () => Auth::logout());

// Autorização
test('usuário sem permissão não consegue exibir formulário de notificação de processo disponível', function () {
    get(route('atendimento.notificar-solicitante.create'))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        NotificarSolicianteController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreNotificarSolicitanteRequest::class],
]);

test('action create compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    get(route('atendimento.notificar-solicitante.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Atendimento/NotificarSolicitante/Create')
                ->whereAll([
                    'links' => [
                        'notificar' => route('atendimento.notificar-solicitante.create'),
                    ],
                ])
        );
});

test('registra a data e hora que o usuário foi notificado', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $sol_01 = Solicitacao::factory()->solicitada()->create();
    $sol_02 = Solicitacao::factory()->solicitada()->create();

    testTime()->freeze();

    post(route('atendimento.notificar-solicitante.store'), [
        'numero' => $sol_01->processo->numero,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $sol_01->refresh();
    $sol_02->refresh();

    expect($sol_01->notificado_em->toString())->toBe(now()->toString())
        ->and($sol_02->notificado_em)->toBeNull();
});

test('dispara o job NotificarSolicitanteProcessoDisponivel quando o usuário solicita a notificação do solicitante', function () {
    Bus::fake();

    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $solicitacao = Solicitacao::factory()->solicitada()->create();

    post(route('atendimento.notificar-solicitante.store'), [
        'numero' => $solicitacao->processo->numero,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    Bus::assertNotDispatchedSync(NotificarSolicitanteProcessoDisponivel::class);
    Bus::assertDispatchedTimes(NotificarSolicitanteProcessoDisponivel::class, 1);
});

test('registra o log em caso de falha na notificação do usuário', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $solicitacao = Solicitacao::factory()->solicitada()->create();

    $this->partialMock(NotificarDisponibilizacao::class)
        ->shouldReceive('handle')
        ->andThrow(\Exception::class)
        ->once();

    Log::spy();

    post(route('atendimento.notificar-solicitante.store'), [
        'numero' => $solicitacao->processo->numero,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.erro');

    Log::shouldHaveReceived('critical')
        ->withArgs(fn ($message) => $message === __('Falha ao notificar a disponibilidade do processo solicitado'))
        ->once();
});

test('notificação de processo disponibilizado está protegida por transaction', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $solicitacao = Solicitacao::factory()->solicitada()->create();

    $this->partialMock(NotificarDisponibilizacao::class)
        ->shouldReceive('handle')
        ->andThrow(\Exception::class)
        ->once();

    $database = DB::spy();

    (new NotificarSolicianteController())->store(new StoreNotificarSolicitanteRequest([
        'numero' => apenasNumeros($solicitacao->processo->numero),
    ]));

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('rollBack')->once();
    $database->shouldNotReceive('commit');
});

test('NotificarSolicianteController usa trait', function () {
    expect(
        collect(class_uses(NotificarSolicianteController::class))
            ->has([
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
