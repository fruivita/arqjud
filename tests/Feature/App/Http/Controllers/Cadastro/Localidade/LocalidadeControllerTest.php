<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\Localidade\LocalidadeController;
use App\Http\Requests\Cadastro\Localidade\StoreLocalidadeRequest;
use App\Http\Requests\Cadastro\Localidade\UpdateLocalidadeRequest;
use App\Http\Resources\Localidade\LocalidadeResource;
use App\Models\Localidade;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->dados = [
        'nome' => 'foo',
        'descricao' => 'foo bar',
    ];

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma localidade', function () {
    $id_localidade = Localidade::factory()->create()->id;

    expect(Localidade::where('id', $id_localidade)->exists())->toBeTrue();

    delete(route('cadastro.localidade.destroy', $id_localidade))->assertForbidden();

    expect(Localidade::where('id', $id_localidade)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação da localidade', function () {
    get(route('cadastro.localidade.create'))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        LocalidadeController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreLocalidadeRequest::class],
    ['update', UpdateLocalidadeRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Localidade::factory(2)->create();

    concederPermissao(Permissao::LOCALIDADE_VIEW_ANY);

    get(route('cadastro.localidade.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Localidade/Index')
                ->has('localidades.data', 2)
                ->has('localidades.meta.termo')
                ->has('localidades.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Localidade::factory()->create();

    $this->travel(1)->seconds();

    $ultima_localidade_criada = Localidade::factory()->create();

    concederPermissao(Permissao::LOCALIDADE_CREATE);

    get(route('cadastro.localidade.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Localidade/Create')
                ->whereAll([
                    'ultima_insercao.data' => LocalidadeResource::make($ultima_localidade_criada)->resolve(),
                    'links' => ['create' => route('cadastro.localidade.store')],
                ])
        );
});

test('cria uma nova localidade', function () {
    concederPermissao(Permissao::LOCALIDADE_CREATE);

    expect(Localidade::count())->toBe(0);

    post(route('cadastro.localidade.store', $this->dados))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Localidade::count())->toBe(1)
        ->and(Localidade::first()->only(array_keys($this->dados)))
        ->toBe($this->dados);
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::LOCALIDADE_UPDATE);

    $localidade = Localidade::factory()->hasPredios(3)->create();

    get(route('cadastro.localidade.edit', $localidade))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Localidade/Edit')
                ->where('localidade.data', LocalidadeResource::make($localidade)->resolve())
                ->has('predios.data', 3)
                ->has('predios.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::LOCALIDADE_VIEW);

    $localidade = Localidade::factory()->create();

    get(route('cadastro.localidade.edit', $localidade))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Cadastro/Localidade/Edit')
        );
});

test('atualiza uma localidade', function () {
    concederPermissao(Permissao::LOCALIDADE_UPDATE);

    $localidade = Localidade::factory()->create();

    patch(route('cadastro.localidade.update', $localidade), $this->dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $localidade->refresh();

    expect($localidade->only(array_keys($this->dados)))->toBe($this->dados);
});

test('exclui a localidade informada', function () {
    $id_localidade = Localidade::factory()->create()->id;

    concederPermissao(Permissao::LOCALIDADE_DELETE);

    expect(Localidade::where('id', $id_localidade)->exists())->toBeTrue();

    delete(route('cadastro.localidade.destroy', $id_localidade))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Localidade::where('id', $id_localidade)->exists())->toBeFalse();
});

test('LocalidadeController usa trait', function () {
    expect(
        collect(class_uses(LocalidadeController::class))
            ->has([
                \App\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
