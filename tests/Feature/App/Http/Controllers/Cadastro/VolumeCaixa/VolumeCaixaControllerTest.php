<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\VolumeCaixa\VolumeCaixaController;
use App\Http\Requests\Cadastro\VolumeCaixa\StoreVolumeCaixaRequest;
use App\Http\Requests\Cadastro\VolumeCaixa\UpdateVolumeCaixaRequest;
use App\Http\Resources\Caixa\CaixaResource;
use App\Http\Resources\VolumeCaixa\VolumeCaixaResource;
use App\Models\Caixa;
use App\Models\Permissao;
use App\Models\VolumeCaixa;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->caixa = Caixa::factory()->create();

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir um volume de caixa', function () {
    $id_volume_caixa = VolumeCaixa::factory()->create()->id;

    expect(VolumeCaixa::where('id', $id_volume_caixa)->exists())->toBeTrue();

    delete(route('cadastro.volumeCaixa.destroy', $id_volume_caixa))->assertForbidden();

    expect(VolumeCaixa::where('id', $id_volume_caixa)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação do volume da caixa', function () {
    get(route('cadastro.volumeCaixa.create', $this->caixa))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function ($action, $request) {
    $this->assertActionUsesFormRequest(
        VolumeCaixaController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreVolumeCaixaRequest::class],
    ['update', UpdateVolumeCaixaRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    VolumeCaixa::factory(2)->create();

    concederPermissao(Permissao::VOLUME_CAIXA_VIEW_ANY);

    get(route('cadastro.volumeCaixa.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/VolumeCaixa/Index')
                ->has('volumes.data', 2)
                ->has('volumes.meta.termo')
                ->has('volumes.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    VolumeCaixa::factory()->for($this->caixa)->create();

    $this->travel(1)->seconds();
    $ultimo_volume_caixa_criado = VolumeCaixa::factory()->for($this->caixa)->create();

    $this->travel(1)->seconds();
    // volume da caixa de outra caixa, será desconsiderada
    VolumeCaixa::factory()->create();

    concederPermissao(Permissao::VOLUME_CAIXA_CREATE);

    get(route('cadastro.volumeCaixa.create', $this->caixa))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/VolumeCaixa/Create')
                ->whereAll([
                    'ultima_insercao.data' => VolumeCaixaResource::make($ultimo_volume_caixa_criado)->resolve(),
                    'caixa' => CaixaResource::make($this->caixa->load(['localidadeCriadora', 'prateleira.estante.sala.andar.predio.localidade']))->response()->getData(true),
                ])
        );
});

test('cria um novo volume da caixa', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_CREATE);

    $dados = [
        'numero' => 10,
        'descricao' => 'foo bar',
        'caixa_id' => $this->caixa->id,
    ];

    expect(VolumeCaixa::count())->toBe(0);

    post(route('cadastro.volumeCaixa.store', $this->caixa), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $volume_caixa = VolumeCaixa::first();

    expect(VolumeCaixa::count())->toBe(1)
        ->and($volume_caixa->only(array_keys($dados)))->toBe($dados);
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_UPDATE);

    $volume_caixa = VolumeCaixa::factory()->hasProcessos(3)->create();

    $volume_caixa->load(['caixa.prateleira.estante.sala.andar.predio.localidade', 'caixa.localidadeCriadora']);

    get(route('cadastro.volumeCaixa.edit', $volume_caixa))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/VolumeCaixa/Edit')
                ->where('volume_caixa', VolumeCaixaResource::make($volume_caixa)->response()->getData(true))
                ->has('processos.data', 3)
                ->has('processos.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_VIEW);

    $volume_caixa = VolumeCaixa::factory()->create();

    get(route('cadastro.volumeCaixa.edit', $volume_caixa))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Cadastro/VolumeCaixa/Edit')
        );
});

test('atualiza um volume da caixa', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_UPDATE);

    $volume_caixa = VolumeCaixa::factory()->create();

    $dados = [
        'numero' => 10,
        'descricao' => 'foo bar',
    ];

    patch(route('cadastro.volumeCaixa.update', $volume_caixa), $dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $volume_caixa->refresh();

    expect($volume_caixa->only(array_keys($dados)))->toBe($dados);
});

test('exclui o volume da caixa informada', function () {
    $id_volume_caixa = VolumeCaixa::factory()->create()->id;

    concederPermissao(Permissao::VOLUME_CAIXA_DELETE);

    expect(VolumeCaixa::where('id', $id_volume_caixa)->exists())->toBeTrue();

    delete(route('cadastro.volumeCaixa.destroy', $id_volume_caixa))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(VolumeCaixa::where('id', $id_volume_caixa)->exists())->toBeFalse();
});

test('VolumeCaixaController usa trait', function () {
    expect(
        collect(class_uses(VolumeCaixaController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
