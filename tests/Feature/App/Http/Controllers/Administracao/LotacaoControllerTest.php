<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Administracao\LotacaoController;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    login();
});

afterEach(fn () => logout());

// Autorização
test('usuário sem permissão não consegue listar as lotações', function () {
    get(route('administracao.lotacao.index'))->assertForbidden();
});

// Caminho feliz
test('action index compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::LOTACAO_VIEW_ANY);

    Lotacao::factory(5)->create();

    get(route('administracao.lotacao.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Lotacao/Index')
                ->has('lotacoes.data', 5 + 1)
                ->has('lotacoes.meta.termo')
                ->has('lotacoes.meta.order')
        );
});

test('atualiza a administrabilidade da lotação e reseta os perfis de seus usuários', function () {
    concederPermissao(Permissao::LOTACAO_UPDATE);

    $perfis = Perfil::all();
    $lotacao = Lotacao::factory()->create(['administravel' => true]);
    $adm = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => $perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id,
    ]);
    $nao_adm = Usuario::factory()->create(['lotacao_id' => $lotacao->id]);
    $outra_lotacao = Usuario::factory()->create();

    patch(route('administracao.lotacao.update', $lotacao))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $lotacao->refresh();
    $adm->refresh();
    $nao_adm->refresh();
    $outra_lotacao->refresh();

    expect($lotacao->administravel)->toBeFalse()
        ->and($adm->perfil->id)->toBe($perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id)
        ->and($nao_adm->perfil->id)->toBe($perfis->firstWhere('slug', Perfil::PADRAO)->id)
        ->and($outra_lotacao->perfil->id)->not->toBe($perfis->firstWhere('slug', Perfil::PADRAO)->id);
});

test('LotacaoController usa trait', function () {
    expect(
        collect(class_uses(LotacaoController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
