<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\Caixa\CaixaController;
use App\Http\Requests\Cadastro\Caixa\StoreCaixaRequest;
use App\Http\Requests\Cadastro\Caixa\UpdateCaixaRequest;
use App\Http\Resources\Caixa\CaixaResource;
use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Resources\Prateleira\PrateleiraResource;
use App\Models\Caixa;
use App\Models\Localidade;
use App\Models\Permissao;
use App\Models\Prateleira;
use App\Models\Processo;
use App\Models\VolumeCaixa;
use App\Pipes\Caixa\SetGPProcessos;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->prateleira = Prateleira::factory()->create();

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma caixa', function () {
    $id_caixa = Caixa::factory()->create()->id;

    expect(Caixa::where('id', $id_caixa)->exists())->toBeTrue();

    delete(route('cadastro.caixa.destroy', $id_caixa))->assertForbidden();

    expect(Caixa::where('id', $id_caixa)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação da caixa', function () {
    get(route('cadastro.caixa.create', $this->prateleira))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function ($action, $request) {
    $this->assertActionUsesFormRequest(
        CaixaController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreCaixaRequest::class],
    ['update', UpdateCaixaRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Caixa::factory(2)->create();

    concederPermissao(Permissao::CAIXA_VIEW_ANY);

    get(route('cadastro.caixa.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Caixa/Index')
                ->has('caixas.data', 2)
                ->has('caixas.meta.termo')
                ->has('caixas.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Caixa::factory()->for($this->prateleira)->create();

    $this->travel(1)->seconds();
    $ultima_caixa_criada = Caixa::factory()->for($this->prateleira)->create();
    $ultima_caixa_criada->load('localidadeCriadora');

    $this->travel(1)->seconds();
    // caixa de outra prateleira, será desconsiderada
    Caixa::factory()->create();

    concederPermissao(Permissao::CAIXA_CREATE);

    get(route('cadastro.caixa.create', $this->prateleira))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Caixa/Create')
                ->whereAll([
                    'ultima_insercao' => CaixaResource::make($ultima_caixa_criada)->response()->getData(true),
                    'prateleira' => PrateleiraResource::make($this->prateleira->load('estante.sala.andar.predio.localidade'))->response()->getData(true),
                    'localidades' => LocalidadeOnlyResource::collection(Localidade::all())->response()->getData(true),
                ])
        );
});

test('cria uma nova caixa', function () {
    $localidade = Localidade::factory()->create();
    concederPermissao(Permissao::CAIXA_CREATE);

    $dados = [
        'numero' => 10,
        'ano' => 2020,
        'guarda_permanente' => true,
        'complemento' => 'foo',
        'descricao' => 'foo bar',
        'localidade_criadora_id' => $localidade->id,
        'prateleira_id' => $this->prateleira->id,
    ];

    expect(Caixa::count())->toBe(0);

    post(route('cadastro.caixa.store', $this->prateleira), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $caixa = Caixa::first();

    expect(Caixa::count())->toBe(1)
        ->and($caixa->only(array_keys($dados)))->toBe($dados);
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::CAIXA_UPDATE);

    $caixa = Caixa::factory()->hasVolumes(3)->create();

    $caixa->load(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora']);

    get(route('cadastro.caixa.edit', $caixa))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Caixa/Edit')
                ->where('caixa', CaixaResource::make($caixa)->response()->getData(true))
                ->has('volumes_caixa.data', 3)
                ->has('volumes_caixa.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::CAIXA_VIEW);

    $caixa = Caixa::factory()->create();

    get(route('cadastro.caixa.edit', $caixa))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Cadastro/Caixa/Edit')
        );
});

test('atualiza uma caixa e o status de guarda dos processos da caixa', function (bool $gp) {
    concederPermissao(Permissao::CAIXA_UPDATE);

    $caixa = Caixa::factory()
        ->has(VolumeCaixa::factory(2)->hasProcessos(3, ['guarda_permanente' => !$gp]), 'volumes')
        ->create();

    Processo::factory(2)->create(['guarda_permanente' => !$gp]); // não serão afetados

    $localidade = Localidade::factory()->create();

    $dados = [
        'numero' => 500,
        'ano' => 2000,
        'guarda_permanente' => $gp,
        'complemento' => 'foo',
        'descricao' => 'foo bar',
        'localidade_criadora_id' => $localidade->id,
    ];

    patch(route('cadastro.caixa.update', $caixa), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Caixa::find($caixa->id)->only(array_keys($dados)))->toBe($dados)
        ->and(Processo::where('guarda_permanente', $gp)->count())->toBe(6)
        ->and(Processo::where('guarda_permanente', !$gp)->count())->toBe(2);
})->with([
    true,
    false,
]);

test('atualização está protegida com transação', function () {
    concederPermissao(Permissao::CAIXA_UPDATE);

    $caixa = Caixa::factory()
        ->has(VolumeCaixa::factory(2)->hasProcessos(3), 'volumes')
        ->create();

    $dados = [
        'numero' => 500,
        'ano' => 2000,
        'guarda_permanente' => true,
        'localidade_criadora_id' => Localidade::first()->id,
    ];

    $database = DB::spy();

    patch(route('cadastro.caixa.update', $caixa), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('commit')->once();
});

test('atualização está protegida com transação e faz o rollback', function () {
    concederPermissao(Permissao::CAIXA_UPDATE);

    $this->mock(SetGPProcessos::class)
        ->shouldReceive('handle')
        ->andThrow(new \RuntimeException());

    $caixa = Caixa::factory()
        ->has(VolumeCaixa::factory(2)->hasProcessos(3), 'volumes')
        ->create();

    $dados = [
        'numero' => 500,
        'ano' => 2000,
        'guarda_permanente' => true,
        'localidade_criadora_id' => Localidade::first()->id,
    ];

    $database = DB::spy();

    patch(route('cadastro.caixa.update', $caixa), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.erro');

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('rollBack')->once();
});

test('exclui a caixa informada', function () {
    $id_caixa = Caixa::factory()->create()->id;

    concederPermissao(Permissao::CAIXA_DELETE);

    expect(Caixa::where('id', $id_caixa)->exists())->toBeTrue();

    delete(route('cadastro.caixa.destroy', $id_caixa))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Caixa::where('id', $id_caixa)->exists())->toBeFalse();
});

test('CaixaController usa trait', function () {
    expect(
        collect(class_uses(CaixaController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
