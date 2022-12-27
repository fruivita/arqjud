<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Atendimento\SolicitacaoController;
use App\Http\Requests\Atendimento\StoreSolicitacaoRequest;
use App\Jobs\NotificarSolicitanteSolicitacao;
use App\Models\Lotacao;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Bus;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = login();

    $this->solicitante = Usuario::factory()->create();
    $this->destino = Lotacao::factory()->create();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma solicitação', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create();

    expect(Solicitacao::where('id', $solicitacao->id)->exists())->toBeTrue();

    delete(route('atendimento.solicitacao.destroy', $solicitacao))->assertForbidden();

    expect(Solicitacao::where('id', $solicitacao->id)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de solicitação de processo', function () {
    get(route('atendimento.solicitacao.create'))->assertForbidden();
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
    Solicitacao::factory()->solicitada()->create();
    Solicitacao::factory(2)->entregue()->create();
    Solicitacao::factory(4)->devolvida()->create();

    concederPermissao([Permissao::SOLICITACAO_VIEW_ANY, Permissao::SOLICITACAO_CREATE]);

    get(route('atendimento.solicitacao.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Atendimento/Solicitacao/Index')
                ->has('solicitacoes.data', 7)
                ->has('solicitacoes.meta.termo')
                ->has('solicitacoes.meta.order')
                ->where('solicitacoes.meta.count', [
                    'solicitadas' => 1,
                    'entregues' => 2,
                    'devolvidas' => 4,
                ])
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::SOLICITACAO_CREATE);

    get(route('atendimento.solicitacao.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Atendimento/Solicitacao/Create')
                ->where('links', [
                    'solicitante' => route('api.solicitacao.solicitante.show'),
                    'processo' => route('api.solicitacao.processo.show'),
                    'store' => route('atendimento.solicitacao.store'),
                ])
        );
});

test('cria uma nova solicitação de processos status solicitada destinada à lotação do usuário informado', function () {
    testTime()->freeze();
    $processo_1 = Processo::factory()->create();
    $processo_2 = Processo::factory()->create();
    $processo_3 = Processo::factory()->create();

    concederPermissao(Permissao::SOLICITACAO_CREATE);

    $this->assertDatabaseCount('solicitacoes', 0);

    post(route('atendimento.solicitacao.store'), [
        'processos' => [['numero' => $processo_1->numero], ['numero' => $processo_3->numero]],
        'solicitante_id' => $this->solicitante->id,
        'destino_id' => $this->destino->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $this
        ->assertDatabaseCount('solicitacoes', 2)
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $processo_1->id,
            'solicitante_id' => $this->solicitante->id,
            'recebedor_id' => null,
            'remetente_id' => null,
            'rearquivador_id' => null,
            'lotacao_destinataria_id' => $this->destino->id,
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
            'solicitante_id' => $this->solicitante->id,
            'recebedor_id' => null,
            'remetente_id' => null,
            'rearquivador_id' => null,
            'lotacao_destinataria_id' => $this->destino->id,
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

test('dispara o job NotificarSolicitanteSolicitacao quando o usuário faz a solicitação de processos', function () {
    Bus::fake();

    $processos = Processo::factory(3)->create();

    concederPermissao(Permissao::SOLICITACAO_CREATE);

    post(route('atendimento.solicitacao.store'), [
        'processos' => $processos->map(fn ($processo) => $processo->only('numero')),
        'solicitante_id' => $this->solicitante->id,
        'destino_id' => $this->destino->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    Bus::assertNotDispatchedSync(NotificarSolicitanteSolicitacao::class);
    Bus::assertDispatchedTimes(NotificarSolicitanteSolicitacao::class, 1);
});

test('exclui a solicitação informada', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create();

    concederPermissao(Permissao::SOLICITACAO_DELETE);

    expect(Solicitacao::where('id', $solicitacao->id)->exists())->toBeTrue();

    delete(route('atendimento.solicitacao.destroy', $solicitacao))
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
