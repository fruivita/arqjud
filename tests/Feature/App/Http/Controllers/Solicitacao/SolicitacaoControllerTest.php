<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Solicitacao\SolicitacaoController;
use App\Http\Requests\Solicitacao\StoreSolicitacaoRequest;
use App\Jobs\NotificarOperadoresSolicitacao;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarOperadores;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = Usuario::factory()->create();

    $this->usuario->loadMissing('lotacao');
    Auth::login($this->usuario);
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma solicitação', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create(['destino_id' => $this->usuario->lotacao_id]);

    expect(Solicitacao::where('id', $solicitacao->id)->exists())->toBeTrue();

    delete(route('solicitacao.destroy', $solicitacao))->assertForbidden();

    expect(Solicitacao::where('id', $solicitacao->id)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de solicitação de processo', function () {
    get(route('solicitacao.create'))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        SolicitacaoController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreSolicitacaoRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Solicitacao::factory()->create();
    Solicitacao::factory(2)->create(['destino_id' => $this->usuario->lotacao_id]);

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_VIEW_ANY);
    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    get(route('solicitacao.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Solicitacao/Index')
                ->has('solicitacoes.data', 2) // visualiza apenas as da própria lotação
                ->has('solicitacoes.meta.termo')
                ->has('solicitacoes.meta.order')
                ->where('solicitacoes.meta.destino', lotacaoApi($this->usuario->lotacao))
                ->where('solicitacoes.meta.count', [
                    'solicitadas' => 0,
                    'entregues' => 0,
                    'devolvidas' => 2,
                ])
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    get(route('solicitacao.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Solicitacao/Create')
                ->where('lotacao.data', lotacaoApi($this->usuario->lotacao))
                ->where('links', [
                    'search' => route('api.solicitacao.processo.show'),
                    'store' => route('solicitacao.store'),
                ])
        );
});

test('cria uma nova solicitação de processos status solicitada destinada à lotação do usuário autenticado, bem como por ele solicitada', function () {
    $processo_1 = Processo::factory()->create();
    $processo_2 = Processo::factory()->create();
    $processo_3 = Processo::factory()->create();

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    $this->assertDatabaseCount('solicitacoes', 0);

    testTime()->freeze();

    post(route('solicitacao.store'), [
        'processos' => [['numero' => $processo_1->numero], ['numero' => $processo_3->numero]],
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $this
        ->assertDatabaseCount('solicitacoes', 2)
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $processo_1->id,
            'solicitante_id' => $this->usuario->id,
            'recebedor_id' => null,
            'remetente_id' => null,
            'rearquivador_id' => null,
            'destino_id' => $this->usuario->lotacao_id,
            'guia_id' => null,
            'solicitada_em' => now(),
            'entregue_em' => null,
            'devolvida_em' => null,
            'por_guia' => false,
            'descricao' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $processo_3->id,
            'solicitante_id' => $this->usuario->id,
            'recebedor_id' => null,
            'remetente_id' => null,
            'rearquivador_id' => null,
            'destino_id' => $this->usuario->lotacao_id,
            'guia_id' => null,
            'solicitada_em' => now(),
            'entregue_em' => null,
            'devolvida_em' => null,
            'por_guia' => false,
            'descricao' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])
        ->assertDatabaseMissing('solicitacoes', [
            'processo_id' => $processo_2->id,
        ]);
});

test('dispara o job NotificarOperadoresSolicitacao quando o usuário faz a solicitação de processos', function () {
    Bus::fake();

    $processos = Processo::factory(3)->create();

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    post(route('solicitacao.store'), [
        'processos' => $processos->map(fn ($processo) => $processo->only('numero')),
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    Bus::assertNotDispatchedSync(NotificarOperadoresSolicitacao::class);
    Bus::assertDispatchedTimes(NotificarOperadoresSolicitacao::class, 1);
});

test('registra o log em caso de falha na solicitação de processos', function () {
    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    $processos = Processo::factory(3)->create();

    $this->partialMock(NotificarOperadores::class)
        ->shouldReceive('handle')
        ->andThrow(\Exception::class)
        ->once();

    Log::spy();

    post(route('solicitacao.store'), [
        'processos' => $processos->map(fn ($processo) => $processo->only('numero')),
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.erro');

    Log::shouldHaveReceived('critical')
        ->withArgs(fn ($message) => $message === __('Falha ao solicitar o processo'))
        ->once();
});

test('solicitação de processo está protegida por transaction', function () {
    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    $processos = Processo::factory(3)->create();

    $this->partialMock(NotificarOperadores::class)
        ->shouldReceive('handle')
        ->andThrow(\Exception::class)
        ->once();

    $database = DB::spy();

    (new SolicitacaoController())->store(new StoreSolicitacaoRequest([
        'processos' => $processos->map(fn ($processo) => ['numero' => apenasNumeros($processo->numero)]),
    ]));

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('rollBack')->once();
    $database->shouldNotReceive('commit');
});

test('exclui a solicitação informada', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create(['destino_id' => $this->usuario->lotacao_id]);

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_DELETE);

    expect(Solicitacao::where('id', $solicitacao->id)->exists())->toBeTrue();

    delete(route('solicitacao.destroy', $solicitacao))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Solicitacao::where('id', $solicitacao->id)->exists())->toBeFalse();
});

test('SolicitacaoController usa trait', function () {
    expect(
        collect(class_uses(SolicitacaoController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
