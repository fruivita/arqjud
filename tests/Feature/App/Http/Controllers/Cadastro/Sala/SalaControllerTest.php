<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\Sala\SalaController;
use App\Http\Requests\Cadastro\Sala\StoreSalaRequest;
use App\Http\Requests\Cadastro\Sala\UpdateSalaRequest;
use App\Http\Resources\Andar\AndarResource;
use App\Http\Resources\Sala\SalaResource;
use App\Models\Andar;
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

    $this->andar = Andar::factory()->create();

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma sala', function () {
    $id_sala = Sala::factory()->create()->id;

    expect(Sala::where('id', $id_sala)->exists())->toBeTrue();

    delete(route('cadastro.sala.destroy', $id_sala))->assertForbidden();

    expect(Sala::where('id', $id_sala)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação da sala', function () {
    get(route('cadastro.sala.create', $this->andar))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function ($action, $request) {
    $this->assertActionUsesFormRequest(
        SalaController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreSalaRequest::class],
    ['update', UpdateSalaRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Sala::factory(2)->create();

    concederPermissao(Permissao::SALA_VIEW_ANY);

    get(route('cadastro.sala.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Sala/Index')
                ->has('salas.data', 2)
                ->has('salas.meta.termo')
                ->has('salas.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Sala::factory()->for($this->andar)->create();

    $this->travel(1)->seconds();
    $ultima_sala_criada = Sala::factory()->for($this->andar)->create();

    $this->travel(1)->seconds();
    // sala de outro andar, será desconsiderada
    Sala::factory()->create();

    concederPermissao(Permissao::SALA_CREATE);

    get(route('cadastro.sala.create', $this->andar))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Sala/Create')
                ->whereAll([
                    'ultima_insercao.data' => SalaResource::make($ultima_sala_criada)->resolve(),
                    'andar' => AndarResource::make($this->andar->load('predio.localidade'))->response()->getData(true),
                ])
        );
});

test('cria uma nova sala e junto uma estante e uma prateleira padrão', function () {
    concederPermissao(Permissao::SALA_CREATE);

    expect(Sala::count())->toBe(0)
        ->and(Estante::count())->toBe(0)
        ->and(Prateleira::count())->toBe(0);

    post(route('cadastro.sala.store', $this->andar), [
        'numero' => '10-a',
        'descricao' => 'foo bar',
        'andar_id' => $this->andar->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $sala = Sala::with('estantes.prateleiras')->first();
    $estante = $sala->estantes->first();
    $prateleira = $estante->prateleiras->first();

    expect(Sala::count())->toBe(1)
        ->and(Estante::count())->toBe(1)
        ->and(Prateleira::count())->toBe(1)
        ->and($sala->numero)->toBe('10-a')
        ->and($sala->descricao)->toBe('foo bar')
        ->and($sala->andar_id)->toBe($this->andar->id)
        ->and($estante->numero)->toBe('0')
        ->and($estante->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'))
        ->and($estante->sala_id)->toBe($sala->id)
        ->and($prateleira->numero)->toBe('0')
        ->and($prateleira->estante_id)->toBe($estante->id)
        ->and($prateleira->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'));
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::SALA_UPDATE);

    $sala = Sala::factory()->hasEstantes(3)->create();

    $sala->load('andar.predio.localidade');

    get(route('cadastro.sala.edit', $sala))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Sala/Edit')
                ->where('sala', SalaResource::make($sala)->response()->getData(true))
                ->has('estantes.data', 3)
                ->has('estantes.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::SALA_VIEW);

    $sala = Sala::factory()->create();

    get(route('cadastro.sala.edit', $sala))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Cadastro/Sala/Edit')
        );
});

test('atualiza uma sala', function () {
    concederPermissao(Permissao::SALA_UPDATE);

    $sala = Sala::factory()->create();

    $dados = [
        'numero' => '10-a',
        'descricao' => 'foo bar',
    ];

    patch(route('cadastro.sala.update', $sala), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $sala->refresh();

    expect($sala->only(array_keys($dados)))->toBe($dados);
});

test('exclui a sala informada', function () {
    $id_sala = Sala::factory()->create()->id;

    concederPermissao(Permissao::SALA_DELETE);

    expect(Sala::where('id', $id_sala)->exists())->toBeTrue();

    delete(route('cadastro.sala.destroy', $id_sala))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Sala::where('id', $id_sala)->exists())->toBeFalse();
});

test('SalaController usa trait', function () {
    expect(
        collect(class_uses(SalaController::class))
            ->has([
                \App\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
