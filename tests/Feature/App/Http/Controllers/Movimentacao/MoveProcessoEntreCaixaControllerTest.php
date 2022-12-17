<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Movimentacao\MoveProcessoEntreCaixaController;
use App\Http\Requests\Movimentacao\PostMoveProcessoEntreCaixaRequest;
use App\Models\Localidade;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\VolumeCaixa;
use Database\Seeders\PerfilSeeder;
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
test('usuário sem permissão não consegue exibir formulário de movimentação dos processos entre caixas', function () {
    get(route('movimentacao.entre-caixas.create'))->assertForbidden();
});

test('usuário sem permissão não consegue movimentar processos entre caixas', function () {
    $processo = Processo::factory()->create();
    $volume_caixa = VolumeCaixa::factory()->create();

    post(route('movimentacao.entre-caixas.store'), [
        'processos' => [
            ['numero' => $processo->numero],
        ],
        'volume_id' => $volume_caixa->id,
    ])->assertForbidden();

    $processo->refresh();

    expect($processo->volume_caixa_id)->not->toBe($volume_caixa->id);
});

// Caminho feliz
test('action do controller usa o form request', function ($action, $request) {
    $this->assertActionUsesFormRequest(
        MoveProcessoEntreCaixaController::class,
        $action,
        $request
    );
})->with([
    ['store', PostMoveProcessoEntreCaixaRequest::class],
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

    $volume_caixa = VolumeCaixa::factory()->create();
    $processo_1 = Processo::factory()->create();
    $processo_2 = Processo::factory()->create();
    $processo_3 = Processo::factory()->create();

    post(route('movimentacao.entre-caixas.store'), [
        'processos' => [
            ['numero' => $processo_1->numero],
            ['numero' => $processo_2->numero],
        ],
        'volume_id' => $volume_caixa->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $processo_1->refresh();
    $processo_2->refresh();
    $processo_3->refresh();

    expect($processo_1->volume_caixa_id)->toBe($volume_caixa->id)
        ->and($processo_2->volume_caixa_id)->toBe($volume_caixa->id)
        ->and($processo_3->volume_caixa_id)->not->toBe($volume_caixa->id);
});

test('MoveProcessoEntreCaixaController usa trait', function () {
    expect(
        collect(class_uses(MoveProcessoEntreCaixaController::class))
            ->has([
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
