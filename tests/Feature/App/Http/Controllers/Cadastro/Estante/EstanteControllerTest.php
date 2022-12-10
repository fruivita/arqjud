<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\Estante\EstanteController;
use App\Http\Requests\Cadastro\Estante\StoreEstanteRequest;
use App\Http\Requests\Cadastro\Estante\UpdateEstanteRequest;
use App\Http\Resources\Estante\EstanteResource;
use App\Http\Resources\Sala\SalaResource;
use App\Models\Estante;
use App\Models\Permissao;
use App\Models\Prateleira;
use App\Models\Sala;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->sala = Sala::factory()->create();

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma estante', function () {
    $id_estante = Estante::factory()->create()->id;

    expect(Estante::where('id', $id_estante)->exists())->toBeTrue();

    delete(route('cadastro.estante.destroy', $id_estante))->assertForbidden();

    expect(Estante::where('id', $id_estante)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação da estante', function () {
    get(route('cadastro.estante.create', $this->sala))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        EstanteController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreEstanteRequest::class],
    ['update', UpdateEstanteRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Estante::factory(2)->create();

    concederPermissao(Permissao::ESTANTE_VIEW_ANY);

    get(route('cadastro.estante.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Estante/Index')
                ->has('estantes.data', 2)
                ->has('estantes.meta.termo')
                ->has('estantes.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Estante::factory()->for($this->sala)->create();

    $this->travel(1)->seconds();
    $ultima_estante_criada = Estante::factory()->for($this->sala)->create();

    $this->travel(1)->seconds();
    // estante de outra sala, será desconsiderada
    Estante::factory()->create();

    concederPermissao(Permissao::ESTANTE_CREATE);

    get(route('cadastro.estante.create', $this->sala))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Estante/Create')
                ->whereAll([
                    'ultima_insercao.data' => EstanteResource::make($ultima_estante_criada)->resolve(),
                    'sala' => SalaResource::make($this->sala->load('andar.predio.localidade'))->response()->getData(true),
                ])
        );
});

test('cria uma nova estante e junto uma prateleira padrão', function () {
    concederPermissao(Permissao::ESTANTE_CREATE);

    expect(Estante::count())->toBe(0)
        ->and(Prateleira::count())->toBe(0);

    post(route('cadastro.estante.store', $this->sala), [
        'numero' => '10-a',
        'descricao' => 'foo bar',
        'sala_id' => $this->sala->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $estante = Estante::with('prateleiras')->first();
    $prateleira = $estante->prateleiras->first();

    expect(Estante::count())->toBe(1)
        ->and(Prateleira::count())->toBe(1)
        ->and($estante->numero)->toBe('10-a')
        ->and($estante->descricao)->toBe('foo bar')
        ->and($estante->sala_id)->toBe($this->sala->id)
        ->and($prateleira->numero)->toBe('0')
        ->and($prateleira->estante_id)->toBe($estante->id)
        ->and($prateleira->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'));
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::ESTANTE_UPDATE);

    $estante = Estante::factory()->has(Prateleira::factory(3), 'prateleiras')->create();

    $estante->load('sala.andar.predio.localidade');

    get(route('cadastro.estante.edit', $estante))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Estante/Edit')
                ->where('estante', EstanteResource::make($estante)->response()->getData(true))
                ->has('prateleiras.data', 3)
                ->has('prateleiras.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::ESTANTE_VIEW);

    $estante = Estante::factory()->create();

    get(route('cadastro.estante.edit', $estante))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Cadastro/Estante/Edit')
        );
});

test('atualiza uma estante', function () {
    concederPermissao(Permissao::ESTANTE_UPDATE);

    $estante = Estante::factory()->create();

    $dados = [
        'numero' => '10-a',
        'descricao' => 'foo bar',
    ];

    patch(route('cadastro.estante.update', $estante), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $estante->refresh();

    expect($estante->only(array_keys($dados)))->toBe($dados);
});

test('exclui a estante informado', function () {
    $id_estante = Estante::factory()->create()->id;

    concederPermissao(Permissao::ESTANTE_DELETE);

    expect(Estante::where('id', $id_estante)->exists())->toBeTrue();

    delete(route('cadastro.estante.destroy', $id_estante))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Estante::where('id', $id_estante)->exists())->toBeFalse();
});

test('EstanteController usa trait', function () {
    expect(
        collect(class_uses(EstanteController::class))
            ->has([
                \App\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
