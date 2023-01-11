<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\Andar\AndarController;
use App\Http\Requests\Cadastro\Andar\StoreAndarRequest;
use App\Http\Requests\Cadastro\Andar\UpdateAndarRequest;
use App\Http\Resources\Andar\AndarResource;
use App\Http\Resources\Predio\PredioResource;
use App\Models\Andar;
use App\Models\Permissao;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->predio = Predio::factory()->create();

    login();
});

afterEach(fn () => logout());

// Autorização
test('usuário sem permissão não consegue excluir um andar', function () {
    $id_andar = Andar::factory()->create()->id;

    expect(Andar::where('id', $id_andar)->exists())->toBeTrue();

    delete(route('cadastro.andar.destroy', $id_andar))->assertForbidden();

    expect(Andar::where('id', $id_andar)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação do andar', function () {
    get(route('cadastro.andar.create', $this->predio))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        AndarController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreAndarRequest::class],
    ['update', UpdateAndarRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Andar::factory(2)->create();

    concederPermissao(Permissao::ANDAR_VIEW_ANY);

    get(route('cadastro.andar.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Andar/Index')
                ->has('andares.data', 2)
                ->has('andares.meta.termo')
                ->has('andares.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Andar::factory()->for($this->predio)->create();

    $this->travel(1)->seconds();
    $ultimo_andar_criado = Andar::factory()->for($this->predio)->create();

    $this->travel(1)->seconds();
    // andar de outro prédio, será desconsiderado
    Andar::factory()->create();

    concederPermissao(Permissao::ANDAR_CREATE);

    get(route('cadastro.andar.create', $this->predio))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Andar/Create')
                ->whereAll([
                    'ultima_insercao.data' => AndarResource::make($ultimo_andar_criado)->resolve(),
                    'predio' => PredioResource::make($this->predio->load('localidade'))->response()->getData(true),
                ])
        );
});

test('cria um novo andar', function () {
    concederPermissao(Permissao::ANDAR_CREATE);

    $dados = [
        'numero' => 10,
        'apelido' => 'foo',
        'descricao' => 'foo bar',
        'predio_id' => $this->predio->id,
    ];

    expect(Andar::count())->toBe(0);

    post(route('cadastro.andar.store', $this->predio), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Andar::count())->toBe(1)
        ->and(Andar::first()->only(array_keys($dados)))
        ->toBe($dados);
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::ANDAR_UPDATE);

    $andar = Andar::factory()->hasSalas(3)->create();

    $andar->load('predio.localidade');

    get(route('cadastro.andar.edit', $andar))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Andar/Edit')
                ->where('andar', AndarResource::make($andar)->response()->getData(true))
                ->has('salas.data', 3)
                ->has('salas.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::ANDAR_VIEW);

    $andar = Andar::factory()->create();

    get(route('cadastro.andar.edit', $andar))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Cadastro/Andar/Edit')
        );
});

test('atualiza um andar', function () {
    concederPermissao(Permissao::ANDAR_UPDATE);

    $andar = Andar::factory()->create();

    $dados = [
        'numero' => 10,
        'apelido' => '10º',
        'descricao' => 'foo bar',
    ];

    patch(route('cadastro.andar.update', $andar), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $andar->refresh();

    expect($andar->only(array_keys($dados)))->toBe($dados);
});

test('exclui o andar informado', function () {
    $id_andar = Andar::factory()->create()->id;

    concederPermissao(Permissao::ANDAR_DELETE);

    expect(Andar::where('id', $id_andar)->exists())->toBeTrue();

    delete(route('cadastro.andar.destroy', $id_andar))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Andar::where('id', $id_andar)->exists())->toBeFalse();
});

test('AndarController usa trait', function () {
    expect(
        collect(class_uses(AndarController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
