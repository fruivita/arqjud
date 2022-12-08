<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\Predio\PredioController;
use App\Http\Requests\Cadastro\Predio\EditPredioRequest;
use App\Http\Requests\Cadastro\Predio\IndexPredioRequest;
use App\Http\Requests\Cadastro\Predio\PostPredioRequest;
use App\Http\Resources\Localidade\LocalidadeResource;
use App\Http\Resources\Predio\PredioResource;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Permissao;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->localidade = Localidade::factory()->create();

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir um prédio', function () {
    $id_predio = Predio::factory()->create()->id;

    expect(Predio::where('id', $id_predio)->exists())->toBeTrue();

    delete(route('cadastro.predio.destroy', $id_predio))
        ->assertForbidden();

    expect(Predio::where('id', $id_predio)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação do prédio', function () {
    get(route('cadastro.predio.create', $this->localidade))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        PredioController::class,
        $action,
        $request
    );
})->with([
    ['store', PostPredioRequest::class],
    ['update', PostPredioRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Predio::factory(2)->create();

    concederPermissao(Permissao::PREDIO_VIEW_ANY);

    get(route('cadastro.predio.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Predio/Index')
                ->has('predios.data', 2)
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Predio::factory()->for($this->localidade)->create();

    $this->travel(1)->seconds();
    $ultimo_predio_criado = Predio::factory()->for($this->localidade)->create();

    $this->travel(1)->seconds();
    // prédio de outra localidade, será desconsiderado
    Predio::factory()->create();

    concederPermissao(Permissao::PREDIO_CREATE);

    get(route('cadastro.predio.create', $this->localidade))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Predio/Create')
                ->whereAll([
                    'ultima_insercao.data' => PredioResource::make($ultimo_predio_criado)->resolve(request()),
                    'localidade.data' => LocalidadeResource::make($this->localidade)->resolve(request()),
                ])
        );
});

test('cria um novo prédio', function () {
    concederPermissao(Permissao::PREDIO_CREATE);

    $dados = [
        'nome' => 'foo',
        'descricao' => 'foo bar',
        'localidade_id' => $this->localidade->id,
    ];

    expect(Predio::count())->toBe(0);

    post(route('cadastro.predio.store', $this->localidade), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Predio::count())->toBe(1)
        ->and(Predio::first()->only(array_keys($dados)))
        ->toBe($dados);
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::PREDIO_UPDATE);

    $predio = Predio::factory()->hasAndares(3)->create();

    get(route('cadastro.predio.edit', $predio))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Predio/Edit')
                ->where('predio.data.id', $predio->id)
                ->has('andares.data', 3)
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::PREDIO_VIEW);

    $predio = Predio::factory()->create();

    get(route('cadastro.predio.edit', $predio))->assertOk();
});

test('atualiza um prédio', function () {
    concederPermissao(Permissao::PREDIO_UPDATE);

    $predio = Predio::factory()->create();

    patch(route('cadastro.predio.update', $predio), [
        'nome' => 'foo',
        'descricao' => 'foo bar',
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $predio->refresh();

    expect($predio->nome)->toBe('foo')
        ->and($predio->descricao)->toBe('foo bar');
});

test('exclui o prédio informado', function () {
    $id_predio = Predio::factory()->create()->id;

    concederPermissao(Permissao::PREDIO_DELETE);

    expect(Predio::where('id', $id_predio)->exists())->toBeTrue();

    delete(route('cadastro.predio.destroy', $id_predio))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Predio::where('id', $id_predio)->exists())->toBeFalse();
});

test('PredioController usa trait', function () {
    expect(
        collect(class_uses(PredioController::class))
            ->has([
                \App\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
