<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Movimentacao\MoveProcessoEntreCaixaController;
use App\Http\Requests\Movimentacao\StoreMoveProcessoEntreCaixaRequest;
use App\Models\Caixa;
use App\Models\Localidade;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());
});

afterEach(fn () => logout());

// Autorização
test('usuário sem permissão não consegue exibir formulário de movimentação dos processos entre caixas', function () {
    get(route('movimentacao.entre-caixas.create'))->assertForbidden();
});

test('usuário sem permissão não consegue movimentar processos entre caixas', function () {
    $processo = Processo::factory()->create();
    $caixa = Caixa::factory()->create();

    post(route('movimentacao.entre-caixas.store'), [
        'processos' => [
            ['numero' => $processo->numero],
        ],
        'numero' => $caixa->numero,
        'ano' => $caixa->ano,
        'guarda_permanente' => $caixa->guarda_permanente,
        'localidade_criadora_id' => $caixa->localidade_criadora_id,
        'complemento' => $caixa->complemento,
    ])->assertForbidden();

    $processo->refresh();

    expect($processo->caixa_id)->not->toBe($caixa->id);
});

// Caminho feliz
test('action do controller usa o form request', function ($action, $request) {
    $this->assertActionUsesFormRequest(
        MoveProcessoEntreCaixaController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreMoveProcessoEntreCaixaRequest::class],
]);

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Localidade::factory(2)->create();

    concederPermissao(Permissao::MOVER_PROCESSO_CREATE);

    get(route('movimentacao.entre-caixas.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Movimentacao/EntreCaixa/Create')
                ->has('localidades.data', 2)
        );
});

test('movimenta determinados processos', function () {
    concederPermissao(Permissao::MOVER_PROCESSO_CREATE);

    $caixa = Caixa::factory()->create();
    $processo_1 = Processo::factory()->create();
    $processo_2 = Processo::factory()->create();
    $processo_3 = Processo::factory()->create();

    post(route('movimentacao.entre-caixas.store'), [
        'processos' => [
            ['numero' => $processo_1->numero],
            ['numero' => $processo_2->numero],
        ],
        'numero' => $caixa->numero,
        'ano' => $caixa->ano,
        'guarda_permanente' => $caixa->guarda_permanente,
        'localidade_criadora_id' => $caixa->localidade_criadora_id,
        'complemento' => $caixa->complemento,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $processo_1->refresh();
    $processo_2->refresh();
    $processo_3->refresh();

    expect($processo_1->caixa_id)->toBe($caixa->id)
        ->and($processo_2->caixa_id)->toBe($caixa->id)
        ->and($processo_3->caixa_id)->not->toBe($caixa->id);
});

test('MoveProcessoEntreCaixaController usa trait', function () {
    expect(
        collect(class_uses(MoveProcessoEntreCaixaController::class))
            ->has([
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
