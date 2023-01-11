<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Administracao\PermissaoController;
use App\Http\Requests\Administracao\UpdatePermissaoRequest;
use App\Http\Resources\Permissao\PermissaoResource;
use App\Models\Perfil;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->dados = [
        'nome' => 'Loren Ipsun',
        'descricao' => 'foo bar',
    ];

    login();
});

afterEach(fn () => logout());

// Autorização
test('usuário sem permissão não consegue exibir formulário de edição da permissão', function () {
    get(route('administracao.permissao.edit', Permissao::factory()->create()))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        PermissaoController::class,
        $action,
        $request
    );
})->with([
    ['update', UpdatePermissaoRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::PERMISSAO_VIEW_ANY);
    Permissao::factory(3)->create();

    get(route('administracao.permissao.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Permissao/Index')
                ->has('permissoes.data', 3 + 1)
                ->has('permissoes.meta.termo')
                ->has('permissoes.meta.order')
        );
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::PERMISSAO_UPDATE);

    $permissao = Permissao::factory()->hasPerfis(3)->create();

    get(route('administracao.permissao.edit', $permissao))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Permissao/Edit')
                ->where('permissao.data', PermissaoResource::make($permissao)->resolve())
                ->has('perfis.data', 5 + 3) //todos os perfis serão retornados
                ->has('perfis.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::PERMISSAO_UPDATE);

    $permissao = Permissao::factory()->create();

    get(route('administracao.permissao.edit', $permissao))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Administracao/Permissao/Edit')
        );
});

test('atualiza uma permissao', function () {
    concederPermissao(Permissao::PERMISSAO_UPDATE);

    $permissao_antes = Permissao::factory()->create();

    patch(route('administracao.permissao.update', $permissao_antes), $this->dados)
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $permissao_depois = Permissao::find($permissao_antes->id);

    expect($permissao_depois->nome)->toBe($this->dados['nome'])
        ->and($permissao_depois->slug)->toBe($permissao_antes->slug)
        ->and($permissao_depois->descricao)->toBe($this->dados['descricao']);
});

test('faz o toggle do perfil se ele for informado sem alterar os demais atributos da permissão', function () {
    concederPermissao(Permissao::PERMISSAO_UPDATE);
    $perfil = Perfil::factory()->create();
    $permissao_antes = Permissao::factory()->create();

    // adiciona o perfil (toggle)
    patch(route('administracao.permissao.update', $permissao_antes), $this->dados + ['perfil_id' => $perfil->id])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $permissao_depois = Permissao::with('perfis')->find($permissao_antes->id);

    expect($permissao_depois->nome)->toBe($permissao_antes->nome)
        ->and($permissao_depois->slug)->toBe($permissao_antes->slug)
        ->and($permissao_depois->descricao)->toBe($permissao_antes->descricao)
        ->and($permissao_depois->perfis->first()->id)->toBe($perfil->id);

    // remove o perfil (toggle)
    patch(route('administracao.permissao.update', $permissao_antes), $this->dados + ['perfil_id' => $perfil->id])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $permissao_depois = Permissao::with('perfis')->find($permissao_antes->id);

    expect($permissao_depois->nome)->toBe($permissao_antes->nome)
        ->and($permissao_depois->slug)->toBe($permissao_antes->slug)
        ->and($permissao_depois->descricao)->toBe($permissao_antes->descricao)
        ->and($permissao_depois->permissoes)->toBeEmpty();
});

test('PermissaoController usa trait', function () {
    expect(
        collect(class_uses(PermissaoController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
