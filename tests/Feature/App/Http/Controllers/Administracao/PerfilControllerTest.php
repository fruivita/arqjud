<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Administracao\PerfilController;
use App\Http\Requests\Administracao\StorePerfilRequest;
use App\Http\Requests\Administracao\UpdatePerfilRequest;
use App\Http\Resources\Perfil\PerfilEditResource;
use App\Models\Perfil;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->dados = [
        'nome' => 'Loren Ipsun',
        'slug' => 'foo', // será ignorado pois o slug é gerado com base no nome
        'poder' => 2000, // será ignorado na atualização
        'descricao' => 'foo bar',
    ];

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir um perfil', function () {
    $id_perfil = Perfil::factory()->create()->id;

    expect(Perfil::where('id', $id_perfil)->exists())->toBeTrue();

    delete(route('administracao.perfil.destroy', $id_perfil))->assertForbidden();

    expect(Perfil::where('id', $id_perfil)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação do perfil', function () {
    get(route('administracao.perfil.create'))->assertForbidden();
});

test('usuário sem permissão não consegue exibir formulário de edição do perfil', function () {
    get(route('administracao.perfil.edit', Perfil::factory()->create()))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        PerfilController::class,
        $action,
        $request
    );
})->with([
    ['store', StorePerfilRequest::class],
    ['update', UpdatePerfilRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::PERFIL_VIEW_ANY);

    get(route('administracao.perfil.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Perfil/Index')
                ->has('perfis.data', 5)
                ->has('perfis.meta.termo')
                ->has('perfis.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Perfil::factory()->create();

    $this->travel(1)->seconds();

    $ultimo_perfil_criado = Perfil::factory()->create();

    concederPermissao(Permissao::PERFIL_CREATE);

    get(route('administracao.perfil.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Perfil/Create')
                ->whereAll([
                    'ultima_insercao.data' => PerfilEditResource::make($ultimo_perfil_criado)->resolve(),
                    'links' => ['store' => route('administracao.perfil.store')],
                ])
        );
});

test('cria um novo perfil com o slug baseado no nome', function () {
    concederPermissao(Permissao::PERFIL_CREATE);

    testTime()->freeze();

    expect(Perfil::count())->toBe(5);

    testTime()->addMinute();

    post(route('administracao.perfil.store', $this->dados))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Perfil::count())->toBe(6)
        ->and(Perfil::latest()->first()->only(array_keys($this->dados)))
        ->toMatchArray(['slug' => 'loren-ipsun'] + $this->dados);
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::PERFIL_UPDATE);

    $perfil = Perfil::factory()->hasPermissoes(3)->create();

    get(route('administracao.perfil.edit', $perfil))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Perfil/Edit')
                ->where('perfil.data', PerfilEditResource::make($perfil)->resolve())
                ->has('permissoes.data', 3 + 1) //todos as permissões serão retornados
                ->has('permissoes.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::PERFIL_VIEW);

    $perfil = Perfil::factory()->create();

    get(route('administracao.perfil.edit', $perfil))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Administracao/Perfil/Edit')
        );
});

test('atualiza um perfil', function () {
    concederPermissao(Permissao::PERFIL_UPDATE);

    $perfil_antes = Perfil::factory()->create();

    patch(route('administracao.perfil.update', $perfil_antes), $this->dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $perfil_depois = Perfil::find($perfil_antes->id);

    expect($perfil_depois->nome)->toBe($this->dados['nome'])
        ->and($perfil_depois->slug)->toBe('loren-ipsun')
        ->and($perfil_depois->poder)->toBe($perfil_antes->poder)
        ->and($perfil_depois->descricao)->toBe($this->dados['descricao']);
});

test('faz o toggle da permissão se ela for informada sem alterar os demais atributos do perfil', function () {
    concederPermissao(Permissao::PERFIL_UPDATE);
    $permissao = Permissao::factory()->create();
    $perfil_antes = Perfil::factory()->create();

    // adiciona a permissão (toggle)
    patch(route('administracao.perfil.update', $perfil_antes), $this->dados + ['permissao_id' => $permissao->id])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $perfil_depois = Perfil::with('Permissoes')->find($perfil_antes->id);

    expect($perfil_depois->nome)->toBe($perfil_antes->nome)
        ->and($perfil_depois->slug)->toBe($perfil_antes->slug)
        ->and($perfil_depois->poder)->toBe($perfil_antes->poder)
        ->and($perfil_depois->descricao)->toBe($perfil_antes->descricao)
        ->and($perfil_depois->permissoes->first()->id)->toBe($permissao->id);

    // remove a permissão (toggle)
    patch(route('administracao.perfil.update', $perfil_antes), $this->dados + ['permissao_id' => $permissao->id])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $perfil_depois = Perfil::with('permissoes')->find($perfil_antes->id);

    expect($perfil_depois->nome)->toBe($perfil_antes->nome)
        ->and($perfil_depois->slug)->toBe($perfil_antes->slug)
        ->and($perfil_depois->poder)->toBe($perfil_antes->poder)
        ->and($perfil_depois->descricao)->toBe($perfil_antes->descricao)
        ->and($perfil_depois->permissoes)->toBeEmpty();
});

test('exclui o perfil informado', function () {
    $id_perfil = Perfil::factory()->create()->id;

    concederPermissao(Permissao::PERFIL_DELETE);

    expect(Perfil::where('id', $id_perfil)->exists())->toBeTrue();

    delete(route('administracao.perfil.destroy', $id_perfil))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Perfil::where('id', $id_perfil)->exists())->toBeFalse();
});

test('PerfilController usa trait', function () {
    expect(
        collect(class_uses(PerfilController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
