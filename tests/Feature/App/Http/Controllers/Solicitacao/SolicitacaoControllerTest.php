<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Solicitacao\SolicitacaoController;
use App\Models\Permissao;
use App\Models\Lotacao;
use App\Models\Solicitacao;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = login();

    $this->lotacao = Lotacao::find($this->usuario->lotacao_id);
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma solicitação', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create(['lotacao_destinataria_id' => $this->usuario->lotacao_id]);

    expect(Solicitacao::where('id', $solicitacao->id)->exists())->toBeTrue();

    delete(route('solicitacao.destroy', $solicitacao))->assertForbidden();

    expect(Solicitacao::where('id', $solicitacao->id)->exists())->toBeTrue();
});

// test('usuário sem permissão não consegue exibir formulário de criação da solicitação', function () {
//     get(route('solicitacao.create'))->assertForbidden();
// });

// Caminho feliz
// test('action do controller usa o form request', function (string $action, string $request) {
//     $this->assertActionUsesFormRequest(
//         SolicitacaoController::class,
//         $action,
//         $request
//     );
// })->with([
//     ['store', StoreRemessaRequest::class],
// ]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Solicitacao::factory(1)->create();
    Solicitacao::factory(2)->create(['lotacao_destinataria_id' => $this->usuario->lotacao_id]);

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
                ->where('solicitacoes.meta.lotacao_destinataria', lotacaoApi($this->usuario->lotacao))
                ->where('solicitacoes.meta.count', [
                    'solicitadas' => 0,
                    'entregues' => 0,
                    'devolvidas' => 2,
                ])
        );
});

// test('action create compartilha os dados esperados com a view/componente correto', function () {
//     concederPermissao(Permissao::ExternoRemessaCreate);

//     get(route('solicitacao.create'))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Remessa/Create')
//                 ->where('lotacao', $this->lotacao->only(['sigla', 'nome']))
//         );
// });

// test('cria uma nova remessa no status solicitada destinada à lotação do usuário autenticado, bem como por ele solicitada', function () {
//     $processo_1 = Processo::factory()->create();
//     $processo_2 = Processo::factory()->create();
//     $processo_3 = Processo::factory()->create();

//     concederPermissao(Permissao::ExternoRemessaCreate);

//     $this->assertDatabaseCount('remessas', 0);

//     post(route('solicitacao.store'), [
//         'processos' => [['numero' => $processo_1->numero], ['numero' => $processo_3->numero]],
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $this
//         ->assertDatabaseCount('remessas', 2)
//         ->assertDatabaseHas('remessas', [
//             'processo_id' => $processo_1->id,
//             'solicitante_id' => $this->usuario->id,
//             'recebedor_id' => null,
//             'remetente_id' => null,
//             'rearquivador_id' => null,
//             'lotacao_destinataria_id' => $this->usuario->lotacao_id,
//             'guia_id' => null,
//             'solicitada_em' => now(),
//             'entregue_em' => null,
//             'devolvida_em' => null,
//             'remessa_por_guia' => false,
//             'descricao' => null,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ])
//         ->assertDatabaseHas('remessas', [
//             'processo_id' => $processo_3->id,
//             'solicitante_id' => $this->usuario->id,
//             'recebedor_id' => null,
//             'remetente_id' => null,
//             'rearquivador_id' => null,
//             'lotacao_destinataria_id' => $this->usuario->lotacao_id,
//             'guia_id' => null,
//             'solicitada_em' => now(),
//             'entregue_em' => null,
//             'devolvida_em' => null,
//             'remessa_por_guia' => false,
//             'descricao' => null,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ])
//         ->assertDatabaseMissing('remessas', [
//             'processo_id' => $processo_2->id,
//         ]);
// });

// test('dispara o evento RemessaSolicitadaPeloUsuario quando o usuário solicita uma nova remessa', function () {
//     Event::fake();

//     $processos = Processo::factory(3)->create();

//     concederPermissao(Permissao::ExternoRemessaCreate);

//     post(route('solicitacao.store'), [
//         'processos' => $processos->map(fn ($processo) => $processo->only('numero')),
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     Event::assertDispatchedTimes(RemessaSolicitadaPeloUsuario::class, 1);
// });

test('exclui a solicitação informada', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create(['lotacao_destinataria_id' => $this->usuario->lotacao_id]);

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
