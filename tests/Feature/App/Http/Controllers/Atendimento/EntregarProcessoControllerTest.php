<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Atendimento\EntregarProcessoController;
use App\Http\Requests\Atendimento\StoreEntregarProcessoRequest;
use App\Jobs\NotificarEntrega;
use App\Models\Guia;
use App\Models\Permissao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Bus;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue exibir formulário de entrega de processos solicitados', function () {
    get(route('atendimento.entregar-processo.create'))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        EntregarProcessoController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreEntregarProcessoRequest::class],
]);

test('action create compartilha os dados esperados com a view/componente correto', function () {
    testTime()->freeze();
    Guia::factory()->create();
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    testTime()->addMinute();
    $guia = Guia::factory()->create();

    get(route('atendimento.entregar-processo.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Atendimento/EntregarProcesso/Create')
                ->whereAll([
                    'links' => [
                        'solicitacoes' => route('api.solicitacao.recebedor.show'),
                        'imprimir_ultima_guia' => route('atendimento.guia.pdf', $guia->id),
                        'entregar' => route('atendimento.entregar-processo.store'),
                    ],
                ])
        );
});

test('se não houver última guia, retonará null na rota para a impressão da última guia', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    get(route('atendimento.entregar-processo.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->where('links.imprimir_ultima_guia', null)
        );
});

test('entrega de processo muda o status da solicitação de solicitada para entrega', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $recebedor = Usuario::factory()->create();
    $solicitacoes = Solicitacao::factory(2)->solicitada()->create(['lotacao_destinataria_id' => $recebedor->lotacao_id]);
    Solicitacao::factory()->solicitada()->create();

    post(route('atendimento.entregar-processo.store'), [
        'recebedor' => $recebedor->username,
        'por_guia' => true,
        'solicitacoes' => $solicitacoes->pluck('id')->toArray(),
        'email_terceiros' => [],
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Solicitacao::solicitadas()->count())->toBe(1)
        ->and(Solicitacao::entregues()->count())->toBe(2);
});

test('entrega de processo gera a guia de remessa dos processos solicitados', function () {
    testTime()->freeze();

    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $recebedor = Usuario::factory()->create();
    $solicitacoes = Solicitacao::factory(2)->solicitada()->create(['lotacao_destinataria_id' => $recebedor->lotacao_id]);
    Solicitacao::factory()->solicitada()->create();

    expect(Guia::count())->toBe(0);

    post(route('atendimento.entregar-processo.store'), [
        'recebedor' => $recebedor->username,
        'por_guia' => true,
        'solicitacoes' => $solicitacoes->pluck('id')->toArray(),
        'email_terceiros' => [],
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $processos = $solicitacoes->map(function (Solicitacao $solicitacao) {
        $solicitacao->loadMissing(['processo', 'solicitante']);

        return [
            'numero' => apenasNumeros($solicitacao->processo->numero),
            'qtd_volumes' => $solicitacao->processo->qtd_volumes,
            'solicitante' => [
                'username' => $solicitacao->solicitante->username,
                'nome' => $solicitacao->solicitante->nome,
            ],
        ];
    });

    $guia = Guia::first();

    expect(Guia::count())->toBe(1)
        ->and($guia->numero)->toBe(1)
        ->and($guia->ano)->toBe(now()->year)
        ->and($guia->gerada_em->toString())->toBe(now()->toString())
        ->and($guia->remetente)->toMatchArray($this->usuario->only(['nome', 'username']))
        ->and($guia->recebedor)->toMatchArray($recebedor->only(['nome', 'username']))
        ->and($guia->lotacao_destinataria)->toMatchArray($recebedor->lotacao->only(['nome', 'sigla']))
        ->and($guia->processos)->toMatchArray($processos->toArray());
});

test('dispara o job NotificarEntrega quando o usuário faz a entrega dos processos solicitados', function () {
    Bus::fake();

    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $recebedor = Usuario::factory()->create();
    $solicitacoes = Solicitacao::factory(2)->solicitada()->create(['lotacao_destinataria_id' => $recebedor->lotacao_id]);

    post(route('atendimento.entregar-processo.store'), [
        'recebedor' => $recebedor->username,
        'por_guia' => true,
        'solicitacoes' => $solicitacoes->pluck('id')->toArray(),
        'email_terceiros' => [],
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    Bus::assertNotDispatchedSync(NotificarEntrega::class);
    Bus::assertDispatchedTimes(NotificarEntrega::class, 1);
});

test('EntregarProcessoController usa trait', function () {
    expect(
        collect(class_uses(EntregarProcessoController::class))
            ->has([
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
