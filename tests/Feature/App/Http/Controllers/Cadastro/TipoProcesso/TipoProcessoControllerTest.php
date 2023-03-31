<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\TipoProcesso\TipoProcessoController;
use App\Http\Requests\Cadastro\TipoProcesso\StoreTipoProcessoRequest;
use App\Http\Requests\Cadastro\TipoProcesso\UpdateTipoProcessoRequest;
use App\Http\Resources\TipoProcesso\TipoProcessoResource;
use App\Models\Permissao;
use App\Models\TipoProcesso;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
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

    Auth::login(Usuario::factory()->create());
});

afterEach(fn () => logout());

// Autorização
test('usuário sem permissão não consegue excluir um tipo de processo', function () {
    $id_tipo_processo = TipoProcesso::factory()->create()->id;

    expect(TipoProcesso::where('id', $id_tipo_processo)->exists())->toBeTrue();

    delete(route('cadastro.tipo-processo.destroy', $id_tipo_processo))->assertForbidden();

    expect(TipoProcesso::where('id', $id_tipo_processo)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação do tipo de processo', function () {
    get(route('cadastro.tipo-processo.create'))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        TipoProcessoController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreTipoProcessoRequest::class],
    ['update', UpdateTipoProcessoRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    TipoProcesso::factory(2)->create();

    concederPermissao(Permissao::TIPO_PROCESSO_VIEW_ANY);

    get(route('cadastro.tipo-processo.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/TipoProcesso/Index')
                ->has('tipos_processo.data', 2)
                ->has('tipos_processo.meta.termo')
                ->has('tipos_processo.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    TipoProcesso::factory()->create();

    $this->travel(1)->seconds();

    $ultimo_tipo_processo_criado = TipoProcesso::factory()->create();

    concederPermissao(Permissao::TIPO_PROCESSO_CREATE);

    get(route('cadastro.tipo-processo.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/TipoProcesso/Create')
                ->whereAll([
                    'ultima_insercao.data' => TipoProcessoResource::make($ultimo_tipo_processo_criado)->resolve(),
                    'links' => ['store' => route('cadastro.tipo-processo.store')],
                ])
        );
});

test('cria uma novo tipo de processo', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_CREATE);

    expect(TipoProcesso::count())->toBe(0);

    post(route('cadastro.tipo-processo.store', $this->dados))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(TipoProcesso::count())->toBe(1)
        ->and(TipoProcesso::first()->only(array_keys($this->dados)))
        ->toBe($this->dados);
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_UPDATE);

    $tipo_processo = TipoProcesso::factory()->hasCaixas(3)->create();

    get(route('cadastro.tipo-processo.edit', $tipo_processo))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/TipoProcesso/Edit')
                ->where('tipo_processo.data', TipoProcessoResource::make($tipo_processo)->resolve())
                ->has('caixas.data', 3)
                ->has('caixas.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_VIEW);

    $tipo_processo = TipoProcesso::factory()->create();

    get(route('cadastro.tipo-processo.edit', $tipo_processo))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Cadastro/TipoProcesso/Edit')
        );
});

test('atualiza umo tipo de processo', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_UPDATE);

    $tipo_processo = TipoProcesso::factory()->create();

    patch(route('cadastro.tipo-processo.update', $tipo_processo), $this->dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $tipo_processo->refresh();

    expect($tipo_processo->only(array_keys($this->dados)))->toBe($this->dados);
});

test('exclui o tipo de processo informado', function () {
    $id_tipo_processo = TipoProcesso::factory()->create()->id;

    concederPermissao(Permissao::TIPO_PROCESSO_DELETE);

    expect(TipoProcesso::where('id', $id_tipo_processo)->exists())->toBeTrue();

    delete(route('cadastro.tipo-processo.destroy', $id_tipo_processo))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(TipoProcesso::where('id', $id_tipo_processo)->exists())->toBeFalse();
});

test('TipoProcessoController usa trait', function () {
    expect(
        collect(class_uses(TipoProcessoController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
