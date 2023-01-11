<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\Prateleira\PrateleiraController;
use App\Http\Requests\Cadastro\Prateleira\StorePrateleiraRequest;
use App\Http\Requests\Cadastro\Prateleira\UpdatePrateleiraRequest;
use App\Http\Resources\Estante\EstanteResource;
use App\Http\Resources\Prateleira\PrateleiraResource;
use App\Models\Estante;
use App\Models\Permissao;
use App\Models\Prateleira;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->estante = Estante::factory()->create();

    login();
});

afterEach(fn () => logout());

// Autorização
test('usuário sem permissão não consegue excluir uma prateleira', function () {
    $id_prateleira = Prateleira::factory()->create()->id;

    expect(Prateleira::where('id', $id_prateleira)->exists())->toBeTrue();

    delete(route('cadastro.prateleira.destroy', $id_prateleira))->assertForbidden();

    expect(Prateleira::where('id', $id_prateleira)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação da prateleira', function () {
    get(route('cadastro.prateleira.create', $this->estante))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        PrateleiraController::class,
        $action,
        $request
    );
})->with([
    ['store', StorePrateleiraRequest::class],
    ['update', UpdatePrateleiraRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Prateleira::factory(2)->create();

    concederPermissao(Permissao::PRATELEIRA_VIEW_ANY);

    get(route('cadastro.prateleira.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Prateleira/Index')
                ->has('prateleiras.data', 2)
                ->has('prateleiras.meta.termo')
                ->has('prateleiras.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Prateleira::factory()->for($this->estante)->create();

    $this->travel(1)->seconds();
    $ultima_prateleira_criada = Prateleira::factory()->for($this->estante)->create();

    $this->travel(1)->seconds();
    // prateleira de outra estante, será desconsiderada
    Prateleira::factory()->create();

    concederPermissao(Permissao::PRATELEIRA_CREATE);

    get(route('cadastro.prateleira.create', $this->estante))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Prateleira/Create')
                ->whereAll([
                    'ultima_insercao.data' => PrateleiraResource::make($ultima_prateleira_criada)->resolve(),
                    'estante' => EstanteResource::make($this->estante->load('sala.andar.predio.localidade'))->response()->getData(true),
                ])
        );
});

test('cria uma nova prateleira', function () {
    concederPermissao(Permissao::PRATELEIRA_CREATE);

    $dados = [
        'numero' => '10-a',
        'descricao' => 'foo bar',
        'estante_id' => $this->estante->id,
    ];

    expect(Prateleira::count())->toBe(0);

    post(route('cadastro.prateleira.store', $this->estante), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $prateleira = Prateleira::first();

    expect(Prateleira::count())->toBe(1)
        ->and($prateleira->only(array_keys($dados)))->toBe($dados);
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::PRATELEIRA_UPDATE);

    $prateleira = Prateleira::factory()->hasCaixas(3)->create();

    $prateleira->load('estante.sala.andar.predio.localidade');

    get(route('cadastro.prateleira.edit', $prateleira))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Prateleira/Edit')
                ->where('prateleira', PrateleiraResource::make($prateleira)->response()->getData(true))
                ->has('caixas.data', 3)
                ->has('caixas.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::PRATELEIRA_VIEW);

    $prateleira = Prateleira::factory()->create();

    get(route('cadastro.prateleira.edit', $prateleira))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Cadastro/Prateleira/Edit')
        );
});

test('atualiza uma prateleira', function () {
    concederPermissao(Permissao::PRATELEIRA_UPDATE);

    $prateleira = Prateleira::factory()->create();

    $dados = [
        'numero' => '10-a',
        'descricao' => 'foo bar',
    ];

    patch(route('cadastro.prateleira.update', $prateleira), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $prateleira->refresh();

    expect($prateleira->only(array_keys($dados)))->toBe($dados);
});

test('exclui a prateleira informada', function () {
    $id_prateleira = Prateleira::factory()->create()->id;

    concederPermissao(Permissao::PRATELEIRA_DELETE);

    expect(Prateleira::where('id', $id_prateleira)->exists())->toBeTrue();

    delete(route('cadastro.prateleira.destroy', $id_prateleira))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Prateleira::where('id', $id_prateleira)->exists())->toBeFalse();
});

test('PrateleiraController usa trait', function () {
    expect(
        collect(class_uses(PrateleiraController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
